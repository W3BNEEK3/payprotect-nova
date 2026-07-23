<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Models\ComplianceFlag;
use App\Models\ComplianceRequirement;
use App\Models\UserComplianceCode;
use App\Repositories\ComplianceFlagRepository;
use App\Repositories\ComplianceCodeRepository;
use App\Repositories\ComplianceSettingsRepository;
use App\Repositories\UserRepository;
use App\Services\ComplianceEngine;

class ComplianceController extends BaseController
{
    private ComplianceFlagRepository     $flagRepo;
    private ComplianceCodeRepository     $codeRepo;
    private ComplianceSettingsRepository $settingsRepo;
    private UserRepository               $userRepo;
    private ComplianceEngine             $engine;

    public function __construct()
    {
        $this->flagRepo     = new ComplianceFlagRepository();
        $this->codeRepo     = new ComplianceCodeRepository();
        $this->settingsRepo = new ComplianceSettingsRepository();
        $this->userRepo     = new UserRepository();
        $this->engine       = new ComplianceEngine();
    }

    /**
     * List all open compliance flags — the primary admin compliance queue.
     */
    public function flags(): void
    {
        $this->view('admin/compliance/flags', [
            'pageTitle'  => 'Compliance Flags',
            'openFlags'  => $this->flagRepo->findAllOpen(),
        ]);
    }

    /**
     * Show the assign-code form for a specific flag (FR-5.3).
     */
    public function showAssignCode(int $id): void
    {
        $flag = ComplianceFlag::find($id);
        if (!$flag || $flag['status'] !== 'open') {
            Session::flash('error', 'Flag not found or already resolved.');
            $this->redirect('/admin/compliance');
            return;
        }

        $user         = \App\Models\User::find($flag['user_id']);
        $requirements = ComplianceRequirement::all();

        $this->view('admin/compliance/assign-code', [
            'pageTitle'    => 'Assign Compliance Code',
            'flag'         => $flag,
            'user'         => $user,
            'requirements' => $requirements,
        ]);
    }

    /**
     * Assign a compliance code to a flagged user and resolve the flag (FR-5.3).
     * The code is then communicated to the user out-of-band by Support.
     */
    public function assignCode(int $id): void
    {
        $flag = ComplianceFlag::find($id);
        if (!$flag || $flag['status'] !== 'open') {
            Session::flash('error', 'Flag not found or already resolved.');
            $this->redirect('/admin/compliance');
            return;
        }

        $code           = strtoupper(trim($_POST['code'] ?? ''));
        $complianceId   = (int) ($_POST['compliance_id'] ?? 0);
        $notes          = trim($_POST['notes'] ?? '');
        $adminId        = Session::get('admin_id');
        $adminName      = Session::get('admin_name', 'Admin');

        if (!$code || !$complianceId) {
            Session::flash('error', 'A code and a requirement type are required.');
            $this->redirect("/admin/compliance/{$id}/assign-code");
            return;
        }

        // Create the user_compliance_codes record linked to this flag.
        UserComplianceCode::create([
            'user_id'       => $flag['user_id'],
            'compliance_id' => $complianceId,
            'flag_id'       => $id,
            'code'          => $code,
            'notes'         => $notes,
            'assigned_by'   => $adminName,
            'is_cleared'    => 0,
            'assigned_at'   => date('Y-m-d H:i:s'),
        ]);

        // Notify the user (FR-5.3: admin has completed the process, user must
        // now enter the code on the verification screen).
        \App\Models\Notification::create([
            'user_id' => $flag['user_id'],
            'type'    => 'compliance_code_assigned',
            'message' => 'Your compliance review has been processed. Contact Support to receive your clearance code, then enter it on your account to resume withdrawals.',
        ]);
        
        \App\Services\AuditLogger::log('assign_compliance_code', 'user_compliance_codes', $flag['user_id'], [
            'compliance_id' => $complianceId,
            'code' => $code
        ]);

        Session::flash('success', "Code assigned. User #{$flag['user_id']} has been notified to contact Support.");
        $this->redirect('/admin/compliance');
    }

    /**
     * Manually flag any user for compliance review — BR-6.
     */
    public function manualFlag(): void
    {
        $userId   = (int) ($_POST['user_id'] ?? 0);
        $reason   = trim($_POST['reason'] ?? '');
        $adminId  = Session::get('admin_id');

        if (!$userId || !$reason) {
            Session::flash('error', 'User and reason are required.');
            $this->redirect('/admin/compliance');
            return;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            Session::flash('error', 'User not found.');
            $this->redirect('/admin/compliance');
            return;
        }

        $this->engine->manualFlag($userId, $reason, $adminId);

        // Notify user that compliance review is required.
        \App\Models\Notification::create([
            'user_id' => $userId,
            'type'    => 'compliance_flag_raised',
            'message' => 'Your account has been flagged for compliance review. Your withdrawal access is temporarily paused. Please contact Support to begin the process.',
        ]);
        
        \App\Services\AuditLogger::log('manual_compliance_flag', 'compliance_flags', $userId, [
            'reason' => $reason
        ]);

        Session::flash('success', "User #{$userId} flagged for compliance review.");
        $this->redirect('/admin/compliance');
    }

    /**
     * List compliance_requirements catalog — admin-defined types (KYC, IMF, etc.).
     */
    public function requirements(): void
    {
        $requireKyc = $this->settingsRepo->get('require_kyc_for_withdrawal', '0') === '1';

        $this->view('admin/compliance/requirements', [
            'pageTitle'    => 'Compliance Requirements',
            'requirements' => ComplianceRequirement::all(),
            'requireKyc'   => $requireKyc,
        ]);
    }

    /**
     * Create a new compliance requirement type.
     */
    public function storeRequirement(): void
    {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$name) {
            Session::flash('error', 'Requirement name is required.');
            $this->redirect('/admin/compliance/requirements');
            return;
        }

        ComplianceRequirement::create([
            'name'        => $name,
            'description' => $description,
            'is_active'   => 1,
        ]);
        
        \App\Services\AuditLogger::log('create_compliance_requirement', 'compliance_requirements', null, [
            'name' => $name
        ]);

        Session::flash('success', "Requirement \"{$name}\" created.");
        $this->redirect('/admin/compliance/requirements');
    }

    /**
     * Toggle a compliance requirement active/inactive.
     */
    public function toggleRequirement(int $id): void
    {
        $req = ComplianceRequirement::find($id);
        if (!$req) {
            Session::flash('error', 'Requirement not found.');
            $this->redirect('/admin/compliance/requirements');
            return;
        }

        $newState = $req['is_active'] ? 0 : 1;
        $db = \App\Core\Database::connection();
        $stmt = $db->prepare('UPDATE compliance_requirements SET is_active = ? WHERE id = ?');
        $stmt->execute([$newState, $id]);
        
        \App\Services\AuditLogger::log('toggle_compliance_requirement', 'compliance_requirements', $id, [
            'is_active' => $newState
        ]);

        Session::flash('success', 'Requirement updated.');
        $this->redirect('/admin/compliance/requirements');
    }

    /**
     * Toggle the global KYC requirement for withdrawals.
     */
    public function toggleKyc(): void
    {
        $current = $this->settingsRepo->get('require_kyc_for_withdrawal', '0');
        $newState = $current === '1' ? '0' : '1';
        $this->settingsRepo->set('require_kyc_for_withdrawal', $newState);
        
        \App\Services\AuditLogger::log('toggle_kyc_requirement', 'compliance_settings', null, [
            'require_kyc_for_withdrawal' => $newState
        ]);

        Session::flash('success', 'KYC requirement toggled.');
        $this->redirect('/admin/compliance/requirements');
    }
}

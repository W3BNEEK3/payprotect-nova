<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Core\Session;
use App\Repositories\ComplianceFlagRepository;
use App\Repositories\ComplianceCodeRepository;
use App\Services\ComplianceEngine;

class ComplianceController extends BaseController
{
    private ComplianceFlagRepository $flagRepo;
    private ComplianceCodeRepository $codeRepo;
    private ComplianceEngine         $engine;

    public function __construct()
    {
        $this->flagRepo = new ComplianceFlagRepository();
        $this->codeRepo = new ComplianceCodeRepository();
        $this->engine   = new ComplianceEngine();
    }

    /**
     * Compliance verification screen — shown when a user tries to withdraw
     * but has an open compliance flag (FR-5.5, BR-9).
     *
     * Two states:
     *  - "contact Support" — flag exists but no code assigned yet.
     *  - "enter your code" — admin has assigned a code (OTP-style input).
     */
    public function index(): void
    {
        $userId = Session::get('user_id');

        // Get the oldest unresolved code (FIFO — BR-8).
        $pendingCode = $this->codeRepo->findOldestUnclearedForUser($userId);

        // Has any open flag at all?
        $hasOpenFlag = $this->flagRepo->hasOpenFlag($userId);

        if (!$hasOpenFlag && !$pendingCode) {
            // Nothing blocking — send them back.
            $this->redirect('/dashboard');
            return;
        }

        $this->view('user/compliance/index', [
            'pageTitle'   => 'Compliance Verification',
            'pendingCode' => $pendingCode,  // null if no code assigned yet
            'hasOpenFlag' => $hasOpenFlag,
        ]);
    }

    /**
     * Submit the compliance code the user received from Support (FR-5.4).
     *
     * Correct code → clears the record (is_cleared = 1), redirects back
     * to dashboard or withdrawal if that's where they came from.
     *
     * Incorrect → error, does NOT reveal the correct value (Section 6.2).
     */
    public function verify(): void
    {
        $userId      = Session::get('user_id');
        $pendingCode = $this->codeRepo->findOldestUnclearedForUser($userId);

        if (!$pendingCode) {
            $this->redirect('/dashboard');
            return;
        }

        // Collect the segmented OTP boxes into a single string.
        $submitted = strtoupper(implode('', $_POST['code_box'] ?? []));

        if ($submitted === '' || $submitted !== strtoupper($pendingCode['code'])) {
            Session::flash('error', 'Incorrect code — check with Support and try again.');
            $this->redirect('/compliance');
            return;
        }

        // Correct — clear this record.
        $this->codeRepo->clear((int) $pendingCode['id']);

        // Check whether more codes remain (BR-8 — sequential, FIFO).
        $nextCode = $this->codeRepo->findOldestUnclearedForUser($userId);
        if ($nextCode) {
            Session::flash('success', 'Code accepted. You have additional compliance requirements to clear.');
            $this->redirect('/compliance');
            return;
        }

        // All cleared — notify.
        \App\Models\Notification::create([
            'user_id' => $userId,
            'type'    => 'compliance_cleared',
            'message' => 'Your compliance review is complete. You may now apply for your virtual card to enable withdrawals.',
        ]);

        Session::flash('success', 'Compliance verified. You can now apply for your virtual card.');
        $this->redirect('/virtual-card/create');
    }
}

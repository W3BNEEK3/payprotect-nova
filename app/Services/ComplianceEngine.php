<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ComplianceFlag;
use App\Repositories\ComplianceFlagRepository;
use App\Repositories\ComplianceSettingsRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;

/**
 * ComplianceEngine — implements SRS Section 5, BR-5–BR-8.
 *
 * Automatically evaluates every inbound credit against the configured
 * threshold. Never touches UI — callers decide what to do with the result.
 */
class ComplianceEngine
{
    private ComplianceFlagRepository    $flagRepo;
    private ComplianceSettingsRepository $settingsRepo;
    private TransactionRepository       $txRepo;
    private UserRepository              $userRepo;

    public function __construct()
    {
        $this->flagRepo     = new ComplianceFlagRepository();
        $this->settingsRepo = new ComplianceSettingsRepository();
        $this->txRepo       = new TransactionRepository();
        $this->userRepo     = new UserRepository();
    }

    /**
     * Evaluate a credit that has just been posted to a user's account.
     *
     * BR-5: Auto-flag when BOTH:
     *  1. account is "new" (created within new_account_threshold_days)
     *  2. cumulative credits since creation reach or exceed auto_flag_amount_usd
     *
     * Safe to call repeatedly — checks for an existing open flag first.
     *
     * @param int   $userId
     * @param float $creditAmountUsd  Amount of this specific credit, in USD-equivalent.
     * @return bool  true if a new flag was raised, false otherwise.
     */
    public function evaluateCredit(int $userId, float $creditAmountUsd): bool
    {
        // Skip if there's already an open flag — no need to double-flag.
        if ($this->flagRepo->hasOpenFlag($userId)) {
            return false;
        }

        $thresholdDays   = (int)   ($this->settingsRepo->get('new_account_threshold_days') ?? 30);
        $thresholdAmount = (float) ($this->settingsRepo->get('auto_flag_amount_usd')       ?? 7000);

        // BR-5 condition 1: is the account still "new"?
        if (!$this->userRepo->isNewAccount($userId, $thresholdDays)) {
            return false;
        }

        // BR-5 condition 2: cumulative credits since account creation.
        // sumCreditsForUserSince expects a datetime string — compute the cutoff.
        $sinceDatetime = (new \DateTimeImmutable())->modify("-{$thresholdDays} days")->format('Y-m-d H:i:s');
        $cumulative = $this->txRepo->sumCreditsForUserSince($userId, $sinceDatetime);

        if ($cumulative < $thresholdAmount) {
            return false;
        }

        // Both conditions met — raise the auto-flag.
        ComplianceFlag::create([
            'user_id'            => $userId,
            'reason'             => 'new_account',
            'status'             => 'open',
            'triggered_amount'   => $cumulative,
            'triggered_currency' => 'USD',
            'created_by'         => null,
        ]);

        return true;
    }

    /**
     * Manually flag a user — BR-6.
     * Admin discretion, any account, any reason.
     */
    public function manualFlag(int $userId, string $reason, int $adminId): bool
    {
        // Allow multiple manual flags over time, per BR-8 (sequential).
        ComplianceFlag::create([
            'user_id'            => $userId,
            'reason'             => 'manual',
            'status'             => 'open',
            'triggered_amount'   => null,
            'triggered_currency' => null,
            'created_by'         => $adminId,
            'notes'              => $reason,
        ]);

        return true;
    }

    /**
     * Check whether a user currently has any open compliance flag.
     * Used by the WithdrawalGate to block withdrawals (SRS BR-9).
     */
    public function hasOpenFlag(int $userId): bool
    {
        return $this->flagRepo->hasOpenFlag($userId);
    }

    /**
     * Check whether the oldest unresolved compliance code has been assigned
     * (i.e. admin completed the off-system process — FR-5.3).
     * Returns the code record if a code exists but hasn't been cleared yet,
     * or null if no code has been assigned (user should contact Support).
     */
    public function getOldestPendingCode(int $userId): ?array
    {
        $codeRepo = new \App\Repositories\ComplianceCodeRepository();
        return $codeRepo->findOldestUnclearedForUser($userId);
    }
}

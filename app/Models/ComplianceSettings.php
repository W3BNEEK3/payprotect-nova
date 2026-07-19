<?php

namespace App\Models;

use App\Core\Model;

/**
 * Deliberately not keyed by id in application logic — ComplianceSettingsRepository
 * reads/writes by setting_key. The base Model's id-based methods still work if
 * ever needed directly, but every real caller uses the Repository's get()/set().
 */
class ComplianceSettings extends Model
{
    protected static string $table = 'compliance_settings';
}

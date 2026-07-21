<?php

require __DIR__ . '/../bootstrap/app.php';

use App\Services\ChatEscalationChecker;

echo "Running ChatEscalationChecker...\n";

$checker = new ChatEscalationChecker();
$checker->checkAndEscalate();

echo "Escalation check complete.\n";

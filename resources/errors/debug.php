<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception | NovaTrust Debug</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="/assets/css/design-tokens.css">
    <style>
        body { background: var(--color-paper); min-height: 100vh; padding: var(--space-8); margin: 0; font-family: var(--font-body); color: var(--color-ink); }
        .debug-container { max-width: 1000px; margin: 0 auto; background: var(--color-surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; }
        .debug-header { background: var(--color-danger); color: white; padding: var(--space-6) var(--space-8); }
        .debug-exception-class { font-size: 0.9rem; font-family: var(--font-mono); opacity: 0.9; margin-bottom: var(--space-2); }
        .debug-message { font-size: 1.5rem; font-family: var(--font-heading); margin: 0; }
        .debug-file { background: #7f1d1d; padding: var(--space-3) var(--space-8); font-size: 0.85rem; font-family: var(--font-mono); color: white; opacity: 0.95; }
        .debug-stack { padding: var(--space-8); }
        .debug-stack h3 { margin-top: 0; font-family: var(--font-heading); font-size: 1.1rem; color: var(--color-ink); border-bottom: 1px solid var(--slate-200); padding-bottom: var(--space-2); margin-bottom: var(--space-4); }
        .trace-item { font-family: var(--font-mono); font-size: 0.85rem; padding: var(--space-3) 0; border-bottom: 1px solid var(--slate-100); color: var(--slate-600); word-break: break-all; }
        .trace-item:last-child { border-bottom: none; }
        .trace-item .trace-index { display: inline-block; width: 30px; color: var(--slate-400); }
        .trace-item .trace-file { color: var(--color-ink); font-weight: 500; }
        .trace-item .trace-call { color: var(--color-teal); }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="debug-header">
            <div class="debug-exception-class"><?= htmlspecialchars(get_class($e)) ?></div>
            <h1 class="debug-message"><?= htmlspecialchars($e->getMessage() ?: 'No message provided') ?></h1>
        </div>
        <div class="debug-file">
            <?= htmlspecialchars($e->getFile()) ?> : <?= $e->getLine() ?>
        </div>
        <div class="debug-stack">
            <h3>Stack Trace</h3>
            <?php foreach ($e->getTrace() as $index => $trace): ?>
                <div class="trace-item">
                    <span class="trace-index">#<?= $index ?></span>
                    <?php if (isset($trace['file'])): ?>
                        <span class="trace-file"><?= htmlspecialchars($trace['file']) ?> : <?= $trace['line'] ?></span><br>
                        <span style="display:inline-block; width: 30px;"></span>
                    <?php endif; ?>
                    <span class="trace-call">
                        <?= htmlspecialchars(($trace['class'] ?? '') . ($trace['type'] ?? '') . $trace['function']) ?>()
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

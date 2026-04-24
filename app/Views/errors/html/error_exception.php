<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Error - JUSEA CMN v2.0</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #c0392b; }
        .message { background: #fef0f0; padding: 15px; border-left: 4px solid #c0392b; margin: 20px 0; }
        .file { color: #666; font-size: 0.9em; }
        pre { background: #f8f8f8; padding: 15px; overflow-x: auto; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Error del Sistema</h1>
        <div class="message">
            <p><strong><?= esc(get_class($exception)) ?></strong></p>
            <p><?= nl2br(esc($message)) ?></p>
        </div>
        <?php if (defined('CI_DEBUG') && CI_DEBUG) : ?>
        <p class="file">
            <strong>Archivo:</strong> <?= esc($exception->getFile()) ?><br>
            <strong>Linea:</strong> <?= esc($exception->getLine()) ?>
        </p>
        <h3>Backtrace:</h3>
        <pre><?php foreach ($exception->getTrace() as $i => $t): ?>#<?= $i ?> <?= isset($t['file']) ? esc($t['file']) . ':' . $t['line'] : '[internal]' ?> - <?= isset($t['class']) ? esc($t['class'] . $t['type']) : '' ?><?= esc($t['function']) ?>()
<?php endforeach; ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>

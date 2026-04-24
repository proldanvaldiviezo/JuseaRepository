<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>404 - Pagina no encontrada</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; text-align: center; }
        .container { background: white; padding: 40px; border-radius: 8px; max-width: 600px; margin: 60px auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2d5016; font-size: 48px; margin-bottom: 10px; }
        p { color: #666; font-size: 18px; }
        a { color: #2d5016; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>404</h1>
        <p><?= esc($message) ?></p>
        <p><a href="<?= site_url('/') ?>">Volver al inicio</a></p>
    </div>
</body>
</html>

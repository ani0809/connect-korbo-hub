<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database Not Ready</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f6f8fb; margin:0; }
        .wrap { max-width: 720px; margin: 8vh auto; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:24px; }
        h1 { margin:0 0 10px; font-size:24px; color:#111827; }
        p { margin:0 0 10px; color:#374151; line-height:1.5; }
        code { background:#f3f4f6; padding:2px 6px; border-radius:4px; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Database setup required</h1>
        <p>Your app is running, but the full database schema/data is not imported yet.</p>
        <p>Import the project SQL file (for example <code>laravel.sql</code> or <code>shop.sql</code>) and update <code>.env</code> DB credentials.</p>
        <p>After import, clear cache with <code>php artisan optimize:clear</code>.</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Baqala POS</title>
    <link rel="icon" href="/favicon.ico">
    <?php echo app('Tighten\Ziggy\BladeRouteGenerator')->generate(); ?>
    <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/pos-app/src/main.jsx']); ?>
</head>
<body>
    <div id="root"></div>
</body>
</html>
<?php /**PATH C:\laragon\www\baqala\resources\views/pos/app.blade.php ENDPATH**/ ?>
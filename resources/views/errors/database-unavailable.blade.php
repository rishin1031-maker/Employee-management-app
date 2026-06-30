<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Unavailable — EMS</title>
    <x-theme-init />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="min-h-screen ems-login-page bg-gradient-to-br from-gray-900 to-indigo-900 flex items-center justify-center p-4 transition-colors duration-200">
<div class="absolute top-4 right-4">
    <x-theme-toggle class="!text-gray-300 hover:!bg-white/10" />
</div>
<div class="w-full max-w-md">
    <div class="ems-login-card bg-white rounded-2xl shadow-2xl p-8 text-center transition-colors duration-200">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-500 rounded-2xl mb-4">
            <i class="fas fa-database text-white text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Database Unavailable</h1>
        <p class="text-sm text-gray-600 mb-6">
            We cannot reach the database server right now. The application is temporarily unavailable.
        </p>
        <p class="text-xs text-gray-500 mb-6">
            Please wait a moment and try again. If this keeps happening, contact your system administrator.
        </p>
        <button type="button" onclick="window.location.reload()"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg transition text-sm">
            Try Again
        </button>
    </div>
</div>
</body>
</html>

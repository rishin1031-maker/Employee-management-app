<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EMS</title>
    <x-theme-init />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="min-h-screen ems-login-page flex items-center justify-center p-4 transition-colors duration-200">
<div class="absolute top-4 right-4 z-10">
    <x-theme-toggle class="!text-gray-300 hover:!bg-white/10" />
</div>

{{-- Decorative blobs --}}
<div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl"></div>
</div>

<div class="w-full max-w-md relative z-10">
    <div class="ems-login-card p-8 sm:p-10 transition-colors duration-200">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-2xl mb-4 shadow-lg shadow-indigo-900/30">
                <i class="fas fa-users text-white text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 tracking-tight">Employee Management System</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">Sign in to your account</p>
        </div>

        @if(session('success'))
            <div class="ems-alert ems-alert-success mb-5">
                <i class="fas fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="ems-alert ems-alert-error mb-5">
                <i class="fas fa-circle-xmark"></i> {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="ems-alert ems-alert-error mb-5">
                <i class="fas fa-circle-xmark"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email or Employee ID</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                           class="ems-input w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white dark:bg-gray-800"
                           placeholder="admin@ems.com or EMP001">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="password" required
                           class="ems-input w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm bg-white dark:bg-gray-800"
                           placeholder="••••••••">
                </div>
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-indigo-600 rounded border-gray-300">
                <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</label>
            </div>
            <button type="submit"
                    class="w-full text-white font-semibold py-3 rounded-xl transition text-sm">
                <i class="fas fa-right-to-bracket mr-1.5"></i> Sign In
            </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/60 rounded-xl text-xs text-gray-500 dark:text-gray-400 space-y-1.5 border border-gray-100 dark:border-gray-700">
            <p><i class="fas fa-shield-halved text-indigo-500 mr-1.5"></i> Admin: use your <span class="font-semibold text-gray-700 dark:text-gray-300">email address</span></p>
            <p><i class="fas fa-id-badge text-teal-500 mr-1.5"></i> Employee: use your <span class="font-semibold text-gray-700 dark:text-gray-300">Employee ID</span> (e.g. EMP001)</p>
        </div>
    </div>
    <p class="text-center text-xs text-gray-500 mt-6">&copy; {{ date('Y') }} Employee Management System</p>
</div>
</body>
</html>

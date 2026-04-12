<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Parkme!') - Gestión Inteligente de Parqueaderos</title>

    <!-- Google Fonts: Poppins (Included via base app.css or loaded here as fallback) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="font-['Poppins'] antialiased bg-slate-900 text-slate-100 min-h-screen flex flex-col relative overflow-hidden">
    <!-- Subtle Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <main class="flex-grow flex items-center justify-center p-4 relative z-10 w-full h-full">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>

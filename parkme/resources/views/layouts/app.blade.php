<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user()?->TEMA === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Parkme!') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-poppins antialiased bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex w-full">
        
        <!-- Sidebar Navigation Component -->
        <livewire:layout.sidebar />

        <!-- Main Content Wrapper -->
        <div class="flex flex-1 flex-col lg:pl-64 min-w-0 transition-all duration-300 h-screen overflow-hidden">
            
            <!-- Topbar sticky area -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between lg:justify-end gap-x-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8 transition-colors">
                
                <!-- Mobile button -->
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-400 lg:hidden focus:outline-none" @click="sidebarOpen = true">
                    <span class="sr-only">Abrir Menú</span>
                    <x-atoms.icono nombre="bars-3" tamaño="lg" />
                </button>
                <div class="flex-1 lg:hidden text-lg font-bold text-indigo-600 dark:text-indigo-400 tracking-tight ml-2">Parkme!</div>

                <!-- Right Toolbar -->
                <div class="flex items-center gap-x-4 lg:gap-x-6">
                    
                    <!-- Theme Toggle -->
                    <livewire:layout.cambiar-tema />
                    
                    <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200 dark:bg-gray-700" aria-hidden="true"></div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.away="open = false" class="-m-1.5 flex items-center p-1.5 focus:outline-none" id="user-menu-button">
                            <span class="sr-only">Opciones de usuario</span>
                            <div class="h-9 w-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold border border-indigo-200 dark:border-indigo-800/60 shadow-sm transition-transform hover:scale-105">
                                {{ strtoupper(substr(auth()->user()->nombres ?? auth()->user()->NOMBRES ?? 'U', 0, 1)) }}
                            </div>
                            <span class="hidden lg:flex lg:items-center">
                                <span class="ml-3 flex flex-col text-sm font-semibold leading-5 text-gray-900 dark:text-white tracking-tight" aria-hidden="true">
                                    <span>{{ auth()->user()->nombres ?? auth()->user()->NOMBRES ?? 'Usuario' }}</span>
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400 font-normal mt-0.5">{{ ucfirst(auth()->user()->rol ?? auth()->user()->ROL ?? 'Usuario') }}</span>
                                </span>
                                <x-atoms.icono nombre="chevron-down" tamaño="sm" class="ml-2 text-gray-400 dark:text-gray-500" />
                            </span>
                        </button>

                        <div x-cloak x-show="open" 
                             x-transition:enter="transition ease-out duration-100" 
                             x-transition:enter-start="transform opacity-0 scale-95" 
                             x-transition:enter-end="transform opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="transform opacity-100 scale-100" 
                             x-transition:leave-end="transform opacity-0 scale-95" 
                             class="absolute right-0 z-10 mt-3 w-40 origin-top-right rounded-md bg-white dark:bg-gray-800 py-2 shadow-lg ring-1 ring-gray-900/5 dark:ring-white/10 border border-gray-100 dark:border-gray-700/50 focus:outline-none" role="menu">
                            
                            <form method="POST" action="{{ route('logout') ?? '#' }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                    <x-atoms.icono nombre="arrow-left-on-rectangle" tamaño="sm" class="mr-2" />
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page dynamic content -->
            <main class="flex-1 overflow-y-auto w-full display-block">
                @if(isset($header))
                    <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                        <div class="px-4 py-6 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                <div class="px-4 py-6 sm:px-6 lg:px-8 max-w-full mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
    
    @livewireScripts
</body>
</html>

@extends('layouts.auth')

@section('title', 'Bienvenido a Parkme!')

@section('content')
<div class="w-full max-w-sm bg-slate-800/80 backdrop-blur-sm rounded-3xl shadow-2xl p-7 transform transition-all sm:hover:scale-[1.02] border border-slate-700/50">
    <!-- Logo & Branding -->
    <div class="text-center mb-7">
        <div class="flex justify-center items-center mb-4">
            <div class="bg-blue-500/10 p-3.5 rounded-2xl ring-1 ring-blue-500/30">
                <!-- Vehicle Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-blue-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
            </div>
        </div>
        
        <h1 class="text-4xl font-extrabold tracking-tight text-white mb-2">
            Parkme<span class="text-blue-500">!</span>
        </h1>
        <p class="text-slate-400 text-base font-medium">
            Gestiona tu parqueadero de forma inteligente.
        </p>
    </div>

    <!-- Actions -->
    <div class="space-y-4">
        <a href="{{ route('register') }}" class="block w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-500 focus:ring-4 focus:outline-none focus:ring-blue-500/50 text-white font-semibold rounded-xl text-center transition-all duration-200 shadow-lg shadow-blue-500/30">
            Crear cuenta
        </a>
        
        <a href="{{ route('login') }}" class="block w-full py-3.5 px-4 bg-slate-700/50 hover:bg-slate-700 focus:ring-4 focus:outline-none focus:ring-slate-700/50 text-white font-semibold rounded-xl text-center transition-all duration-200 border border-slate-600 hover:border-slate-500">
            Iniciar sesión
        </a>
    </div>
    
    <!-- Graphic/decorations -->
    <div class="mt-10 flex justify-center gap-2">
        <div class="h-1.5 w-8 bg-blue-500 rounded-full"></div>
        <div class="h-1.5 w-2 bg-slate-600 rounded-full"></div>
        <div class="h-1.5 w-2 bg-slate-600 rounded-full"></div>
    </div>
</div>
@endsection

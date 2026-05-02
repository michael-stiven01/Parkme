<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <div class="text-xs font-semibold leading-6 text-slate-400/80 tracking-wider">PRINCIPAL</div>
            <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold transition-colors
                            {{ request()->routeIs('dashboard') ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-atoms.icono nombre="chart-bar" tamaño="md"
                            class="shrink-0 transition-colors {{ request()->routeIs('dashboard') ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-400' }}" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('parqueo.ingreso') }}"
                        class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold transition-colors
                            {{ request()->routeIs('parqueo.ingreso') ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-atoms.icono nombre="arrow-right-circle" tamaño="md"
                            class="shrink-0 transition-colors {{ request()->routeIs('parqueo.ingreso') ? 'text-indigo-400' : 'text-slate-400 group-hover:text-indigo-400' }}" />
                        Ingreso
                    </a>
                </li>
                <li>
                    <a href="{{ route('parqueo.salida') }}"
                        class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold transition-colors
                            {{ request()->routeIs('parqueo.salida') ? 'bg-rose-500/10 text-rose-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-atoms.icono nombre="arrow-left-circle" tamaño="md"
                            class="shrink-0 transition-colors {{ request()->routeIs('parqueo.salida') ? 'text-rose-400' : 'text-slate-400 group-hover:text-rose-400' }}" />
                        Salida
                    </a>
                </li>
                <li>
                    <a href="{{ route('parqueo.historial') }}"
                        class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold transition-colors
                            {{ request()->routeIs('parqueo.historial') ? 'bg-amber-500/10 text-amber-400' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <x-atoms.icono nombre="clock" tamaño="md"
                            class="shrink-0 transition-colors {{ request()->routeIs('parqueo.historial') ? 'text-amber-400' : 'text-slate-400 group-hover:text-amber-400' }}" />
                        Historial
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="text-xs font-semibold leading-6 text-slate-400/80 tracking-wider">GESTIÓN</div>
            <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li>
                    <a href="{{ route('vehiculos') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('vehiculos') ? 'bg-indigo-500/10 text-indigo-400' : '' }}">
                        <x-atoms.icono nombre="truck" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors {{ request()->routeIs('vehiculos') ? 'text-indigo-400' : '' }}" />
                        Vehículos
                    </a>
                </li>
                <li>
                    <a href="{{ route('clientes') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('clientes') ? 'bg-indigo-500/10 text-indigo-400' : '' }}">
                        <x-atoms.icono nombre="user-group" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors {{ request()->routeIs('clientes') ? 'text-indigo-400' : '' }}" />
                        Clientes
                    </a>
                </li>
                <li>
                    <a href="{{ route('cupos') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('cupos') ? 'bg-indigo-500/10 text-indigo-400' : '' }}">
                        <x-atoms.icono nombre="squares-2x2" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors {{ request()->routeIs('cupos') ? 'text-indigo-400' : '' }}" />
                        Cupos
                    </a>
                </li>
                <li>
                    <a href="{{ route('tarifas') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors {{ request()->routeIs('tarifas') ? 'bg-indigo-500/10 text-indigo-400' : '' }}">
                        <x-atoms.icono nombre="currency-dollar" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors {{ request()->routeIs('tarifas') ? 'text-indigo-400' : '' }}" />
                        Tarifas
                    </a>
                </li>
            </ul>
        </li>
        @if(strtolower(auth()->user()?->ROL ?? '') === 'administrador')
        <li>
            <div class="text-xs font-semibold leading-6 text-slate-400/80 tracking-wider">ADMINISTRACIÓN</div>
            <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li>
                    <a href="{{ route('usuarios') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="users" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Usuarios
                    </a>
                </li>
                    <a href="{{ route('empresa.configurar') }}" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="cog-6-tooth" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Configuración
                    </a>
                </li>
            </ul>
        </li>
        @endif
    </ul>
</nav>

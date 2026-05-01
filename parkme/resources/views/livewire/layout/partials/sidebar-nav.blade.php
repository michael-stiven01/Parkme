<nav class="flex flex-1 flex-col">
    <ul role="list" class="flex flex-1 flex-col gap-y-7">
        <li>
            <div class="text-xs font-semibold leading-6 text-slate-400/80 tracking-wider">PRINCIPAL</div>
            <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold bg-indigo-500/10 text-indigo-400 hover:bg-slate-800 transition-colors">
                        <x-atoms.icono nombre="chart-bar" tamaño="md" class="shrink-0" />
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="arrow-right-circle" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Ingreso/Salida
                    </a>
                </li>
            </ul>
        </li>
        <li>
            <div class="text-xs font-semibold leading-6 text-slate-400/80 tracking-wider">GESTIÓN</div>
            <ul role="list" class="-mx-2 mt-2 space-y-1">
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="truck" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Vehículos
                    </a>
                </li>
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="user-group" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Clientes
                    </a>
                </li>
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="squares-2x2" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Cupos
                    </a>
                </li>
                <li>
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="currency-dollar" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
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
                    <a href="#" class="group flex gap-x-3 rounded-xl p-2.5 text-sm leading-6 font-semibold text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                        <x-atoms.icono nombre="users" tamaño="md" class="text-slate-400 group-hover:text-indigo-400 shrink-0 transition-colors" />
                        Usuarios
                    </a>
                <li>
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

<?php

namespace App\Http\Livewire\Layout;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CambiarTema extends Component
{
    public $tema = 'light';

    public function mount()
    {
        $this->tema = Auth::check() ? (Auth::user()->TEMA ?? 'light') : 'light';
    }

    public function toggleTema()
    {
        $this->tema = $this->tema === 'dark' ? 'light' : 'dark';
        
        if (Auth::check()) {
            DB::table('usuarios')
              ->where('ID_USUARIO', Auth::id())
              ->update(['TEMA' => $this->tema]);
        }
        
        $this->dispatch('tema-cambiado', tema: $this->tema);
    }

    public function render()
    {
        return view('livewire.layout.cambiar-tema');
    }
}

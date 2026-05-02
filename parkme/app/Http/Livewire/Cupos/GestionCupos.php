<?php

namespace App\Http\Livewire\Cupos;

use Livewire\Component;
use App\Helpers\SPHelper;

class GestionCupos extends Component
{
    // ── Vista activa ────────────────────────────────────────────────────
    public string $vista = 'mapa'; // 'mapa' | 'lista'

    // ── Datos ────────────────────────────────────────────────────────────
    public array $cupos       = [];
    public array $mapa        = [];
    public array $tiposVehiculo = [];

    // ── Búsqueda ─────────────────────────────────────────────────────────
    public string $busqueda = '';

    // ── Modal estado ─────────────────────────────────────────────────────
    public bool $modalVisible            = false;
    public bool $modalConfirmacionVisible = false;
    public bool $modoEdicion             = false;

    // ── Formulario ───────────────────────────────────────────────────────
    public $cupo_id;
    public string $codigo   = '';
    public        $id_tipo_vehiculo = '';
    public string $fila     = '';
    public        $columna  = '';
    public string $estado   = 'disponible';

    // ── Confirmación ─────────────────────────────────────────────────────
    public $cupoSeleccionado;
    public string $estadoObjetivo = '';

    // ── Contadores ───────────────────────────────────────────────────────
    public int $totalDisponibles   = 0;
    public int $totalOcupados      = 0;
    public int $totalMantenimiento = 0;

    // ── Reglas de validación ─────────────────────────────────────────────
    protected function rules(): array
    {
        return [
            'codigo'          => 'required|string|max:20|regex:/^[A-Z0-9\-]+$/',
            'id_tipo_vehiculo'=> 'nullable',
            'fila'            => 'nullable|string|max:5',
            'columna'         => 'nullable|integer|min:1|max:999',
            'estado'          => 'required|in:disponible,mantenimiento',
        ];
    }

    protected array $messages = [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.regex'    => 'Solo letras mayúsculas, números y guiones.',
        'codigo.max'      => 'Máximo 20 caracteres.',
        'columna.integer' => 'La columna debe ser un número.',
        'columna.min'     => 'La columna mínima es 1.',
    ];

    // ─────────────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->cargarDatos();
    }

    public function render()
    {
        $cuposFiltrados = collect($this->cupos)
            ->filter(fn ($c) =>
                $this->busqueda === '' ||
                str_contains(strtolower($c->CODIGO), strtolower($this->busqueda)) ||
                str_contains(strtolower($c->TIPO_VEHICULO ?? ''), strtolower($this->busqueda)) ||
                str_contains(strtolower($c->FILA ?? ''), strtolower($this->busqueda))
            )
            ->values()
            ->all();

        return view('livewire.cupos.gestion-cupos', [
            'cuposFiltrados' => $cuposFiltrados,
        ])->layout('layouts.app');
    }

    // ── Carga de datos ───────────────────────────────────────────────────
    public function cargarDatos(): void
    {
        $idEmpresa = auth()->user()->id_empresa;

        $resultado = SPHelper::ejecutar('sp_listar_cupos', [$idEmpresa]);
        $this->cupos = $resultado ?? [];

        $mapa = SPHelper::ejecutar('sp_obtener_mapa_cupos', [$idEmpresa]);
        $this->mapa = $mapa ?? [];

        $tipos = SPHelper::ejecutar('sp_listar_tipos_vehiculo');
        $this->tiposVehiculo = $tipos ?? [];

        $this->calcularContadores();
    }

    private function calcularContadores(): void
    {
        $coleccion = collect($this->cupos);
        $this->totalDisponibles   = $coleccion->where('ESTADO', 'disponible')->count();
        $this->totalOcupados      = $coleccion->where('ESTADO', 'ocupado')->count();
        $this->totalMantenimiento = $coleccion->where('ESTADO', 'mantenimiento')->count();
    }

    // ── Refrescar mapa (polling) ─────────────────────────────────────────
    public function refrescarMapa(): void
    {
        $this->cargarDatos();
    }

    // ── Cambiar vista ────────────────────────────────────────────────────
    public function cambiarVista(string $vista): void
    {
        $this->vista = $vista;
    }

    // ── Modales CRUD ─────────────────────────────────────────────────────
    public function abrirModalCrear(): void
    {
        $this->soloAdmin();
        $this->resetearFormulario();
        $this->modoEdicion  = false;
        $this->modalVisible = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->soloAdmin();
        $this->resetValidation();
        $this->modoEdicion = true;

        $cupo = collect($this->cupos)->firstWhere('id_cupo', $id);

        if ($cupo) {
            $this->cupo_id          = $cupo->id_cupo;
            $this->codigo           = $cupo->CODIGO;
            $this->id_tipo_vehiculo = $cupo->id_tipo_vehiculo ?? '';
            $this->fila             = $cupo->FILA ?? '';
            $this->columna          = $cupo->COLUMNA ?? '';
            $this->estado           = $cupo->ESTADO;
            $this->modalVisible     = true;
        }
    }

    public function cerrarModal(): void
    {
        $this->modalVisible             = false;
        $this->modalConfirmacionVisible = false;
        $this->resetearFormulario();
    }

    public function resetearFormulario(): void
    {
        $this->resetValidation();
        $this->cupo_id          = null;
        $this->codigo           = '';
        $this->id_tipo_vehiculo = '';
        $this->fila             = '';
        $this->columna          = '';
        $this->estado           = 'disponible';
        $this->cupoSeleccionado = null;
        $this->estadoObjetivo   = '';
    }

    // ── Guardar (crear / editar) ──────────────────────────────────────────
    public function guardar(): void
    {
        $this->soloAdmin();
        $this->validate();

        $idEmpresa = auth()->user()->id_empresa;
        $idTv      = $this->id_tipo_vehiculo !== '' ? (int) $this->id_tipo_vehiculo : null;
        $fila      = $this->fila !== '' ? strtoupper($this->fila) : null;
        $columna   = $this->columna !== '' ? (int) $this->columna : null;

        if ($this->modoEdicion) {
            $resultado = SPHelper::ejecutar('sp_actualizar_cupo', [
                $this->cupo_id,
                $idTv,
                strtoupper($this->codigo),
                $fila,
                $columna,
            ]);

            $error = $resultado[0]->error ?? null;

            if ($error === 'CODIGO_DUPLICADO') {
                $this->addError('codigo', 'Ya existe un cupo con ese código en esta empresa.');
                return;
            }
            if ($error === 'POSICION_DUPLICADA') {
                $this->addError('fila', 'Ya existe un cupo en esa posición (fila + columna).');
                return;
            }

            session()->flash('success', 'Cupo actualizado correctamente.');
        } else {
            $resultado = SPHelper::ejecutar('sp_crear_cupo', [
                $idEmpresa,
                $idTv,
                strtoupper($this->codigo),
                $this->estado,
                $fila,
                $columna,
            ]);

            $error = $resultado[0]->error ?? null;

            if ($error === 'CODIGO_DUPLICADO') {
                $this->addError('codigo', 'Ya existe un cupo con ese código en esta empresa.');
                return;
            }
            if ($error === 'POSICION_DUPLICADA') {
                $this->addError('fila', 'Ya existe un cupo en esa posición (fila + columna).');
                return;
            }

            session()->flash('success', 'Cupo creado correctamente.');
        }

        $this->cerrarModal();
        $this->cargarDatos();
    }

    // ── Cambio de estado ─────────────────────────────────────────────────
    public function confirmarCambioEstado(int $id, string $estadoActual): void
    {
        $this->soloAdmin();
        $this->cupoSeleccionado = collect($this->cupos)->firstWhere('id_cupo', $id);
        $this->estadoObjetivo   = $estadoActual === 'disponible' ? 'mantenimiento' : 'disponible';
        $this->modalConfirmacionVisible = true;
    }

    public function cambiarEstado(): void
    {
        $this->soloAdmin();

        if ($this->cupoSeleccionado) {
            SPHelper::ejecutar('sp_cambiar_estado_cupo', [
                $this->cupoSeleccionado->id_cupo,
                $this->estadoObjetivo,
            ]);

            session()->flash('success', 'Estado del cupo actualizado.');
            $this->cerrarModal();
            $this->cargarDatos();
        }
    }

    // ── Helper de autorización ───────────────────────────────────────────
    private function soloAdmin(): void
    {
        if (strtolower(auth()->user()->ROL) !== 'administrador') {
            abort(403, 'Solo el administrador puede realizar esta acción.');
        }
    }
}

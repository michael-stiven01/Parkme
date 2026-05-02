<?php

namespace App\Http\Livewire\Parqueo;

use Livewire\Component;
use App\Helpers\SPHelper;
use App\Services\ParqueoService;
use App\Services\BitacoraService;

class RegistrarIngreso extends Component
{
    // ── Modo activo ──────────────────────────────────────────────────────────
    /** 'rapido' | 'primer_ingreso' */
    public string $modo = 'rapido';

    // ── Estado general ───────────────────────────────────────────────────────
    public bool  $exito         = false;
    public array $resultadoExito = [];

    // ── Datos comunes ─────────────────────────────────────────────────────────
    public array $tiposVehiculo = [];
    public array $cuposDisponibles = [];

    // ─────────────────────────────────────────────────────────────────────────
    // MODO B — Ingreso Rápido
    // ─────────────────────────────────────────────────────────────────────────
    public string $placa_rapido     = '';
    public ?array $vehiculoEncontrado = null; // datos del vehículo buscado
    /** null = sin buscar | 'adentro' | 'disponible' | 'no_encontrado' */
    public ?string $estadoVehiculo  = null;
    public ?string $fechaIngresoActual = null; // si ya está adentro

    public $id_cupo_rapido = '';   // cupo seleccionado en modo rápido

    // ─────────────────────────────────────────────────────────────────────────
    // MODO A — Primer Ingreso
    // ─────────────────────────────────────────────────────────────────────────
    // Cliente
    public string $nombres    = '';
    public string $apellidos  = '';
    public string $correo     = '';
    public string $telefono   = '';
    // Vehículo
    public string $placa           = '';
    public        $id_tipo_vehiculo = '';
    public string $marca           = '';
    public string $descripcion     = '';
    public        $id_cupo         = '';

    /** null | 'ya_existe' — alerta cuando la placa ya existe en la empresa */
    public ?string $alertaPlaca = null;
    /** true mientras se hace la comprobación de placa */
    public bool $verificandoPlaca = false;

    // ── Reglas de validación ─────────────────────────────────────────────────
    protected function rules(): array
    {
        if ($this->modo === 'rapido') {
            return [
                'id_cupo_rapido' => 'required',
            ];
        }

        return [
            'nombres'         => 'required|string|max:100',
            'apellidos'       => 'required|string|max:100',
            'correo'          => 'required|email|max:150',
            'telefono'        => 'nullable|string|max:20',
            'placa'           => 'required|string|max:10|regex:/^[A-Z0-9\-]+$/',
            'id_tipo_vehiculo'=> 'required',
            'marca'           => 'nullable|string|max:80',
            'descripcion'     => 'nullable|string|max:255',
            'id_cupo'         => 'required',
        ];
    }

    protected array $messages = [
        'nombres.required'          => 'El nombre es obligatorio.',
        'apellidos.required'        => 'El apellido es obligatorio.',
        'correo.required'           => 'El correo es obligatorio.',
        'correo.email'              => 'Ingresa un correo válido.',
        'placa.required'            => 'La placa es obligatoria.',
        'placa.regex'               => 'Solo letras mayúsculas, números y guiones.',
        'placa.max'                 => 'Máximo 10 caracteres.',
        'id_tipo_vehiculo.required' => 'Selecciona el tipo de vehículo.',
        'id_cupo.required'          => 'Selecciona un cupo.',
        'id_cupo_rapido.required'   => 'Selecciona un cupo para el ingreso.',
    ];

    // ── Lifecycle ────────────────────────────────────────────────────────────
    public function mount(): void
    {
        $this->cargarDatosBase();
    }

    public function render()
    {
        return view('livewire.parqueo.registrar-ingreso')->layout('layouts.app');
    }

    // ── Carga base ───────────────────────────────────────────────────────────
    private function cargarDatosBase(): void
    {
        $tipos = SPHelper::ejecutar('sp_listar_tipos_vehiculo');
        $this->tiposVehiculo = $tipos ?? [];
    }

    private function cargarCuposDisponibles(?int $idTipoVehiculo = null): void
    {
        $idEmpresa = auth()->user()->id_empresa;
        $cupos = SPHelper::ejecutar('sp_listar_cupos', [$idEmpresa]);

        $this->cuposDisponibles = collect($cupos ?? [])
            ->filter(function ($c) use ($idTipoVehiculo) {
                if ($c->ESTADO !== 'disponible') return false;
                // Si el cupo tiene restricción de tipo, debe coincidir
                if ($c->id_tipo_vehiculo && $idTipoVehiculo) {
                    return (int)$c->id_tipo_vehiculo === (int)$idTipoVehiculo;
                }
                return true;
            })
            ->values()
            ->all();
    }

    // ── Cambio de modo ───────────────────────────────────────────────────────
    public function cambiarModo(string $modo): void
    {
        $this->modo   = $modo;
        $this->exito  = false;
        $this->resetearFormulario();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MODO B — Búsqueda en tiempo real
    // ─────────────────────────────────────────────────────────────────────────
    public function updatedPlacaRapido(string $valor): void
    {
        $this->vehiculoEncontrado  = null;
        $this->estadoVehiculo      = null;
        $this->fechaIngresoActual  = null;
        $this->cuposDisponibles    = [];
        $this->id_cupo_rapido      = '';
        $this->resetErrorBag();

        $placa = strtoupper(trim($valor));
        $this->placa_rapido = $placa;

        if (strlen($placa) < 3) return;

        $this->buscarVehiculoRapido($placa);
    }

    public function buscarVehiculoRapido(string $placa): void
    {
        $idEmpresa = auth()->user()->id_empresa;

        // 1. Verificar si el vehículo existe
        $resultado = SPHelper::ejecutar('sp_buscar_vehiculo_por_placa', [$idEmpresa, $placa]);

        if (empty($resultado)) {
            $this->estadoVehiculo     = 'no_encontrado';
            $this->vehiculoEncontrado = null;
            return;
        }

        $vehiculo = (array) $resultado[0];
        $this->vehiculoEncontrado = $vehiculo;

        // 2. Verificar si ya está adentro
        $check = SPHelper::ejecutar('sp_verificar_vehiculo_en_parqueo', [$idEmpresa, $placa]);
        $checkData = ! empty($check) ? (array) $check[0] : [];

        if (! empty($checkData['esta_adentro']) && (int)$checkData['esta_adentro'] === 1) {
            $this->estadoVehiculo     = 'adentro';
            $this->fechaIngresoActual = $checkData['fecha_ingreso'] ?? null;
            return;
        }

        $this->estadoVehiculo = 'disponible';
        $this->cargarCuposDisponibles((int)($vehiculo['id_tipo_vehiculo'] ?? 0));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MODO A — Verificar placa al salir del campo
    // ─────────────────────────────────────────────────────────────────────────
    public function updatedPlaca(string $valor): void
    {
        $this->alertaPlaca = null;
        $placa = strtoupper(trim($valor));
        $this->placa = $placa;

        if (strlen($placa) < 3) return;

        $this->verificarPlacaExistente($placa);
    }

    public function verificarPlacaExistente(string $placa): void
    {
        $idEmpresa = auth()->user()->id_empresa;
        $resultado = SPHelper::ejecutar('sp_buscar_vehiculo_por_placa', [$idEmpresa, $placa]);

        if (! empty($resultado)) {
            $this->alertaPlaca = 'ya_existe';
        }
    }

    // Cuando cambia el tipo de vehículo en modo A, recargar cupos
    public function updatedIdTipoVehiculo($valor): void
    {
        $this->id_cupo = '';
        $idTv = $valor !== '' ? (int)$valor : null;
        $this->cargarCuposDisponibles($idTv);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — Ingreso Rápido (Modo B)
    // ─────────────────────────────────────────────────────────────────────────
    public function registrarIngresoRapido(): void
    {
        $this->validate(['id_cupo_rapido' => 'required']);

        if ($this->estadoVehiculo !== 'disponible' || empty($this->vehiculoEncontrado)) {
            $this->addError('id_cupo_rapido', 'No hay un vehículo disponible para registrar.');
            return;
        }

        $idEmpresa  = auth()->user()->id_empresa;
        $idUsuario  = auth()->user()->id_usuario;
        $idVehiculo = (int) $this->vehiculoEncontrado['id_vehiculo'];

        $servicio   = new ParqueoService();
        $datos      = $servicio->registrarIngreso([
            'id_empresa'  => $idEmpresa,
            'id_vehiculo' => $idVehiculo,
            'id_cupo'     => (int) $this->id_cupo_rapido,
            'id_usuario'  => $idUsuario,
        ]);

        if (! empty($datos['error'])) {
            $this->manejarErrorSP($datos['error']);
            return;
        }

        BitacoraService::registrar(
            'Ingreso rápido de vehículo',
            'Parqueo',
            $idUsuario,
            "Placa: {$datos['placa']} | Cupo: {$datos['codigo_cupo']}"
        );

        $servicio->enviarTicketIngreso($datos);

        $this->exito         = true;
        $this->resultadoExito = $datos;
        $this->resetearFormulario();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GUARDAR — Primer Ingreso (Modo A)
    // ─────────────────────────────────────────────────────────────────────────
    public function registrarPrimerIngreso(): void
    {
        $this->validate();

        if ($this->alertaPlaca === 'ya_existe') {
            $this->addError('placa', 'Esta placa ya existe. Usa el Ingreso Rápido.');
            return;
        }

        $idEmpresa = auth()->user()->id_empresa;
        $idUsuario = auth()->user()->id_usuario;

        // 1. Crear cliente
        $resCliente = SPHelper::ejecutar('sp_crear_cliente', [
            $idEmpresa,
            trim($this->nombres),
            trim($this->apellidos),
            trim($this->correo),
            trim($this->telefono) ?: null,
        ]);

        if (empty($resCliente)) {
            session()->flash('error', 'No se pudo crear el cliente. Intenta de nuevo.');
            return;
        }

        $idCliente = (int) $resCliente[0]->id_cliente;

        // 2. Crear vehículo
        $resVehiculo = SPHelper::ejecutar('sp_crear_vehiculo', [
            $idEmpresa,
            $idCliente,
            (int) $this->id_tipo_vehiculo,
            strtoupper(trim($this->placa)),
            trim($this->marca) ?: null,
            trim($this->descripcion) ?: null,
        ]);

        if (empty($resVehiculo)) {
            session()->flash('error', 'No se pudo registrar el vehículo (la placa puede ya existir).');
            return;
        }

        $idVehiculo = (int) $resVehiculo[0]->id_vehiculo;

        // 3. Registrar ingreso
        $servicio = new ParqueoService();
        $datos    = $servicio->registrarIngreso([
            'id_empresa'  => $idEmpresa,
            'id_vehiculo' => $idVehiculo,
            'id_cupo'     => (int) $this->id_cupo,
            'id_usuario'  => $idUsuario,
        ]);

        if (! empty($datos['error'])) {
            $this->manejarErrorSP($datos['error']);
            return;
        }

        BitacoraService::registrar(
            'Primer ingreso de vehículo',
            'Parqueo',
            $idUsuario,
            "Placa: {$datos['placa']} | Cliente: {$datos['nombre_cliente']} | Cupo: {$datos['codigo_cupo']}"
        );

        $servicio->enviarTicketIngreso($datos);

        $this->exito          = true;
        $this->resultadoExito = $datos;
        $this->resetearFormulario();
    }

    // ── Reset ────────────────────────────────────────────────────────────────
    public function resetearFormulario(): void
    {
        $this->resetValidation();
        $this->resetErrorBag();

        // Modo B
        $this->placa_rapido       = '';
        $this->vehiculoEncontrado = null;
        $this->estadoVehiculo     = null;
        $this->fechaIngresoActual = null;
        $this->id_cupo_rapido     = '';

        // Modo A
        $this->nombres          = '';
        $this->apellidos        = '';
        $this->correo           = '';
        $this->telefono         = '';
        $this->placa            = '';
        $this->id_tipo_vehiculo = '';
        $this->marca            = '';
        $this->descripcion      = '';
        $this->id_cupo          = '';
        $this->alertaPlaca      = null;
        $this->cuposDisponibles = [];
    }

    public function nuevoIngreso(): void
    {
        $this->exito          = false;
        $this->resultadoExito = [];
        $this->resetearFormulario();
        $this->cargarDatosBase();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    private function manejarErrorSP(string $error): void
    {
        $mensajes = [
            'VEHICULO_NO_ENCONTRADO'   => 'El vehículo no fue encontrado o no pertenece a esta empresa.',
            'VEHICULO_YA_EN_PARQUEO'   => 'Este vehículo ya tiene un ingreso activo en el parqueadero.',
            'CUPO_NO_ENCONTRADO'       => 'El cupo seleccionado no existe o no pertenece a esta empresa.',
            'CUPO_NO_DISPONIBLE'       => 'El cupo seleccionado ya no está disponible. Por favor elige otro.',
            'TIPO_VEHICULO_INCOMPATIBLE' => 'El tipo de vehículo no es compatible con el cupo seleccionado.',
            'SP_SIN_RESPUESTA'         => 'Error de comunicación con la base de datos.',
        ];

        $msg = $mensajes[$error] ?? "Error inesperado: {$error}";
        session()->flash('error', $msg);
    }
}

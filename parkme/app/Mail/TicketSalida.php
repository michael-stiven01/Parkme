<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketSalida extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array Datos del registro de parqueo finalizado */
    public array $datos;

    /**
     * @param array $datos  Resultado de sp_salida_vehiculo:
     *   id_registro, placa, nombre_cliente, correo_cliente,
     *   fecha_ingreso, fecha_salida, tiempo_minutos,
     *   valor_cobrado, nombre_tarifa, codigo_cupo
     */
    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Gracias por usar Parkme! — Comprobante de salida',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_salida',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

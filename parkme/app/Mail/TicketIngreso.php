<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketIngreso extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array Datos del registro de parqueo */
    public array $datos;

    /**
     * @param array $datos  Resultado de sp_ingreso_vehiculo:
     *   id_registro, fecha_ingreso, placa, nombre_cliente,
     *   correo_cliente, codigo_cupo, tipo_vehiculo
     */
    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Ticket de Ingreso - Parkme!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket_ingreso',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

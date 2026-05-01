<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialesUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $nombres;
    public $correo;
    public $contrasena;
    public $rol;
    public $codigoVerificacion;

    /**
     * Create a new message instance.
     */
    public function __construct($nombres, $correo, $contrasena, $rol, $codigoVerificacion)
    {
        $this->nombres = $nombres;
        $this->correo = $correo;
        $this->contrasena = $contrasena;
        $this->rol = ucfirst($rol);
        $this->codigoVerificacion = $codigoVerificacion;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tus credenciales de acceso a Parkme!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-usuario',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}

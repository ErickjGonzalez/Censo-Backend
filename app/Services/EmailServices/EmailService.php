<?php

namespace App\Services\EmailServices;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
       
    }

    /* 
        view es la vista que se va a usar para el correo,
        subject es el asunto del correo,
        mailMessage es el mensaje que se va a enviar, 
        password es la contraseña que se va a enviar
        email es el correo al que se va a enviar el mensaje
    */

    /* mandar contraseña y reenviar contraseña a un usuario */
    public function sendEmail($view,$subject,$mailMessage,$email)
    {
        /* verificar que no vengan vacias  */
        if (empty($view) || empty($subject) || empty($mailMessage)|| empty($email)) {
            throw new \InvalidArgumentException("Los parámetros no pueden estar vacíos.");
        }
        Mail::send($view, [
            'mailSubject' => $subject,
            'mailMessage' => $mailMessage,
            'link' => url('/login'),
            ], function ($message) use ($email,$subject) {
                $message->to($email)->subject($subject);
        });
    }
}
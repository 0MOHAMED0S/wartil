<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $categoryName;

    public function __construct(User $user, $password, $categoryName)
    {
        $this->user = $user;
        $this->password = $password;
        $this->categoryName = $categoryName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مبروك! تم قبول طلب انضمامك كمعلم في ورتل',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher_approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

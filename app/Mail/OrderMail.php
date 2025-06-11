<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Mailables\Address;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        string $subject,  
        public string $message,
        public ?string $pdfPath = null
    ) {
        $this->subject = $subject;
    }

    public function envelope(): Envelope
    {
        $fromAddress = Auth::check() ? Auth::user()->email : config('mail.from.address');
        $fromName = Auth::check() ? Auth::user()->name : config('mail.from.name');
        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.order',
            with: [
                'order' => $this->order,
                'emailMessage' => $this->message,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];
        
        if ($this->pdfPath && file_exists($this->pdfPath)) {
            $attachments[] = Attachment::fromPath($this->pdfPath)
                ->as("bestelling-{$this->order->id}.pdf")
                ->withMime('application/pdf');
        }
        
        return $attachments;
    }
}
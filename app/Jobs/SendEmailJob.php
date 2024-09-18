<?php
// app/Jobs/SendEmailJob.php
namespace App\Jobs;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $to;
    protected $subject;
    protected $message;

    public function __construct($to, $subject, $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function handle()
    {
        $mail = new PHPMailer(true);

        try {
            Log::info('Attempting to send email to ' . $this->to);

            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'testprintmysproject@gmail.com'; // Your Gmail address
            $mail->Password = 'ozmjvwmtbjhbifnq'; // Your Gmail password or app-specific password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            Log::info('SMTP settings configured');

            // Recipients
            $mail->setFrom('testprintmysproject@gmail.com', 'Your Name');
            $mail->addAddress($this->to);

            Log::info('Recipients configured');

            // Content
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body    = $this->message;

            Log::info('Email content set');

            $mail->send();

            Log::info('Email sent successfully to ' . $this->to);
        } catch (Exception $e) {
            Log::error('Failed to send email: ' . $mail->ErrorInfo);
            Log::error('Exception: ' . $e->getMessage());
        }
    }
}

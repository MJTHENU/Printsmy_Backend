<?php
// app/Mail/PHPMailerTransport.php
namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use Swift_Mime_SimpleMessage;
use Swift_Transport;
use Swift_Events_EventListener;

class PHPMailerTransport implements Swift_Transport
{
    protected $phpMailer;

    public function __construct(PHPMailer $phpMailer)
    {
        $this->phpMailer = $phpMailer;
    }

    public function isStarted()
    {
        return true;
    }

    public function start()
    {
        // No action needed to start
    }

    public function stop()
    {
        // No action needed to stop
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->phpMailer->isSMTP();
        $this->phpMailer->Host = config('mail.mailers.smtp.host');
        $this->phpMailer->SMTPAuth = true;
        $this->phpMailer->Username = config('mail.mailers.smtp.username');
        $this->phpMailer->Password = config('mail.mailers.smtp.password');
        $this->phpMailer->SMTPSecure = config('mail.mailers.smtp.encryption');
        $this->phpMailer->Port = config('mail.mailers.smtp.port');

        $this->phpMailer->setFrom($message->getFrom()[0]->getAddress(), $message->getFrom()[0]->getName());
        foreach ($message->getTo() as $address => $name) {
            $this->phpMailer->addAddress($address, $name);
        }
        $this->phpMailer->Subject = $message->getSubject();
        $this->phpMailer->Body = $message->getBody();

        if (!$this->phpMailer->send()) {
            throw new \Exception('Mail error: ' . $this->phpMailer->ErrorInfo);
        }

        return $this->phpMailer->getSentMIMEMessage();
    }

    public function registerPlugin(Swift_Events_EventListener $plugin)
    {
        // No action needed to register plugin
    }
}

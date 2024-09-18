<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Mail\PHPMailerTransport;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Mail::extend('phpmailer', function ($app) {
            $phpMailer = new PHPMailer(true);
            return new PHPMailerTransport($phpMailer);
        });
    }
}

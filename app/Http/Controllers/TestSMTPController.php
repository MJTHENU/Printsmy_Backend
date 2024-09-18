<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestSMTPController extends Controller
{
 function testConnection() {
   $hostname = 'smtp.gmail.com';
$port = 587;
$timeout = 10;
$socket = fsockopen($hostname, $port, $errno, $errstr, $timeout);
if (!$socket) {
    echo "Connection failed: $errstr ($errno)\n";
} else {
    echo "Connection successful!\n";
    fclose($socket);
}
}
}

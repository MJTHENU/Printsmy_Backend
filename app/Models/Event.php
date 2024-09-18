<?php

namespace App\Models;

use App\Models\Design;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', // Add 'title' to the fillable array
        'description',
        'event_date',
        'status',
    ];

    public function design()
    {
        return $this->hasOne(Design::class);
    }
}

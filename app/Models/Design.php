<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_design';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'images',
        'header_image',
        'logo',
    ];

    /**
     * Get the event that owns the design.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

   
}

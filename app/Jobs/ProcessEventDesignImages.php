<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessEventDesignImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventDesign;
    protected $images;
    protected $folderName;
    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        
        $this->eventDesign = $eventDesign;
        $this->images = $images;
        $this->folderName = $folderName;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
          $folderPath = 'public/' . $this->folderName;
        $newImagePaths = [];

        foreach ($this->images as $base64Image) {
            $filename = uniqid() . '_' . time() . '.png';
            $decodedImage = base64_decode($base64Image);
            Storage::disk('local')->put($folderPath . '/' . $filename, $decodedImage);
            $newImagePaths[] = $this->folderName . '/' . $filename;
        }

        $this->eventDesign->images = json_encode($newImagePaths);
        $this->eventDesign->save();
    }
}

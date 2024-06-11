<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dinosaur extends Model
{
    use HasFactory;

    protected $table = 'dinosaurs';

    protected $fillable = ['name', 'image_url', 'period', 'habitat', 'type'];
}

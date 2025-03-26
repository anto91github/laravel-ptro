<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsPernyataan extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'logs_pernyataan';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment
}

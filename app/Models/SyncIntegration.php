<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncIntegration extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'synchronize_integration';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment

    protected $guarded = [];
}

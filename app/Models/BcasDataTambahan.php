<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasDataTambahan extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_nasabah_data_tambahan';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment
    public $timestamps = false;

    protected $guarded = [];
}

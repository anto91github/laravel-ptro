<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasNasabahRekening extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_nasabah_rekening';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment
    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasDataPekerjaan extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bca_nasabah_data_pekerjaan';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment
    public $timestamps = false;

    protected $guarded = [];

}

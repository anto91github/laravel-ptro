<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasNasabahBO extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_nasabah_beneficiary_owner';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment
    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasNasabahTTD extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_nasabah_ttd';

    public $timestamps = false;

    protected $guarded = [];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcaAhliWaris extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bca_nasabah_ahli_waris';

    public $timestamps = false;

    protected $guarded = [];
}

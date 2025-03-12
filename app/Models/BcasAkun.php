<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcasAkun extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_akun';

}

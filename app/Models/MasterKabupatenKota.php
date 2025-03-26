<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKabupatenKota extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'master_kabupaten_kota';
}

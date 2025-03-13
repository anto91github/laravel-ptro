<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    use HasFactory;
    protected $connection = 'sqlsrv';
    protected $table = 'export_log';
    public $timestamps = false;
    protected $primaryKey = 'id';

    protected $guarded = [];
}

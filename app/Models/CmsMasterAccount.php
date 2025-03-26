<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsMasterAccount extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'cms_master_account';

    protected $guarded = [];
}

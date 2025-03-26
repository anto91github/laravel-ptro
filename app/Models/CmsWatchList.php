<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsWatchList extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'cms_watchlist_names';
}

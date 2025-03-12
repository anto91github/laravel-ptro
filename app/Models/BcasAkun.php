<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class BcasAkun extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_akun';

    protected $keyType = 'string'; // Mengatur tipe kunci menjadi string
    public $incrementing = false; // Menonaktifkan auto-increment

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid(); // Menghasilkan UUID saat membuat model
        });
    }

}

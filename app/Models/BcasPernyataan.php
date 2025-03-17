<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class BcasPernyataan extends Model
{
    use HasFactory;
    protected $connection = 'pgsql';
    protected $table = 'bcas_nasabah_pernyataan';

    protected $keyType = 'integer'; // Mengatur tipe kunci menjadi string
    public $incrementing = true; // Menonaktifkan auto-increment

    protected $guarded = [];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         $model->id = (string) Str::uuid(); // Menghasilkan UUID saat membuat model
    //     });
    // }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Walimurid extends Model
{
    protected $table = 'wali_siswa';
    protected $fillable = ['id_users', 'nama', 'alamat', 'status'];
}

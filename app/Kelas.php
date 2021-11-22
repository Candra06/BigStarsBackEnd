<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $fillable = ['id_mapel', 'id_guru', 'id_siswa', 'spp', 'fee_guru', 'status'];
}

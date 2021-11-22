<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetailKelas extends Model
{
    protected $table = 'detail_kelas';
    protected $fillable = ['id_kelas', 'hari', 'jam_mulai','jam_selesai'];
}

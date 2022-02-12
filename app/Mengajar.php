<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mengajar extends Model
{
    protected $table = 'mengajar';
    protected $fillable = ['id_guru', 'id_kelas', 'tipe', 'spp', 'fee_pengajar', 'poin_siswa', 'status', 'file_materi', 'jurnal', 'latitude','longitude','materi'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotifikasiDetail extends Model
{
    protected $table = 'notifikasi_detail';
    protected $fillable = ['id_notif', 'id_penerima', 'status'];
}

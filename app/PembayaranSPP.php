<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranSPP extends Model
{
    protected $table = 'pembayaran_spp';
    protected $fillable = ['no_invoice', 'tagihan_bulan', 'id_siswa', 'jumlah', 'status', 'created_by', 'updated_by', 'keterangan'];
}

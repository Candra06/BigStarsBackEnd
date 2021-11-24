<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranFEE extends Model
{
    protected $table = 'pembayaran_fee';
    protected $fillable = ['no_invoice', 'tagihan_bulan', 'id_guru', 'jumlah', 'status', 'created_by', 'updated_by'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Referal extends Model
{
    protected $table = 'referal';
    protected $fillable = ['reff_id', 'id_siswa', 'status'];
}

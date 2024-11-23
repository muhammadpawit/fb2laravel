<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelolaPoKirimSetorModel extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table='kelolapo_kirim_setor';

    function PO() {
        return $this->belongsTo(ProduksiPoModel::class,'id_produksi_po','idpo');
    }
}

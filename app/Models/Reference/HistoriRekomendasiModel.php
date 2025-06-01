<?php

namespace App\Models\Reference;

use App\Models\Auth\MahasiswaModel;
use Illuminate\Database\Eloquent\Model;

class HistoriRekomendasiModel extends Model
{
    protected $table = 't_histori_rekomendasi';
    protected $primaryKey = 'id_histori';
    
    protected $fillable = [
        'id_mahasiswa',
        'id_lowongan',
        'id_preferensi',
        'id_periode',
        'ranking'
    ];
    
    // Relasi ke mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaModel::class, 'id_mahasiswa', 'id_mahasiswa');
    }
    
    // Relasi ke lowongan
    public function lowongan()
    {
        return $this->belongsTo(LowonganMagangModel::class, 'id_lowongan', 'id_lowongan');
    }
}
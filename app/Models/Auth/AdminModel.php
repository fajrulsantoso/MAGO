<?php

namespace App\Models\Auth;

use App\Models\Reference\PerusahaanModel;
use App\Models\UserModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class AdminModel extends Model
{
    use HasFactory, Notifiable;

    // m_admin
    // + id_admin: String (PK)
    // + id_user: int (FK)
    // + nip: String

    protected $table = 'm_admin';
    protected $primaryKey = 'id_admin';
    protected $fillable = [
        'id_user',
        'nip',
    ];
    protected $hidden = [
        'id_user',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'id_user');
    }

    public function perusahaan(): HasMany
    {
        return $this->hasMany(PerusahaanModel::class, 'id_admin', 'id_admin');

    }

    public function admin()
{
    return $this->belongsTo(\App\Models\Auth\AdminModel::class, 'id_admin', 'id_admin')
                ->with('user'); // tambahkan eager loading ke user
}
}

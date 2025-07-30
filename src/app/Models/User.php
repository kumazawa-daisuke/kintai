<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    // 勤怠（1対多）
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // 勤怠修正申請（1対多：申請者として）
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class, 'user_id');
    }

    // 承認した修正申請
    public function approvedRequests()
    {
        return $this->hasMany(CorrectionRequest::class, 'approved_by');
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin'; // role カラムが 'admin' の場合
    }
}

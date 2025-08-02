<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'break_times';

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    // Attendanceとのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // ===== 時刻は常に H:i 形式で保存 =====

    public function setBreakStartAttribute($value)
    {
        $this->attributes['break_start'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function setBreakEndAttribute($value)
    {
        $this->attributes['break_end'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }

    // ====== 表示用アクセサ（オプション） ======

    // 休憩開始の表示用（H:i形式で出力）
    public function getBreakStartDisplayAttribute()
    {
        return $this->break_start ? Carbon::parse($this->break_start)->format('H:i') : '-';
    }

    // 休憩終了の表示用（H:i形式で出力）
    public function getBreakEndDisplayAttribute()
    {
        return $this->break_end ? Carbon::parse($this->break_end)->format('H:i') : '-';
    }
}

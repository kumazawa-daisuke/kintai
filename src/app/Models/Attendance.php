<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'break_time',
        'total_time',
        'status',
        'reason',
    ];

    // ===== リレーション =====

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    // ====== 時刻は常に H:i 形式で保存 ======

    public function setClockInAttribute($value)
    {
        $this->attributes['clock_in'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }

    public function setClockOutAttribute($value)
    {
        $this->attributes['clock_out'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }

    // ===== 合計時間・休憩時間 再計算 =====

    /**
     * 勤怠の休憩時間・合計時間を再計算してモデルに反映
     * 保存時は "G:i"形式（1:00、0:30、など先頭ゼロなし）でセット
     */
    public function recalculateTimes(): void
    {
        $totalBreakMinutes = $this->breakTimes->reduce(function ($carry, $break) {
            if ($break->break_start && $break->break_end) {
                $start = Carbon::parse($break->break_start);
                $end = Carbon::parse($break->break_end);
                return $carry + $end->diffInMinutes($start);
            }
            return $carry;
        }, 0);

        // 休憩時間（合計）を「G:i」形式（例: 1:00, 0:30, ...）で保存
        $this->break_time = $totalBreakMinutes > 0
            ? sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
            : null;

        if ($this->clock_in && $this->clock_out) {
            $workMinutes = Carbon::parse($this->clock_out)->diffInMinutes(Carbon::parse($this->clock_in));
            $realWork = $workMinutes - $totalBreakMinutes;
            $this->total_time = $realWork > 0
                ? sprintf('%d:%02d', floor($realWork / 60), $realWork % 60)
                : null;
        } else {
            $this->total_time = null;
        }
    }

    // ====== 表示用アクセサ ======

    // 合計勤務時間（1:00のようなゼロなしで取得）
    public function getTotalTimeDisplayAttribute()
    {
        if ($this->total_time) {
            [$h, $m] = explode(':', $this->total_time);
            return intval($h) . ':' . $m;
        }
        return '';
    }

    // 合計休憩時間（1:00のようなゼロなしで取得）
    public function getBreakTimeDisplayAttribute()
    {
        // breakTimesリレーションがロード済みの場合のみ、その場で計算
        if ($this->breakTimes && $this->breakTimes->count()) {
            $totalBreakMinutes = $this->breakTimes->reduce(function ($carry, $break) {
                if ($break->break_start && $break->break_end) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    return $carry + $end->diffInMinutes($start);
                }
                return $carry;
            }, 0);
            if ($totalBreakMinutes > 0) {
                $h = floor($totalBreakMinutes / 60);
                $m = $totalBreakMinutes % 60;
                return intval($h) . ':' . str_pad($m, 2, '0', STR_PAD_LEFT);
            }
        }
        // DBカラムのbreak_timeもある場合（旧実装の互換）
        if ($this->break_time) {
            [$h, $m] = explode(':', $this->break_time);
            return intval($h) . ':' . $m;
        }
        return '';
    }
}

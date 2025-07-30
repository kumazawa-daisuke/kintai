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

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 修正申請とのリレーション
    public function correctionRequests()
    {
        return $this->hasMany(CorrectionRequest::class);
    }

    // 休憩（BreakTime）とのリレーション
    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class, 'attendance_id');
    }

    /**
     * 勤怠の休憩時間・合計時間を再計算してモデルに反映
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

        // 休憩時間を "HH:MM" 形式で保存
        $this->break_time = $totalBreakMinutes > 0
            ? sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60)
            : null;

        if ($this->clock_in && $this->clock_out) {
            $workMinutes = Carbon::parse($this->clock_out)->diffInMinutes(Carbon::parse($this->clock_in));
            $realWork = $workMinutes - $totalBreakMinutes;
            $this->total_time = $realWork > 0
                ? sprintf('%02d:%02d', floor($realWork / 60), $realWork % 60)
                : null;
        } else {
            $this->total_time = null;
        }
    }
}

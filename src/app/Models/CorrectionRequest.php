<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'date',
        'request_type',
        'reason',
        'status',
        'approved_by',
        'clock_in_before',
        'clock_in_after',
        'clock_out_before',
        'clock_out_after',
        'reason_before',
        'reason_after',
        'breaks_before',
        'breaks_after',
    ];

    protected $casts = [
        'breaks_before' => 'array',
        'breaks_after' => 'array',
    ];

    // ===== リレーション =====
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ===== 時刻は常に H:i 形式で保存 =====

    public function setClockInBeforeAttribute($value)
    {
        $this->attributes['clock_in_before'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }
    public function setClockInAfterAttribute($value)
    {
        $this->attributes['clock_in_after'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }
    public function setClockOutBeforeAttribute($value)
    {
        $this->attributes['clock_out_before'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }
    public function setClockOutAfterAttribute($value)
    {
        $this->attributes['clock_out_after'] = $value ? Carbon::parse($value)->format('H:i') : null;
    }

    // ===== breaks_before / breaks_after も時:分で保存（オプション） =====

    public function setBreaksBeforeAttribute($value)
    {
        // 配列ならjson_encode、nullはnull
        $this->attributes['breaks_before'] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }
    public function setBreaksAfterAttribute($value)
    {
        $this->attributes['breaks_after'] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
    }


    /**
     * breaks配列を「時:分」形式に変換してjson_encode
     */
    protected function formatBreaksArray($breaks)
    {
        if (empty($breaks) || !is_array($breaks)) return [];

        $formatted = [];
        foreach ($breaks as $break) {
            $break_start = !empty($break['break_start']) ? \Carbon\Carbon::parse($break['break_start'])->format('H:i') : null;
            $break_end   = !empty($break['break_end'])   ? \Carbon\Carbon::parse($break['break_end'])->format('H:i') : null;
            if ($break_start || $break_end) {
                $formatted[] = [
                    'break_start' => $break_start,
                    'break_end'   => $break_end,
                ];
            }
        }
        return !empty($formatted) ? $formatted : null;
    }

    // ===== 表示用アクセサ（任意） =====

    public function getClockInBeforeDisplayAttribute()
    {
        return $this->clock_in_before ?? '-';
    }
    public function getClockOutBeforeDisplayAttribute()
    {
        return $this->clock_out_before ?? '-';
    }
    public function getClockInAfterDisplayAttribute()
    {
        return $this->clock_in_after ?? '-';
    }
    public function getClockOutAfterDisplayAttribute()
    {
        return $this->clock_out_after ?? '-';
    }
}

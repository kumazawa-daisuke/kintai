<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}

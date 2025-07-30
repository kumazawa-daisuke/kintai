<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
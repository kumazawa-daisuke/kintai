<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class CorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            // ユーザーの勤怠データからランダム2件
            $attendances = Attendance::where('user_id', $user->id)
                ->inRandomOrder()
                ->limit(2)
                ->get();

            foreach ($attendances as $attendance) {
                $clock_in  = $attendance->clock_in;
                $clock_out = $attendance->clock_out;

                $patterns = [
                    [
                        'request_type'     => 'clock_in',
                        'reason'           => '寝坊のため',
                        'clock_in_before'  => $clock_in,
                        'clock_in_after'   => $clock_in ? Carbon::parse($clock_in)->addMinutes(30)->format('H:i') : null,
                        'clock_out_before' => $clock_out,
                        'clock_out_after'  => $clock_out,
                        'reason_before'    => null,
                        'reason_after'     => null,
                        'breaks_before'    => null,
                        'breaks_after'     => null,
                    ],
                    [
                        'request_type'     => 'clock_out',
                        'reason'           => '残業対応',
                        'clock_in_before'  => $clock_in,
                        'clock_in_after'   => $clock_in,
                        'clock_out_before' => $clock_out,
                        'clock_out_after'  => $clock_out ? Carbon::parse($clock_out)->addMinutes(60)->format('H:i') : null,
                        'reason_before'    => null,
                        'reason_after'     => null,
                        'breaks_before'    => null,
                        'breaks_after'     => null,
                    ],
                    [
                        'request_type'     => 'reason',
                        'reason'           => '備考修正',
                        'clock_in_before'  => $clock_in,
                        'clock_in_after'   => $clock_in,
                        'clock_out_before' => $clock_out,
                        'clock_out_after'  => $clock_out,
                        'reason_before'    => '元の備考内容',
                        'reason_after'     => '修正後の備考内容',
                        'breaks_before'    => null,
                        'breaks_after'     => null,
                    ],
                    [
                        'request_type'     => 'break',
                        'reason'           => '休憩修正',
                        'clock_in_before'  => $clock_in,
                        'clock_in_after'   => $clock_in,
                        'clock_out_before' => $clock_out,
                        'clock_out_after'  => $clock_out,
                        'reason_before'    => null,
                        'reason_after'     => null,
                        // テスト用に休憩データ
                        'breaks_before'    => [
                            ['break_start' => '12:00', 'break_end' => '12:30'],
                        ],
                        'breaks_after'     => [
                            ['break_start' => '12:00', 'break_end' => '12:45'],
                        ],
                    ],
                ];

                $pattern = $patterns[array_rand($patterns)];

                // breaks系は配列ならjson_encode、nullならnull
                $data = [
                    'attendance_id'     => $attendance->id,
                    'user_id'           => $user->id,
                    'date'              => $attendance->date,
                    'request_type'      => $pattern['request_type'],
                    'reason'            => $pattern['reason'],
                    'status'            => 'pending',
                    'approved_by'       => null,
                    'clock_in_before'   => $pattern['clock_in_before'],
                    'clock_in_after'    => $pattern['clock_in_after'],
                    'clock_out_before'  => $pattern['clock_out_before'],
                    'clock_out_after'   => $pattern['clock_out_after'],
                    'reason_before'     => $pattern['reason_before'],
                    'reason_after'      => $pattern['reason_after'],
                    'breaks_before'     => is_array($pattern['breaks_before']) ? json_encode($pattern['breaks_before'], JSON_UNESCAPED_UNICODE) : $pattern['breaks_before'],
                    'breaks_after'      => is_array($pattern['breaks_after']) ? json_encode($pattern['breaks_after'], JSON_UNESCAPED_UNICODE) : $pattern['breaks_after'],
                ];

                try {
                    CorrectionRequest::create($data);
                    // echo "OK: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
                } catch (\Throwable $e) {
                    dd(
                        'NG: CorrectionRequest create error!',
                        $data,
                        [
                            'attendance' => $attendance->toArray(),
                            'user'       => $user->toArray(),
                        ],
                        $e->getMessage(),
                        $e->getTraceAsString()
                    );
                }
            }
        }
    }
}

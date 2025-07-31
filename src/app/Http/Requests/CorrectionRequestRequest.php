<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequestRequest extends FormRequest
{
    public function authorize() { return true; }

    public function rules()
    {
        return [
            'reason'    => 'required|string|max:255',
            'clock_in'  => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'clock_out' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required'    => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.regex'       => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'   => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.regex'      => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required'      => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function($validator) {
            $clock_in  = $this->input('clock_in');
            $clock_out = $this->input('clock_out');

            $hasClockError      = false; // ①用
            $hasBreakStartError = false; // ②用
            $hasBreakEndError   = false; // ③用

            // ① 出勤 > 退勤 のとき
            if ($clock_in && $clock_out && $clock_in >= $clock_out) {
                $hasClockError = true;
            }

            // 休憩バリデーション
            $breaks = $this->input('break_times', []);
            foreach ($breaks as $break) {
                $start = $break['break_start'] ?? null;
                $end   = $break['break_end'] ?? null;

                // どちらかだけ入力時は無視（入力揃ってる場合のみバリデーション）
                if ($start && $end) {
                    // ② 休憩開始が勤務時間外（出勤前or退勤後）
                    if ($clock_in && $start < $clock_in) $hasBreakStartError = true;
                    if ($clock_out && $start > $clock_out) $hasBreakStartError = true;

                    // ③ 休憩終了が退勤時間より後
                    if ($clock_out && $end > $clock_out) $hasBreakEndError = true;

                    // 休憩開始 > 終了 も休憩時間不適切でまとめて③として扱う
                    if ($start > $end) $hasBreakEndError = true;
                }
            }

            // エラーメッセージは各タイプ1回だけ
            if ($hasClockError) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }
            if ($hasBreakStartError) {
                $validator->errors()->add('break_start', '休憩時間が不適切な値です');
            }
            if ($hasBreakEndError) {
                $validator->errors()->add('break_end', '休憩時間もしくは退勤時間が不適切な値です');
            }
        });
    }
}

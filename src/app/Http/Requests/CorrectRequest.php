<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;


class CorrectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in'=>'required|date_format:H:i',
            'clock_out'=>'required|date_format:H:i',
            'break_start.*'=> 'required|date_format:H:i',
            'break_end.*' => 'required|date_format:H:i',
            'break_start_add' => 'nullable|date_format:H:i',
            'break_end_add' => 'nullable|date_format:H:i',
            'note'=> 'required|max:255'
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間は「HH:MM」形式で入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間は「HH:MM」形式で入力してください',
            'break_start.*.required' => '休憩時間を入力してください',
            'break_start.*.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'break_end.*.required' => '休憩時間を入力してください',
            'break_end.*.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'break_start_add.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'break_end_add.date_format' => '休憩時間は「HH:MM」形式で入力してください',
            'note.required' => '備考を記入してください',
            'note.max' => '255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $in = $this->input('clock_in');
            $out = $this->input('clock_out');
            $breakStarts = $this->input('break_start');
            $breakEnds = $this->input('break_end');
            $breakAddStart= $this->input('break_start_add');
            $breakAddEnd = $this->input('break_end_add');

            if (!$in || !$out) {
                return;
            }

            try {
                $clockIn = Carbon::createFromFormat('H:i', $in);
                $clockOut = Carbon::createFromFormat('H:i', $out);

                if ($clockIn->greaterThan($clockOut)) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                    return;
                }

                if (is_array($breakStarts) && is_array($breakEnds)) {
                    foreach ($breakStarts as $i => $start) {
                        if (empty($start)) continue;

                        $startTime = Carbon::createFromFormat('H:i', $start);

                        if ($startTime->lessThan($clockIn) || $startTime->greaterThan($clockOut)) {
                            $validator->errors()->add("break_start.$i", '休憩時間が勤務時間外です');
                        }
                    }

                    foreach ($breakEnds as $i => $end) {
                        if (empty($end)) continue;

                        $endTime = Carbon::createFromFormat('H:i', $end);

                        if ($endTime->lessThan($clockIn) || $endTime->greaterThan($clockOut)) {
                            $validator->errors()->add("break_end.$i", '休憩時間が勤務時間外です');
                        }
                    }
                }

                if($breakAddStart && $breakAddEnd){
                    $addStart = Carbon::createFromFormat('H:i', $breakAddStart);
                    $addEnd = Carbon::createFromFormat('H:i', $breakAddEnd);

                    if($addStart->lessThan($clockIn) || $addStart->greaterThan($clockOut)){
                        $validator->errors()->add("break_start_add", '休憩時間が勤務時間外です');
                    }

                    if ($addEnd->lessThan($clockIn) || $addEnd->greaterThan($clockOut)) {
                        $validator->errors()->add("break_end_add", '休憩時間が勤務時間外です');
                    }
                }

                if (($breakAddStart && !$breakAddEnd) || (!$breakAddStart && $breakAddEnd)) {
                    $validator->errors()->add('break_start_add', '休憩開始と休憩終了の両方を入力してください');
                }
            } catch (\Exception $e) {
                // 無視する（既にバリデーションで処理される想定なので）
            }
        });
    }

}

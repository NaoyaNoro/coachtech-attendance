<?php

namespace App\Http\Requests;

use Illuminate\Validation\Validator;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends FortifyLoginRequest
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
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if (!$validator->errors()->has('email') && !$validator->errors()->has('password')) {
                if($this->is('login')){
                    $user = User::where('email', $this->input('email'))->first();
                    if (!$user || !Hash::check($this->input('password'), $user->password)) {
                        $validator->errors()->add('email', 'ログイン情報が登録されていません');
                    }
                }
                if($this->is('admin/login')){
                    $admin=Admin::where('email', $this->input('email'))->first();
                    if (!$admin || !Hash::check($this->input('password'), $admin->password)) {
                        $validator->errors()->add('email', 'ログイン情報が登録されていません');
                    }
                }
            }
        });
    }
}

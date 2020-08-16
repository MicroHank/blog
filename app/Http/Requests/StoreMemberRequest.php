<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true ;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account' => 'required|min:3|max:12',
            'password1' => 'required|min:3|max:10',
            'password2' => 'required|min:3|max:10|same:password1',
            'username' => 'required|min:3|max:10',
        ];
    }

    /**
     * 覆寫 messages，自訂訊息
     *  // $errors = [ "account" => [0 => "Account is required"], "password2" => [0 => "Password length must be greater than 3", 1 => "Password must be the same"] ]
     */
    public function messages()
    {
        return [
            'account.min' => 'Account is required',
            'password1.min' => 'Password length must be greater than 3',
            'password2.min' => 'Password length must be greater than 3',
            'password2.same' => 'Password must be the same',
            'username.required' => 'Username is required',
            'username.min' => 'Username length must be greater than 3',
        ];
    }

    /**
     * 覆寫 response，如果有任何錯誤 就會被導向。
     * @param $errors Array, 驗證錯誤內容 // $errors = [ "account" => [0 => "validation.required", 1 => "..."] ]
     */
    public function response(array $errors)
    {
        // 帳號
        if (isset($errors['account'])) {
            return redirect()->back()->withInput()->with([
                'status' => trans('message.validate.illegal', ['col' => 'Account']),
                'errors' => $errors,
            ]) ;
        }
        // 密碼1 長度
        if (isset($errors['password1'])) {
            return redirect()->back()->withInput()->with([
                'status' => trans('message.validate.illegal', ['col' => 'Password']),
                'errors' => $errors,
            ]) ;
        }
        // 密碼2 長度, 與密碼1 相等
        if (isset($errors['password2'])) {
            return redirect()->back()->withInput()->with([
                'status' => trans('message.validate.password', ['col' => 'Password']),
                'errors' => $errors,
            ]) ;
        }
        // 會員名稱
        if (isset($errors['username'])) {
            return redirect()->back()->withInput()->with([
                'status' => trans('message.validate.length', ['col' => 'Username']),
                'errors' => $errors,
            ]) ;
        }
    }
}

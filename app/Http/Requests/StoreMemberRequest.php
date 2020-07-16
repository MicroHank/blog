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
        return false;
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
            'password2' => 'required|min:3|max:10',
            'username' => 'required|min:3|max:10',
        ];
    }
}

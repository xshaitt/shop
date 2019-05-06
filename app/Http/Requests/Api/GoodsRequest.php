<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GoodsRequest extends FormRequest
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
            'id' => 'required|numeric',
        ];
    }


    public function messages(){
        return [
            'seller_id.required'      =>'seller_id必须填写',
            'seller_id.numeric'      =>'seller_id必须是整形!',
        ];
    }

}

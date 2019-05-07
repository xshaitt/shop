<?php

namespace App\Http\Requests;

use App\Http\Service\ApiService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;


class BaseRequest extends FormRequest
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
            'seller_id'                => 'bail|required|numeric',
            'page'                     => 'bail|nullable|numeric',
            'page_size'                => 'bail|nullable|numeric',
            'product_id'               => 'required|numeric',
            'product_attribute_id'     => 'required|numeric',
        ];
    }


    public function messages(){
        return [
            'seller_id.required'              => 41001,
            'seller_id.numeric'               => 42001,
            'page.numeric'                    => 40001,
            'page_size.numeric'               => 40002,
            'product_id.required'             => 41002,
            'product_id.numeric'              => 42002,
            'product_attribute_id.required'   => 41003,
            'product_attribute_id.numeric'    => 42003,
        ];
    }


    /**
     * 自定义错误信息
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(ApiService::error($validator->errors()->first(), null)));
    }

    /**
     * 参数校验前处理--剔除不需要校验的参数
     *
     * @param ValidationFactory $factory
     * @return Validator
     */
    protected function createDefaultValidator(ValidationFactory $factory){
        //要验证的参数
        $params = array_keys($this->validationData());
        //验证规则
        $rules = $this->container->call([$this, 'rules']);
        //剔除不需要验证的参数
        foreach ($rules as $k => $v) {
            if (!in_array($k, $params)) {
                unset($rules[$k]);
            }
        }

        return $factory->make(
            $this->validationData(), $rules,
            $this->messages(), $this->attributes()
        );
    }

}

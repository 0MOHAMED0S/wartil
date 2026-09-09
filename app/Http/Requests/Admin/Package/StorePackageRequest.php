<?php

namespace App\Http\Requests\Admin\Package;

use Illuminate\Foundation\Http\FormRequest;

class StorePackageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:packages,name',
            ],

            'egypt_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'arab_price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'foreign_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'show_in_egypt' => ['boolean'],
            'show_in_arab' => ['boolean'],
            'show_in_foreign' => ['boolean'],

            'discount' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],

            'base_minutes' => [
                'required',
                'integer',
                'min:1',
            ],

            'bonus_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'validity_days' => [
                'required',
                'integer',
                'min:1',
            ],

            'description' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الباقة مطلوب',
            'name.unique' => 'اسم الباقة مستخدم بالفعل',
            'name.max' => 'اسم الباقة يجب ألا يتجاوز 255 حرفًا',

            'egypt_price.required' => 'سعر الباقة في مصر مطلوب',
            'egypt_price.numeric' => 'سعر الباقة في مصر يجب أن يكون رقمًا',
            'arab_price.required' => 'سعر الباقة في الدول العربية مطلوب',
            'arab_price.numeric' => 'سعر الباقة في الدول العربية يجب أن يكون رقمًا',
            'foreign_price.required' => 'سعر الباقة في الدول الأجنبية مطلوب',
            'foreign_price.numeric' => 'سعر الباقة في الدول الأجنبية يجب أن يكون رقمًا',

            'discount.integer' => 'نسبة الخصم يجب أن تكون رقمًا صحيحًا',
            'discount.max' => 'نسبة الخصم لا يمكن أن تتجاوز 100%',

            'base_minutes.required' => 'عدد الدقائق الأساسية مطلوب',
            'base_minutes.min' => 'عدد الدقائق الأساسية يجب أن يكون أكبر من صفر',

            'bonus_minutes.integer' => 'الدقائق الإضافية يجب أن تكون رقمًا صحيحًا',

            'validity_days.required' => 'مدة الصلاحية مطلوبة',
            'validity_days.min' => 'مدة الصلاحية يجب أن تكون يومًا واحدًا على الأقل',

            'description.required' => 'وصف الباقة مطلوب',
        ];
    }
}

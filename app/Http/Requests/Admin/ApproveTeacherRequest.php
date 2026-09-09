<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveTeacherRequest extends FormRequest
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
            'category_id' => [
                'required',
                'exists:teacher_categories,id',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'profile_photo' => [
                
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            // 👇 التأكد أن الإيميل غير موجود مسبقًا
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'تحديد الفئة مطلوب',
            'category_id.exists' => 'الفئة المحددة غير موجودة',

            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 8 أحرف',

            'profile_photo.required' => 'الصورة الشخصية مطلوبة',
            'profile_photo.image' => 'الملف يجب أن يكون صورة',
            'profile_photo.mimes' => 'الصورة يجب أن تكون بصيغة jpeg أو png أو jpg أو gif',
            'profile_photo.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',

            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'هذا البريد الإلكتروني مستخدم بالفعل',
        ];
    }
}

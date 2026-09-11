<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 👤 البيانات الأساسية
            'name'                => ['sometimes', 'string', 'min:3', 'max:255'],
            'phone'               => ['sometimes', 'string', 'min:6', 'max:20'],
            'address'             => ['sometimes', 'string', 'min:5', 'max:500'],
            'qualification'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'professional_status' => ['sometimes', 'nullable', 'string', 'max:255'],
            'profile_photo_path'  => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB
            ],

            // 📖 تفضيلاتي التعليمية
            'birth_date'                 => ['sometimes', 'nullable', 'date', 'before:today'],
            'reading_level'              => ['sometimes', 'nullable', 'string', 'max:255'],
            'preferred_teacher_language' => ['sometimes', 'nullable', 'string', 'max:255'],
            'reading_track'              => ['sometimes', 'nullable', 'string', 'max:255'],
            'memorized_amount'           => ['sometimes', 'nullable', 'string', 'max:255'],

            // ⏱️ تفضيلات الجلسة
            'plan_name'                  => ['sometimes', 'nullable', 'string', 'max:255'],
            'reading_type'               => ['sometimes', 'nullable', 'string', 'max:255'],
            'teacher_response_speed'     => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'الاسم يجب ألا يقل عن 3 أحرف.',
            'name.max' => 'الاسم طويل جدًا.',

            'phone.min' => 'رقم الهاتف قصير جدًا.',
            'phone.max' => 'رقم الهاتف طويل جدًا.',

            'address.min' => 'العنوان قصير جدًا.',
            'address.max' => 'العنوان طويل جدًا.',

            // رسائل الصورة الشخصية
            'profile_photo_path.image' => 'الملف يجب أن يكون صورة.',
            'profile_photo_path.mimes' => 'الصورة يجب أن تكون بصيغة jpeg أو png أو jpg أو gif.',
            'profile_photo_path.max'   => 'حجم الصورة يجب ألا يزيد عن 2 ميجابايت.',

            // رسائل الحقول الجديدة (تمت إضافتها للحماية من تجاوز الطول المسموح)
            'birth_date.date'                => 'تاريخ الميلاد غير صحيح.',
            'birth_date.before'              => 'تاريخ الميلاد يجب أن يكون في الماضي.',
            'reading_level.max'              => 'مستوى القراءة طويل جدًا.',
            'preferred_teacher_language.max' => 'اسم لغة المعلم طويل جدًا.',
            'reading_track.max'              => 'مسار القراءة طويل جدًا.',
            'memorized_amount.max'           => 'مقدار المحفوظ طويل جدًا.',
            'plan_name.max'                  => 'اسم الخطة طويل جدًا.',
            'reading_type.max'               => 'نوع القراءة طويل جدًا.',
            'teacher_response_speed.max'     => 'سرعة الرد طويلة جدًا.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'status'  => false,
            'message' => 'بيانات غير صحيحة.',
            'errors'  => $validator->errors()
        ], 422);

        throw new HttpResponseException($response);
    }
}

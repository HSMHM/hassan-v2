<?php

namespace App\Http\Requests;

use App\Rules\TurnstileToken;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'mobile' => ['nullable', 'string', 'regex:/^[\d\+\-\(\)\s]{7,20}$/'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'cf_turnstile_response' => ['nullable', 'string', new TurnstileToken],
        ];
    }

    public function messages(): array
    {
        if (app()->getLocale() === 'ar') {
            return [
                'name.required' => 'الاسم مطلوب',
                'name.min' => 'الاسم قصير جداً',
                'email.required' => 'البريد الإلكتروني مطلوب',
                'email.email' => 'البريد الإلكتروني غير صحيح',
                'message.required' => 'الرسالة مطلوبة',
                'message.min' => 'الرسالة قصيرة جداً',
                'mobile.regex' => 'رقم الجوال غير صحيح',
            ];
        }

        return [
            'name.required' => 'Name is required',
            'name.min' => 'Name is too short',
            'email.required' => 'Email is required',
            'email.email' => 'Email is not valid',
            'message.required' => 'Message is required',
            'message.min' => 'Message is too short',
            'mobile.regex' => 'Mobile number is not valid',
        ];
    }
}

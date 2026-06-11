<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DemoAepsTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth handled by route middleware in a real app
    }

    /**
     * Field rules:
     *  - transactionId  : required client reference
     *  - tranType       : CW (cash withdrawal) | BE (balance enquiry) | MS (mini statement)
     *  - amount         : required ONLY for CW; must be absent/ignored otherwise
     *  - mobileNumber   : required 10-digit Indian mobile
     *  - aadhaarNumber  : required 12-digit Aadhaar
     */
    public function rules(): array
    {
        return [
            'transactionId' => ['required', 'string', 'max:64'],
            'tranType'      => ['required', 'string', 'in:CW,BE,MS'],
            'amount'        => ['required_if:tranType,CW', 'nullable', 'numeric', 'min:1', 'max:100000'],
            'mobileNumber'  => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'aadhaarNumber' => ['required', 'string', 'regex:/^\d{12}$/'],
            // Optional: in a real app the user comes from auth(); accepted here
            // so the demo is testable without an auth token.
            'userId'        => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required_if'  => 'The amount field is required when tranType is CW.',
            'mobileNumber.regex'  => 'The mobile number must be a valid 10-digit Indian number.',
            'aadhaarNumber.regex' => 'The aadhaar number must be exactly 12 digits.',
        ];
    }
}

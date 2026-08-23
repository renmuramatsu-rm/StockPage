<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SbiHoldingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required', 'string', 'max:10', 'exists:stocks,code',
                Rule::unique('sbi_holdings', 'code')->ignore($this->route('sbi_holding')),
            ],
            'shares' => 'required|integer|min:1',
            'average_acquisition_price' => 'required|numeric|min:0',
            'acquisition_date' => 'nullable|date',
            'account_type' => 'nullable|string|in:specific,general,nisa',
            'memo' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'この銘柄は既に保有銘柄として登録されています。追加購入した場合は、既存の行を編集して株数・取得単価を更新してください。',
        ];
    }
}

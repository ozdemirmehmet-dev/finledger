<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                   => ['required', 'string', 'max:255'],
            'currency'                => ['required', 'string', 'size:3'],
            'due_date'                => ['required', 'date', 'after:today'],
            'status'                  => ['sometimes', 'in:draft,sent,paid'],
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.description'     => ['required', 'string', 'max:255'],
            'items.*.quantity'        => ['required', 'integer', 'min:1'],
            'items.*.unit_price'      => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate'        => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }
}

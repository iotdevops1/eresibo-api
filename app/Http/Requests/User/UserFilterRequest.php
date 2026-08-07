<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'search' => ['nullable',
                'string',
                'max:100',
            ],

            'status' => ['nullable',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_SUSPENDED,
                    User::STATUS_LOCKED,
                    User::STATUS_DELETED,
                ]),
            ],

            'role_code' => ['nullable',
                Rule::in([
                    'SUPER_ADMIN',
                    'ADMIN',
                    'CUSTOMER',
                ]),
            ],

            'sort_by' => ['nullable',
                Rule::in([
                    'name',
                    'email',
                    'created_at',
                ]),
            ],

            'sort_order' => ['nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],
        ];
    }
}
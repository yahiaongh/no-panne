<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Provider;

use App\Enums\RequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequestStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([RequestStatus::EnRoute->value, RequestStatus::OnSite->value])],
        ];
    }
}
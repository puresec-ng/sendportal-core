<?php

namespace Sendportal\Base\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;

/**
 * @property-read string $subscriber
 */
class SubscriberUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('sendportal_subscribers', 'email')
                    ->ignore($this->subscriber, 'id')
                    ->where(static function (Builder $query) {
                        $query->where('workspace_id', Sendportal::currentWorkspaceId());
                    })
            ],
            'first_name' => [
                'max:255',
            ],
            'last_name' => [
                'max:255',
            ],
            'tags' => [
                'nullable',
                'array',
            ],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AssignMasterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
		return auth()->check() && auth()->user()->hasRole('dispatcher');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
			'master_id' => [
				'required',
				'integer',
				'exists:users,id',
				function ($attribute, $value, $fail) {
					$master = \App\Models\User::find($value);
					if (!$master || !$master->hasRole('master')) {
						$fail('Выбранный пользователь не является мастером.');
					}
				},
			],
        ];
    }

	/**
	 * Custom validation messages.
	 *
	 * @return array<string, string>
	 */
	public function messages(): array
	{
		return [
			'master_id.required' => 'Необходимо выбрать мастера для назначения',
			'master_id.integer' => 'ID мастера должен быть числовым значением',
			'master_id.exists' => 'Выбранный мастер не существует в системе',
		];
	}

	/**
	 * Prepare the data for validation.
	 *
	 * @return void
	 */
	protected function prepareForValidation(): void
	{
		$this->merge([
			'master_id' => (int) ($this->input('master_id') ?? 0),
		]);
	}
}

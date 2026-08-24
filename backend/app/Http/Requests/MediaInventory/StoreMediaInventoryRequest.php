<?php

namespace App\Http\Requests\MediaInventory;

use App\Models\MediaInventory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaInventoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', MediaInventory::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:media_categories,id'],
            'subcategory_id' => ['nullable', 'integer', 'exists:media_categories,id'],
            'frequency_id' => ['required', 'integer', 'exists:frequencies,id'],
            'language_id' => ['required', 'integer', 'exists:languages,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'key_insights' => ['nullable', 'array'],
            'key_insights.*.label' => ['required_with:key_insights.*.value', 'string', 'max:255'],
            'key_insights.*.value' => ['required_with:key_insights.*.label', 'string', 'max:255'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:pdf,docx,xlsx', 'max:10240'],
        ];
    }
}

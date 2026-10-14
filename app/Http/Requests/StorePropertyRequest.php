<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() && $this->user()->status === 'pending') {
            return false;
        }

        return $this->user() !== null;
    }

    protected function failedAuthorization()
    {
        if ($this->user() && $this->user()->status === 'pending') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()
                    ->route('dashboard.index', ['locale' => app()->getLocale()])
                    ->with('status_warning', 'حسابك قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك إضافة عقارات جديدة حتى يتم تفعيل حسابك من قبل الإدارة.')
            );
        }

        parent::failedAuthorization();
    }

    public function rules(): array
    {
        return [
            'reference_code' => ['nullable', 'string', 'max:60', 'unique:properties,reference_code'],
            'purpose' => ['required', 'in:sale,rent'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'property_type_id' => ['nullable', 'exists:property_types,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:100'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'translations' => ['required', 'array'],
            'translations.*.name' => ['required_without_all:translations.ar.name,translations.en.name', 'nullable', 'string', 'max:190'],
            'translations.*.slug' => ['nullable', 'string', 'max:190'],
            'translations.*.description' => ['nullable', 'string'],
            'floor_plan_name' => ['nullable', 'string', 'max:190'],
            'floor_plan_image' => ['nullable', 'image', 'max:10240'],
            'floor_plan_description' => ['nullable', 'string', 'max:1000'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ];
    }
}

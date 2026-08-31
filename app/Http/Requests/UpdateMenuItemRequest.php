<?php

namespace App\Http\Requests;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $menuItem = $this->route('menu_item');

        return $this->user()?->can('update', $menuItem) ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();
        $menuItem = $this->route('menu_item');

        return [
            'menu_id' => ['prohibited'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:menu_items,id',
                Rule::exists('menu_items', 'id')
                    ->where(fn ($query) => $query->where(
                        'menu_id',
                        $this->input('menu_id', $menuItem->menu_id)
                    ))
                    ->where('id', '!=', $menuItem->id),
            ],
            'title' => ['sometimes', 'string', 'max:150'],
            'type' => ['sometimes', 'string', 'max:30'],
            'content' => ['nullable', 'string'],
            'media_path' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:1000'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_id.integer' => 'El menú debe ser un número entero.',
            'menu_id.exists' => 'El menú seleccionado no existe.',
            'parent_id.integer' => 'El elemento padre debe ser un número entero.',
            'parent_id.exists' => 'El elemento padre seleccionado no existe o no pertenece a este menú.',
            'title.string' => 'El título debe ser texto.',
            'title.max' => 'El título no puede superar los :max caracteres.',
            'type.string' => 'El tipo debe ser texto.',
            'type.max' => 'El tipo no puede superar los :max caracteres.',
            'content.string' => 'El contenido debe ser texto.',
            'media_path.string' => 'La ruta del archivo debe ser texto.',
            'media_path.max' => 'La ruta del archivo no puede superar los :max caracteres.',
            'url.string' => 'La URL debe ser texto.',
            'url.max' => 'La URL no puede superar los :max caracteres.',
            'position.integer' => 'La posición debe ser un número entero.',
            'position.min' => 'La posición mínima permitida es :min.',
            'is_active.boolean' => 'El estado activo debe ser verdadero o falso.',
        ];
    }

    public function attributes(): array
    {
        return [
            'menu_id' => 'menú',
            'parent_id' => 'elemento padre',
            'title' => 'título',
            'type' => 'tipo',
            'content' => 'contenido',
            'media_path' => 'ruta de media',
            'url' => 'URL',
            'position' => 'posición',
            'is_active' => 'estado activo',
        ];
    }
}

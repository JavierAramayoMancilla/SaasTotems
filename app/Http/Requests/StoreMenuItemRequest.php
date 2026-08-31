<?php

namespace App\Http\Requests;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MenuItem::class) ?? false;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'menu_id' => ['required', 'integer', 'exists:menus,id', function ($attribute, $value, $fail) use ($user) {
                $menu = Menu::find($value);

                if (! $menu) {
                    return;
                }

                if (! $user->hasRole('superadmin') && (int) $menu->tenant_id !== (int) $user->tenant_id) {
                    $fail('El menú seleccionado no pertenece a tu tenant.');
                }
            }],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id', Rule::exists('menu_items', 'id')->where(fn ($query) => $query->where('menu_id', $this->input('menu_id')))],
            'title' => ['required', 'string', 'max:150'],
            'type' => ['required', 'string', 'max:30'],
            'content' => ['nullable', 'string'],
            'media_path' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:1000'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_id.required' => 'El menú es obligatorio.',
            'menu_id.integer' => 'El menú debe ser un número entero.',
            'menu_id.exists' => 'El menú seleccionado no existe.',
            'parent_id.integer' => 'El padre debe ser un número entero.',
            'parent_id.exists' => 'El elemento padre seleccionado no existe o no pertenece a este menú.',
            'title.required' => 'El título del elemento es obligatorio.',
            'title.string' => 'El título debe ser texto.',
            'title.max' => 'El título no puede superar los :max caracteres.',
            'type.required' => 'El tipo del elemento es obligatorio.',
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

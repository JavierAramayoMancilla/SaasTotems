<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Advertisement;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Menu::class);

        $query = Menu::query()
            ->with(['advertisement', 'items'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('advertisement_id')) {
            $query->where('advertisement_id', $request->advertisement_id);
        }

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)
            );
        }

        $menus = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Menus/Index', [
            'menus' => $menus,
            'filters' => $request->only([
                'search',
                'advertisement_id',
                'is_active',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Menu::class);

        return Inertia::render('Menus/Create');
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['tenant_id'] = $request->user()->tenant_id;

        Menu::create($validated);

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menú creado correctamente.');
    }

    public function show(Menu $menu): Response
    {
        $this->authorize('view', $menu);

        $menu->load([
            'advertisement',
            'items.parent',
            'items.children',
        ]);

        return Inertia::render('Menus/Show', [
            'menu' => $menu,
        ]);
    }

    public function edit(Menu $menu): Response
    {
        $this->authorize('update', $menu);

        $menu->load([
            'advertisement',
            'items',
        ]);

        return Inertia::render('Menus/Edit', [
            'menu' => $menu,
        ]);
    }

    public function update(
        UpdateMenuRequest $request,
        Menu $menu
    ): RedirectResponse {
        $this->authorize('update', $menu);

        $menu->update($request->validated());

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menú actualizado correctamente.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $this->authorize('delete', $menu);

        $menu->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('menus.index')
            ->with('success', 'Menú deshabilitado correctamente.');
    }
}
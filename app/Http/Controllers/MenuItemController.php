<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuItemController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', MenuItem::class);

        $query = MenuItem::query()
            ->with(['menu', 'parent', 'children'])
            ->orderBy('menu_id')
            ->orderBy('position');

        if ($request->filled('menu_id')) {
            $query->where('menu_id', $request->menu_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $menuItems = $query->paginate(15)->withQueryString();

        return Inertia::render('MenuItems/Index', [
            'menuItems' => $menuItems,
            'filters' => $request->only(['menu_id', 'search', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', MenuItem::class);

        return Inertia::render('MenuItems/Create');
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $menuItem = MenuItem::create($validated);

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'Elemento del menú creado correctamente.');
    }

    public function show(MenuItem $menuItem): Response
    {
        $this->authorize('view', $menuItem);

        $menuItem->load(['menu', 'parent', 'children']);

        return Inertia::render('MenuItems/Show', [
            'menuItem' => $menuItem,
        ]);
    }

    public function edit(MenuItem $menuItem): Response
    {
        $this->authorize('update', $menuItem);

        $menuItem->load(['menu', 'parent', 'children']);

        return Inertia::render('MenuItems/Edit', [
            'menuItem' => $menuItem,
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $this->authorize('update', $menuItem);

        $menuItem->fill($request->validated());
        $menuItem->save();

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'Elemento del menú actualizado correctamente.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->authorize('delete', $menuItem);

        $menuItem->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('menu-items.index')
            ->with('success', 'Elemento del menú deshabilitado correctamente.');
    }
}

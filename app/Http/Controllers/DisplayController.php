<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisplayRequest;
use App\Http\Requests\UpdateDisplayRequest;
use App\Models\Display;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Display::class);

        $query = Display::query()
            ->with([
                'displayAdvertisements.advertisement',
                'menus',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $displays = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Displays/Index', [
            'displays' => $displays,
            'filters' => $request->only([
                'search',
                'status',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Display::class);

        return Inertia::render('Displays/Create');
    }

    public function store(StoreDisplayRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['tenant_id'] = $request->user()->tenant_id;

        Display::create($validated);

        return redirect()
            ->route('displays.index')
            ->with('success', 'Display creado correctamente.');
    }

    public function show(Display $display): Response
    {
        $this->authorize('view', $display);

        $display->load([
            'displayAdvertisements.advertisement',
            'displayAdvertisements.adSchedules',
            'menus.items',
            'analyticsEvents',
        ]);

        return Inertia::render('Displays/Show', [
            'display' => $display,
        ]);
    }

    public function edit(Display $display): Response
    {
        $this->authorize('update', $display);

        $display->load([
            'displayAdvertisements.advertisement',
            'displayAdvertisements.adSchedules',
            'menus',
        ]);

        return Inertia::render('Displays/Edit', [
            'display' => $display,
        ]);
    }

    public function update(
        UpdateDisplayRequest $request,
        Display $display
    ): RedirectResponse {
        $this->authorize('update', $display);

        $display->update($request->validated());

        return redirect()
            ->route('displays.index')
            ->with('success', 'Display actualizado correctamente.');
    }

    public function destroy(Display $display): RedirectResponse
    {
        $this->authorize('delete', $display);

        $display->update([
            'status' => 'inactive',
        ]);

        return redirect()
            ->route('displays.index')
            ->with('success', 'Display deshabilitado correctamente.');
    }
}   
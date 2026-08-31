<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisplayAdvertisementRequest;
use App\Http\Requests\UpdateDisplayAdvertisementRequest;
use App\Models\DisplayAdvertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayAdvertisementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', DisplayAdvertisement::class);

        $query = DisplayAdvertisement::query()
            ->with(['display', 'advertisement'])
            ->orderByDesc('created_at');

        if ($request->filled('display_id')) {
            $query->where('display_id', $request->display_id);
        }

        if ($request->filled('advertisement_id')) {
            $query->where('advertisement_id', $request->advertisement_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $displayAdvertisements = $query->paginate(15)->withQueryString();

        return Inertia::render('DisplayAdvertisements/Index', [
            'displayAdvertisements' => $displayAdvertisements,
            'filters' => $request->only(['display_id', 'advertisement_id', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', DisplayAdvertisement::class);

        return Inertia::render('DisplayAdvertisements/Create');
    }

    public function store(StoreDisplayAdvertisementRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $displayAdvertisement = DisplayAdvertisement::create($validated);

        return redirect()
            ->route('display-advertisements.index')
            ->with('success', 'Asociación creada correctamente.');
    }

    public function show(DisplayAdvertisement $displayAdvertisement): Response
    {
        $this->authorize('view', $displayAdvertisement);

        $displayAdvertisement->load([
            'display',
            'advertisement',
            'adSchedules',
        ]);

        return Inertia::render('DisplayAdvertisements/Show', [
            'displayAdvertisement' => $displayAdvertisement,
        ]);
    }

    public function edit(DisplayAdvertisement $displayAdvertisement): Response
    {
        $this->authorize('update', $displayAdvertisement);

        $displayAdvertisement->load(['display', 'advertisement']);

        return Inertia::render('DisplayAdvertisements/Edit', [
            'displayAdvertisement' => $displayAdvertisement,
        ]);
    }

    public function update(UpdateDisplayAdvertisementRequest $request, DisplayAdvertisement $displayAdvertisement): RedirectResponse
    {
        $this->authorize('update', $displayAdvertisement);

        $displayAdvertisement->fill($request->validated());
        $displayAdvertisement->save();

        return redirect()
            ->route('display-advertisements.index')
            ->with('success', 'Asociación actualizada correctamente.');
    }

    public function destroy(DisplayAdvertisement $displayAdvertisement): RedirectResponse
    {
        $this->authorize('delete', $displayAdvertisement);

        $displayAdvertisement->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('display-advertisements.index')
            ->with('success', 'Asociación deshabilitada correctamente.');
    }
}

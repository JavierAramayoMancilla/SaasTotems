<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdvertisementRequest;
use App\Http\Requests\UpdateAdvertisementRequest;
use App\Models\Advertisement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdvertisementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Advertisement::class);

        $query = Advertisement::query()
            ->with(['displayAdvertisements.display'])
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $advertisements = $query->paginate(15)->withQueryString();

        return Inertia::render('Advertisements/Index', [
            'advertisements' => $advertisements,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Advertisement::class);

        return Inertia::render('Advertisements/Create');
    }

    public function store(StoreAdvertisementRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['tenant_id'] = $request->user()->tenant_id;

        $advertisement = Advertisement::create($validated);

        return redirect()
            ->route('advertisements.index')
            ->with('success', 'Publicidad creada correctamente.');
    }

    public function show(Advertisement $advertisement): Response
    {
        $this->authorize('view', $advertisement);

        $advertisement->load([
            'displayAdvertisements.display',
            'displayAdvertisements.adSchedules',
        ]);

        return Inertia::render('Advertisements/Show', [
            'advertisement' => $advertisement,
        ]);
    }

    public function edit(Advertisement $advertisement): Response
    {
        $this->authorize('update', $advertisement);

        $advertisement->load([
            'displayAdvertisements.display',
            'displayAdvertisements.adSchedules',
        ]);

        return Inertia::render('Advertisements/Edit', [
            'advertisement' => $advertisement,
        ]);
    }

    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): RedirectResponse
    {
        $this->authorize('update', $advertisement);

        $advertisement->fill($request->validated());
        $advertisement->save();

        return redirect()
            ->route('advertisements.index')
            ->with('success', 'Publicidad actualizada correctamente.');
    }

    public function destroy(Advertisement $advertisement): RedirectResponse
    {
        $this->authorize('delete', $advertisement);

        $advertisement->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('advertisements.index')
            ->with('success', 'Publicidad deshabilitada correctamente.');
    }
}

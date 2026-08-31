<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdScheduleRequest;
use App\Http\Requests\UpdateAdScheduleRequest;
use App\Models\AdSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdScheduleController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AdSchedule::class);

        $currentUser = $request->user();

        $query = AdSchedule::query()
            ->with([
                'displayAdvertisement.display',
                'displayAdvertisement.advertisement',
            ])
            ->orderByDesc('created_at');

        if (! $currentUser->hasRole('superadmin')) {
            $query->whereHas('displayAdvertisement.display', function ($q) use ($currentUser) {
                $q->where('tenant_id', $currentUser->tenant_id);
            });
        }

        if ($request->filled('display_advertisement_id')) {
            $query->where(
                'display_advertisement_id',
                $request->display_advertisement_id
            );
        }

        if ($request->filled('day_of_week')) {
            $query->where(
                'day_of_week',
                $request->day_of_week
            );
        }

        $adSchedules = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AdSchedules/Index', [
            'adSchedules' => $adSchedules,

            'filters' => $request->only([
                'display_advertisement_id',
                'day_of_week',
            ]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AdSchedule::class);

        return Inertia::render('AdSchedules/Create');
    }

    public function store(StoreAdScheduleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $adSchedule = AdSchedule::create($validated);

        return redirect()
            ->route('ad-schedules.index')
            ->with('success', 'Horario creado correctamente.');
    }

    public function show(AdSchedule $adSchedule): Response
    {
        $this->authorize('view', $adSchedule);

        $adSchedule->load([
            'displayAdvertisement.display',
            'displayAdvertisement.advertisement',
        ]);

        return Inertia::render('AdSchedules/Show', [
            'adSchedule' => $adSchedule,
        ]);
    }

    public function edit(AdSchedule $adSchedule): Response
    {
        $this->authorize('update', $adSchedule);

        $adSchedule->load([
            'displayAdvertisement.display',
            'displayAdvertisement.advertisement',
        ]);

        return Inertia::render('AdSchedules/Edit', [
            'adSchedule' => $adSchedule,
        ]);
    }

    public function update(UpdateAdScheduleRequest $request, AdSchedule $adSchedule): RedirectResponse
    {
        $this->authorize('update', $adSchedule);

        $adSchedule->fill($request->validated());
        $adSchedule->save();

        return redirect()
            ->route('ad-schedules.index')
            ->with('success', 'Horario actualizado correctamente.');
    }

}

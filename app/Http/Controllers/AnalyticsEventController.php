<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnalyticsEventRequest;
use App\Models\AnalyticsEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsEventController extends Controller
{
    /**
     * Mostrar eventos registrados.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AnalyticsEvent::class);

        $query = AnalyticsEvent::query()
            ->with([
                'display',
                'advertisement',
                'menuItem',
            ])
            ->orderByDesc('started_at');

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('display_id')) {
            $query->where('display_id', $request->display_id);
        }

        // El tenant se controla mediante TenantScope.
        // No confiamos en tenant_id enviado desde el frontend.

        $analyticsEvents = $query
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('AnalyticsEvents/Index', [
            'analyticsEvents' => $analyticsEvents,
            'filters' => $request->only([
                'event_type',
                'display_id',
            ]),
        ]);
    }

    /**
     * Registrar automáticamente un evento.
     */
    public function store(StoreAnalyticsEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $validated['tenant_id'] = $request->user()->tenant_id;

        AnalyticsEvent::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Evento registrado correctamente.');
    }

    /**
     * Mostrar un evento específico.
     */
    public function show(AnalyticsEvent $analyticsEvent): Response
    {
        $this->authorize('view', $analyticsEvent);

        $analyticsEvent->load([
            'display',
            'advertisement',
            'menuItem',
        ]);

        return Inertia::render('AnalyticsEvents/Show', [
            'analyticsEvent' => $analyticsEvent,
        ]);
    }
}
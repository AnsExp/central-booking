<?php

namespace App\Http\Controllers;

use App\Models\Route as RouteModel;
use App\Models\Station;
use App\Models\Train;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RouteController extends Controller
{
    public function store(Request $request): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $this->validateRoute($request);

        $route = RouteModel::create($this->extractRouteData($validated));
        $this->syncRouteStops($route, $validated['stops'] ?? []);
        $route->trains()->sync($validated['trains'] ?? []);

        return redirect()
            ->route('routes.view')
            ->with('route_status', 'Ruta registrada correctamente.');
    }

    public function update(Request $request, RouteModel $route): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $this->validateRoute($request);

        $route->update($this->extractRouteData($validated));
        $this->syncRouteStops($route, $validated['stops'] ?? []);
        $route->trains()->sync($validated['trains'] ?? []);

        return redirect()
            ->route('routes.view')
            ->with('route_status', 'Ruta actualizada correctamente.');
    }

    public function destroy(RouteModel $route): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $route->delete();

        return redirect()
            ->route('routes.view')
            ->with('route_status', 'Ruta eliminada correctamente.');
    }

    public function view(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $page = (int) $request->query('page', 1);
        $sort = (string) $request->query('sort', 'departure_time');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['origin', 'destination', 'departure_time', 'arrival_time'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'departure_time';
        }

        $query = RouteModel::query()->with(['originStation:id,name', 'destinationStation:id,name']);

        if ($sort === 'origin') {
            $query->join('station as origin_station', 'route.origin_station_id', '=', 'origin_station.id')
                ->select('route.*')
                ->orderBy('origin_station.name', $direction);
        } elseif ($sort === 'destination') {
            $query->join('station as destination_station', 'route.destination_station_id', '=', 'destination_station.id')
                ->select('route.*')
                ->orderBy('destination_station.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        return view('views.route-view', [
            'routesList' => $query->paginate(10, ['*'], 'page', $page)->appends([
                'sort' => $sort,
                'direction' => $direction,
            ]),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function form(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $routeId = $request->query('route', null);
        $route = null;
        if ($routeId) {
            $route = RouteModel::with([
                'stops' => fn ($query) => $query->orderBy('stop_order'),
                'trains:id',
            ])->find($routeId);
            if (!$route) {
                return response()->view('404', [], 404);
            }
        }

        $stations = Station::query()->orderBy('name')->get();
        $trains = Train::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('forms.route-form', [
            'route' => $route,
            'stations' => $stations,
            'trains' => $trains,
        ]);
    }

    private function validateRoute(Request $request): array
    {
        return $request->validate([
            'origin_station_id' => ['required', 'integer', 'exists:station,id', 'different:destination_station_id'],
            'destination_station_id' => ['required', 'integer', 'exists:station,id'],
            'departure_time' => ['required', 'date_format:H:i'],
            'arrival_time' => ['required', 'date_format:H:i'],
            'stops' => ['required', 'array', 'min:1'],
            'stops.*.station_id' => ['required', 'integer', 'exists:station,id', 'distinct'],
            'stops.*.stop_order' => ['required', 'integer', 'min:1', 'distinct'],
            'stops.*.arrival_time' => ['required', 'date_format:H:i'],
            'stops.*.departure_time' => ['required', 'date_format:H:i'],
            'trains' => ['nullable', 'array'],
            'trains.*' => ['integer', 'exists:train,id'],
        ], [
            'stops.required' => 'Debes registrar al menos una parada para la ruta.',
            'stops.*.station_id.distinct' => 'No se puede repetir una estación en las paradas.',
            'stops.*.stop_order.distinct' => 'El orden de parada no puede repetirse.',
            'trains.*.exists' => 'Uno de los trenes seleccionados no es válido.',
        ]);
    }

    private function extractRouteData(array $validated): array
    {
        return [
            'origin_station_id' => $validated['origin_station_id'],
            'destination_station_id' => $validated['destination_station_id'],
            'departure_time' => $validated['departure_time'],
            'arrival_time' => $validated['arrival_time'],
        ];
    }

    private function syncRouteStops(RouteModel $route, array $stops): void
    {
        $normalizedStops = collect($stops)
            ->map(fn ($stop) => [
                'station_id' => (int) $stop['station_id'],
                'stop_order' => (int) $stop['stop_order'],
                'arrival_time' => $stop['arrival_time'],
                'departure_time' => $stop['departure_time'],
            ])
            ->sortBy('stop_order')
            ->values()
            ->all();

        $route->stops()->delete();
        $route->stops()->createMany($normalizedStops);
    }
}

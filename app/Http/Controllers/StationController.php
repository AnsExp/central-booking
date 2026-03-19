<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function store(Request $request): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:station,name'
            ],
            'location' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        Station::create($validated);

        return redirect()
            ->route('stations.view')
            ->with(['station_status' => 'Estacion registrada correctamente.']);
    }

    public function update(Request $request, Station $station): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('station', 'name')->ignore($station->id),
            ],
            'location' => [
                'required',
                'string',
                'max:255'
            ],
        ]);

        $station->update($validated);

        return redirect()
            ->route('stations.view')
            ->with(['station_status' => 'Estacion actualizada correctamente.']);
    }

    public function destroy(Station $station): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $station->delete();

        return redirect()
            ->route('stations.view')
            ->with(['station_status' => 'Estacion eliminada correctamente.']);
    }

    public function view(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $page = (int) $request->query('page', 1);
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'location'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        return view('views.station-view', [
            'stations' => Station::query()
                ->orderBy($sort, $direction)
                ->paginate(10, ['*'], 'page', $page)
                ->appends([
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
        $stationId = $request->query('station', null);
        $station = null;
        if ($stationId) {
            $station = Station::find($stationId);
            if (!$station) {
                return response()->view('404', [], 404);
            }
        }
        return view('forms.station-form', ['station' => $station]);
    }
}

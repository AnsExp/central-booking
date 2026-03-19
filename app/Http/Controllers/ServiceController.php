<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Train;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function store(Request $request): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:service,name'],
            'price' => ['required', 'numeric', 'min:0'],
            'trains' => ['nullable', 'array'],
            'trains.*' => ['integer', 'exists:train,id'],
        ]);

        $service = Service::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
        ]);
        $service->trains()->sync($validated['trains'] ?? []);

        return redirect()
            ->route('services.view')
            ->with('service_status', 'Servicio registrado correctamente.');
    }

    public function update(Request $request, Service $service): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service', 'name')->ignore($service->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
            'trains' => ['nullable', 'array'],
            'trains.*' => ['integer', 'exists:train,id'],
        ]);

        $service->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
        ]);
        $service->trains()->sync($validated['trains'] ?? []);

        return redirect()
            ->route('services.view')
            ->with('service_status', 'Servicio actualizado correctamente.');
    }

    public function destroy(Service $service): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $service->trains()->detach();
        $service->delete();

        return redirect()
            ->route('services.view')
            ->with('service_status', 'Servicio eliminado correctamente.');
    }

    public function view(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $page = (int) $request->query('page', 1);
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'price'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        return view('views.service-view', [
            'services' => Service::query()
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
        $serviceId = $request->query('service', null);
        $service = null;
        if ($serviceId) {
            $service = Service::with('trains:id')->find($serviceId);
            if (!$service) {
                return response()->view('404', [], 404);
            }
        }

        $trains = Train::query()->orderBy('name')->get(['id', 'name', 'code']);

        return view('forms.service-form', [
            'service' => $service,
            'trains' => $trains,
        ]);
    }
}

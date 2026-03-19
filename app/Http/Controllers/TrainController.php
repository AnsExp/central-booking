<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\Route as RouteModel;
use App\Models\TrainStatus;
use App\Models\TrainType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TrainController extends Controller
{
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $this->validateTrain($request);

        $train = Train::create($this->extractTrainData($validated));
        $train->services()->sync($validated['services'] ?? []);
        $train->routes()->sync($validated['routes'] ?? []);
        $this->syncTrainMetadata($train, $validated['notes'] ?? null);

        return redirect()
            ->route('trains.view')
            ->with('train_status', 'Tren registrado correctamente.');
    }

    public function update(Request $request, Train $train): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $validated = $this->validateTrain($request, $train);

        $train->update($this->extractTrainData($validated));
        $train->services()->sync($validated['services'] ?? []);
        $train->routes()->sync($validated['routes'] ?? []);
        $this->syncTrainMetadata($train, $validated['notes'] ?? null);

        return redirect()
            ->route('trains.view')
            ->with('train_status', 'Tren actualizado correctamente.');
    }

    public function destroy(Train $train): RedirectResponse|Response
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $train->delete();

        return redirect()
            ->route('trains.view')
            ->with('train_status', 'Tren eliminado correctamente.');
    }

    private function validateTrain(Request $request, ?Train $train = null): array
    {
        $typeValues = array_map(fn($type) => $type->value, TrainType::cases());
        $statusValues = array_map(fn($status) => $status->value, TrainStatus::cases());

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:60',
                Rule::unique('train', 'code')->ignore($train?->id),
            ],
            'type' => ['required', Rule::in($typeValues)],
            'status' => ['required', Rule::in($statusValues)],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:service,id'],
            'routes' => ['nullable', 'array'],
            'routes.*' => ['integer', 'exists:route,id'],
            'capacity' => ['nullable', 'integer', 'min:0', 'required_if:type,passengers,mixed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'services.*.exists' => 'Uno de los servicios seleccionados no es válido.',
            'routes.*.exists' => 'Una de las rutas seleccionadas no es válida.',
            'capacity.required_if' => 'La capacidad es obligatoria para trenes de pasajeros o mixtos.',
        ]);
    }

    private function extractTrainData(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'capacity' => (int) ($validated['capacity'] ?? 0),
        ];
    }

    private function syncTrainMetadata(Train $train, ?string $notes): void
    {
        $notes = $notes !== null ? trim($notes) : null;

        if ($notes === null || $notes === '') {
            $train->metadataEntries()->where('meta_key', 'notes')->delete();

            return;
        }

        $train->metadataEntries()->updateOrCreate(
            [
                'meta_key' => 'notes'
            ],
            [
                'meta_format' => 'json',
                'meta_value' => ['text' => $notes]
            ]
        );
    }

    public function view(Request $request)
    {
        if (!Auth::check()) {
            return response()->view('403', [], 403);
        }

        $page = (int) $request->query('page', 1);
        $sort = (string) $request->query('sort', 'name');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'code', 'type', 'status'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        return view('views.train-view', [
            'trains' => Train::query()
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
        $trainId = $request->query('train', null);
        $train = null;
        if ($trainId) {
            $train = Train::with(['services:id', 'routes:id', 'metadataEntries'])->find($trainId);
            if (!$train) {
                return response()->view('404', [], 404);
            }
        }
        $routes = RouteModel::query()
            ->with(['originStation:id,name', 'destinationStation:id,name'])
            ->orderBy('id')
            ->get();

        return view('forms.train-form', [
            'train' => $train,
            'routes' => $routes,
        ]);
    }
}

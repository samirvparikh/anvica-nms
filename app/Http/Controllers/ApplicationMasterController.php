<?php

namespace App\Http\Controllers;

use App\Models\ApplicationMaster;
use App\Services\ApplicationMasterService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationMasterController extends Controller
{
    public function __construct(
        protected ApplicationMasterService $masterService,
    ) {}

    public function index(Request $request)
    {
        $types = ApplicationMaster::sortedTypeLabels();
        $type = $request->query('type', array_key_first($types) ?? 'manufacturer');

        if (! array_key_exists($type, $types)) {
            $type = array_key_first($types) ?? 'manufacturer';
        }

        $items = ApplicationMaster::query()
            ->where('type', $type)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('master.application-masters.index', [
            'types' => $types,
            'selectedType' => $type,
            'items' => $items,
            'typeSummaries' => $this->masterService->typeSummaries(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);
        $validated['value'] = $validated['value'] ?: $validated['name'];

        ApplicationMaster::create($validated);

        return redirect()
            ->route('master.application-masters.index', ['type' => $validated['type']])
            ->with('success', ApplicationMaster::typeLabel($validated['type']).' entry created.');
    }

    public function update(Request $request, ApplicationMaster $applicationMaster)
    {
        $validated = $this->validateItem($request, $applicationMaster);
        $validated['value'] = $validated['value'] ?: $validated['name'];

        $applicationMaster->update($validated);

        $this->masterService->forgetCached($applicationMaster->id);

        return redirect()
            ->route('master.application-masters.index', ['type' => $validated['type']])
            ->with('success', ApplicationMaster::typeLabel($validated['type']).' entry updated.');
    }

    public function destroy(ApplicationMaster $applicationMaster)
    {
        $type = $applicationMaster->type;
        $id = $applicationMaster->id;
        $applicationMaster->delete();

        $this->masterService->forgetCached($id);

        return redirect()
            ->route('master.application-masters.index', ['type' => $type])
            ->with('success', ApplicationMaster::typeLabel($type).' entry deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateItem(Request $request, ?ApplicationMaster $existing = null): array
    {
        $type = $request->input('type');

        return $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(ApplicationMaster::typeLabels()))],
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'status' => 'required|in:Active,Inactive',
            'value' => [
                'nullable',
                'string',
                'max:190',
                Rule::unique('application_masters', 'value')
                    ->where(fn ($query) => $query->where('type', $type))
                    ->ignore($existing?->id),
            ],
        ], [], [
            'name' => 'display name',
            'value' => 'stored value',
        ]);
    }
}

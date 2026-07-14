<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertKeteranganRequest;
use App\Http\Requests\UpsertMaterialRequest;
use App\Http\Requests\UpsertPlantRequest;
use App\Http\Requests\UpsertStoCodeRequest;
use App\Http\Requests\UpsertUserRequest;
use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ActiveStoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MasterController extends Controller
{
    public function __construct(
        private ActiveStoService $activeStoService,
        private ActivityLogService $activityLog,
    ) {}

    public function sto(): View
    {
        return view('admin.master.generic', $this->moduleConfig('sto'));
    }

    public function plants(): View
    {
        return view('admin.master.generic', $this->moduleConfig('plant'));
    }

    public function materials(): View
    {
        return view('admin.master.generic', $this->moduleConfig('material'));
    }

    public function keterangan(): View
    {
        return view('admin.master.generic', $this->moduleConfig('keterangan'));
    }

    public function users(): View
    {
        return view('admin.master.generic', $this->moduleConfig('user'));
    }

    public function locations(): View
    {
        return view('admin.master.generic', $this->moduleConfig('location'));
    }

    public function stoData(Request $request): JsonResponse
    {
        return $this->dataTable(
            StoCode::query(),
            $request,
            ['code', 'description'],
            fn (StoCode $stoCode, int $no) => [
                'no' => $no,
                'id' => $stoCode->id,
                'code' => $stoCode->code,
                'description' => $stoCode->description,
                'start_date' => optional($stoCode->start_date)->format('Y-m-d'),
                'end_date' => optional($stoCode->end_date)->format('Y-m-d'),
                'is_active' => $stoCode->is_active,
            ]
        );
    }

    public function storeSto(UpsertStoCodeRequest $request): JsonResponse
    {
        $stoCode = StoCode::create($request->safe()->except('is_active') + [
            'is_active' => false,
        ]);
        $this->activityLog->record($request->user(), 'master.sto.created', $stoCode, newValues: $stoCode->toArray());

        if ($request->boolean('is_active')) {
            $this->activeStoService->activate($stoCode, $request->user());
        }

        return $this->success('STO berhasil ditambahkan.');
    }

    public function updateSto(UpsertStoCodeRequest $request, int $id): JsonResponse
    {
        $stoCode = StoCode::findOrFail($id);
        $oldValues = $stoCode->toArray();
        $stoCode->update($request->safe()->except('is_active'));
        $stoCode->refresh();
        $this->activityLog->record($request->user(), 'master.sto.updated', $stoCode, $oldValues, $stoCode->toArray());

        if ($request->boolean('is_active')) {
            $this->activeStoService->activate($stoCode, $request->user());
        } elseif ($stoCode->is_active && $request->has('is_active')) {
            $oldActiveValues = $stoCode->toArray();
            $stoCode->update(['is_active' => false]);
            $this->activityLog->record($request->user(), 'master.sto.updated', $stoCode, $oldActiveValues, $stoCode->fresh()->toArray());
            $this->activeStoService->forgetCache();
        }

        return $this->success('STO berhasil diperbarui.');
    }

    public function activateSto(int $id): JsonResponse
    {
        $this->activeStoService->activate(StoCode::findOrFail($id), request()->user());

        return $this->success('STO berhasil diaktifkan.');
    }

    public function deactivateSto(int $id): JsonResponse
    {
        $stoCode = StoCode::findOrFail($id);
        
        if ($stoCode->is_active) {
            $oldActiveValues = $stoCode->toArray();
            $stoCode->update(['is_active' => false]);
            $this->activityLog->record(request()->user(), 'master.sto.updated', $stoCode, $oldActiveValues, $stoCode->fresh()->toArray());
            $this->activeStoService->forgetCache();
        }

        return $this->success('STO berhasil dinonaktifkan.');
    }

    public function destroySto(int $id): JsonResponse
    {
        $stoCode = StoCode::findOrFail($id);

        if (ScanResult::where('sto_code_id', $stoCode->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'STO tidak dapat dihapus karena sudah digunakan.',
            ], 422);
        }

        $this->activityLog->record(request()->user(), 'master.sto.deleted', $stoCode, oldValues: $stoCode->toArray());
        $stoCode->delete();

        return $this->success('STO berhasil dihapus.');
    }

    public function plantData(Request $request): JsonResponse
    {
        return $this->dataTable(
            Plant::query(),
            $request,
            ['name'],
            fn (Plant $plant, int $no) => [
                'no' => $no,
                'id' => $plant->id,
                'name' => $plant->name,
                'is_active' => $plant->is_active,
            ]
        );
    }

    public function storePlant(UpsertPlantRequest $request): JsonResponse
    {
        $plant = Plant::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);
        $this->activityLog->record($request->user(), 'master.plant.created', $plant, newValues: $plant->toArray());

        return $this->success('Plant berhasil ditambahkan.');
    }

    public function updatePlant(UpsertPlantRequest $request, int $id): JsonResponse
    {
        $plant = Plant::findOrFail($id);
        $oldValues = $plant->toArray();
        $plant->update($request->validated() + ['is_active' => $request->boolean('is_active')]);
        $this->activityLog->record($request->user(), 'master.plant.updated', $plant, $oldValues, $plant->fresh()->toArray());

        return $this->success('Plant berhasil diperbarui.');
    }

    public function destroyPlant(int $id): JsonResponse
    {
        $plant = Plant::findOrFail($id);

        if ($plant->scanResults()->exists() || $plant->locations()->exists()) {
            return response()->json(['success' => false, 'message' => 'Plant tidak dapat dihapus karena sudah digunakan.'], 422);
        }

        $this->activityLog->record(request()->user(), 'master.plant.deleted', $plant, oldValues: $plant->toArray());
        $plant->delete();

        return $this->success('Plant berhasil dihapus.');
    }

    public function materialData(Request $request): JsonResponse
    {
        return $this->dataTable(
            MasterMaterial::query(),
            $request,
            ['material_code', 'material_name'],
            fn (MasterMaterial $material, int $no) => [
                'no' => $no,
                'id' => $material->id,
                'material_code' => $material->material_code,
                'material_name' => $material->material_name,
                'is_active' => $material->is_active,
            ]
        );
    }

    public function storeMaterial(UpsertMaterialRequest $request): JsonResponse
    {
        $material = MasterMaterial::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);
        $this->activityLog->record($request->user(), 'master.material.created', $material, newValues: $material->toArray());

        return $this->success('Material berhasil ditambahkan.');
    }

    public function updateMaterial(UpsertMaterialRequest $request, int $id): JsonResponse
    {
        $material = MasterMaterial::findOrFail($id);
        $oldValues = $material->toArray();
        $material->update($request->validated() + ['is_active' => $request->boolean('is_active')]);
        $this->activityLog->record($request->user(), 'master.material.updated', $material, $oldValues, $material->fresh()->toArray());

        return $this->success('Material berhasil diperbarui.');
    }

    public function destroyMaterial(int $id): JsonResponse
    {
        $material = MasterMaterial::findOrFail($id);

        if (ScanResult::where('material_code', $material->material_code)->exists()) {
            return response()->json(['success' => false, 'message' => 'Material tidak dapat dihapus karena sudah digunakan.'], 422);
        }

        $this->activityLog->record(request()->user(), 'master.material.deleted', $material, oldValues: $material->toArray());
        $material->delete();

        return $this->success('Material berhasil dihapus.');
    }

    public function keteranganData(Request $request): JsonResponse
    {
        return $this->dataTable(
            MasterKeterangan::query(),
            $request,
            ['name'],
            fn (MasterKeterangan $keterangan, int $no) => [
                'no' => $no,
                'id' => $keterangan->id,
                'name' => $keterangan->name,
                'is_active' => $keterangan->is_active,
            ]
        );
    }

    public function storeKeterangan(UpsertKeteranganRequest $request): JsonResponse
    {
        $keterangan = MasterKeterangan::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);
        $this->activityLog->record($request->user(), 'master.keterangan.created', $keterangan, newValues: $keterangan->toArray());

        return $this->success('Keterangan berhasil ditambahkan.');
    }

    public function updateKeterangan(UpsertKeteranganRequest $request, int $id): JsonResponse
    {
        $keterangan = MasterKeterangan::findOrFail($id);
        $oldValues = $keterangan->toArray();
        $keterangan->update($request->validated() + ['is_active' => $request->boolean('is_active')]);
        $this->activityLog->record($request->user(), 'master.keterangan.updated', $keterangan, $oldValues, $keterangan->fresh()->toArray());

        return $this->success('Keterangan berhasil diperbarui.');
    }

    public function destroyKeterangan(int $id): JsonResponse
    {
        $keterangan = MasterKeterangan::findOrFail($id);

        if ($keterangan->name === 'OK') {
            return response()->json(['success' => false, 'message' => 'Keterangan OK tidak dapat dihapus.'], 422);
        }

        $this->activityLog->record(request()->user(), 'master.keterangan.deleted', $keterangan, oldValues: $keterangan->toArray());
        $keterangan->delete();

        return $this->success('Keterangan berhasil dihapus.');
    }

    public function userData(Request $request): JsonResponse
    {
        return $this->dataTable(
            User::query(),
            $request,
            ['name', 'username', 'role'],
            fn (User $user, int $no) => [
                'no' => $no,
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role,
                'is_validator' => $user->isValidator(),
                'is_active' => $user->is_active,
            ]
        );
    }

    public function storeUser(UpsertUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['pass'] = $data['password'];
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_validator'] = $data['role'] === 'admin' || ($data['role'] === 'scanner' && $request->boolean('is_validator'));

        $user = User::create($data);
        $this->activityLog->record($request->user(), 'master.user.created', $user, newValues: $user->makeHidden(['password', 'pass', 'remember_token'])->toArray());

        return $this->success('User berhasil ditambahkan.');
    }

    public function updateUser(UpsertUserRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['pass'] = $data['password'];
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
            // Do not unset pass, it just stays the same.
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_validator'] = $data['role'] === 'admin' || ($data['role'] === 'scanner' && $request->boolean('is_validator'));

        $user = User::findOrFail($id);
        $oldValues = $user->makeHidden(['password', 'pass', 'remember_token'])->toArray();
        $user->update($data);
        $this->activityLog->record($request->user(), 'master.user.updated', $user, $oldValues, $user->fresh()->makeHidden(['password', 'pass', 'remember_token'])->toArray());

        return $this->success('User berhasil diperbarui.');
    }

    public function destroyUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->scanResults()->exists()) {
            return response()->json(['success' => false, 'message' => 'User tidak dapat dihapus karena sudah memiliki scan.'], 422);
        }

        $this->activityLog->record(request()->user(), 'master.user.deleted', $user, oldValues: $user->makeHidden(['password', 'remember_token'])->toArray());
        $user->delete();

        return $this->success('User berhasil dihapus.');
    }

    public function locationData(Request $request): JsonResponse
    {
        $query = Location::query()->with(['plant', 'createdBy']);

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        return $this->dataTable(
            $query,
            $request,
            ['name'],
            fn (Location $location, int $no) => [
                'no' => $no,
                'id' => $location->id,
                'name' => $location->name,
                'plant' => $location->plant?->name ?? '-',
                'plant_id' => $location->plant_id,
                'pic' => $location->createdBy?->name ?? '-',
                'created_at' => optional($location->created_at)->format('Y-m-d H:i'),
                'is_active' => $location->is_active,
            ]
        );
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Check uniqueness per plant
        if (Location::where('plant_id', $data['plant_id'])->where('name', $data['name'])->exists()) {
            return response()->json(['success' => false, 'message' => 'Lokasi dengan nama tersebut sudah ada untuk plant ini.'], 422);
        }

        $location = Location::create([
            'name'              => $data['name'],
            'plant_id'          => $data['plant_id'],
            'is_active'         => $request->boolean('is_active', true),
            'created_by_user_id' => $request->user()->id,
        ]);

        $this->activityLog->record($request->user(), 'master.location.created', $location, newValues: $location->toArray());

        return $this->success('Lokasi berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Check uniqueness per plant (excluding self)
        if (Location::where('plant_id', $data['plant_id'])
                    ->where('name', $data['name'])
                    ->where('id', '!=', $id)
                    ->exists()) {
            return response()->json(['success' => false, 'message' => 'Lokasi dengan nama tersebut sudah ada untuk plant ini.'], 422);
        }

        $oldValues = $location->toArray();
        $location->update([
            'name'      => $data['name'],
            'plant_id'  => $data['plant_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->activityLog->record($request->user(), 'master.location.updated', $location, $oldValues, $location->fresh()->toArray());

        return $this->success('Lokasi berhasil diperbarui.');
    }

    public function destroyLocation(int $id): JsonResponse
    {
        $location = Location::findOrFail($id);

        if ($location->scanResults()->exists()) {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak dapat dihapus karena sudah digunakan untuk scan.'], 422);
        }

        $this->activityLog->record(request()->user(), 'master.location.deleted', $location, oldValues: $location->toArray());
        $location->delete();

        return $this->success('Lokasi berhasil dihapus.');
    }

    private function dataTable(Builder $query, Request $request, array $searchColumns, callable $map): JsonResponse
    {
        $totalRecords = (clone $query)->count();
        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($innerQuery) use ($searchColumns, $search) {
                foreach ($searchColumns as $column) {
                    $innerQuery->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $filteredRecords = (clone $query)->count();
        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);

        $rows = $query->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get()
            ->map(fn ($item, int $index) => $map($item, $filteredRecords - $start - $index))
            ->values();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows,
        ]);
    }

    private function moduleConfig(string $module): array
    {
        $configs = [
            'sto' => [
                'title' => 'Master STO',
                'apiBase' => route('admin.api.master-sto'),
                'activateBase' => route('admin.api.master-sto'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'code', 'label' => 'STO Code'],
                    ['data' => 'description', 'label' => 'Description'],
                    ['data' => 'start_date', 'label' => 'Start Date'],
                    ['data' => 'end_date', 'label' => 'End Date'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'code', 'label' => 'STO Code', 'type' => 'text', 'required' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'text'],
                    ['name' => 'start_date', 'label' => 'Start Date', 'type' => 'date'],
                    ['name' => 'end_date', 'label' => 'End Date', 'type' => 'date'],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                ],
            ],
            'plant' => [
                'title' => 'Master Plant',
                'apiBase' => route('admin.api.master-plant'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'name', 'label' => 'Plant'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Plant Name', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                ],
            ],
            'material' => [
                'title' => 'Master Material',
                'apiBase' => route('admin.api.master-material'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'material_code', 'label' => 'Material Code'],
                    ['data' => 'material_name', 'label' => 'Material Name'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'material_code', 'label' => 'Material Code', 'type' => 'text', 'required' => true],
                    ['name' => 'material_name', 'label' => 'Material Name', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                ],
            ],
            'keterangan' => [
                'title' => 'Master Keterangan',
                'apiBase' => route('admin.api.master-keterangan'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'name', 'label' => 'Keterangan'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Keterangan', 'type' => 'text', 'required' => true],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                ],
            ],
            'user' => [
                'title' => 'User Management',
                'apiBase' => route('admin.api.users'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'name', 'label' => 'Name'],
                    ['data' => 'username', 'label' => 'Username'],
                    ['data' => 'role', 'label' => 'Role'],
                    ['data' => 'is_validator', 'label' => 'Validator', 'type' => 'boolean'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                    ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password'],
                    ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'options' => [
                        ['value' => 'admin', 'label' => 'Admin'],
                        ['value' => 'scanner', 'label' => 'Scanner'],
                    ]],
                    ['name' => 'is_validator', 'label' => 'Validator', 'type' => 'switch'],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'switch', 'default' => 1],
                ],
            ],
            'location' => [
                'title' => 'Master Location',
                'apiBase' => route('admin.api.master-location'),
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'name', 'label' => 'Location'],
                    ['data' => 'plant', 'label' => 'Plant'],
                    ['data' => 'pic', 'label' => 'PIC'],
                    ['data' => 'created_at', 'label' => 'Created At'],
                    ['data' => 'is_active', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Location Name', 'type' => 'text', 'required' => true],
                    ['name' => 'plant_id', 'label' => 'Plant', 'type' => 'select',
                     'options' => Plant::orderBy('name')->get()->map(fn($p) => ['value' => $p->id, 'label' => $p->name])->toArray(),
                     'required' => true],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'switch', 'default' => 1],
                ],
                'filters' => [
                    [
                        'name' => 'plant_id',
                        'label' => 'Filter Plant',
                        'options' => Plant::orderBy('name')->get()->map(fn($p) => ['value' => $p->id, 'label' => $p->name])->toArray()
                    ]
                ]
            ],
        ];

        return $configs[$module];
    }

    private function success(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}

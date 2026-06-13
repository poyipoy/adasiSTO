<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpsertKeteranganRequest;
use App\Http\Requests\UpsertMaterialRequest;
use App\Http\Requests\UpsertPlantRequest;
use App\Http\Requests\UpsertStoCodeRequest;
use App\Http\Requests\UpsertUserRequest;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\STOService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MasterController extends Controller
{
    public function __construct(private STOService $stoService) {}

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

        if ($request->boolean('is_active')) {
            $this->stoService->activate($stoCode);
        }

        return $this->success('STO berhasil ditambahkan.');
    }

    public function updateSto(UpsertStoCodeRequest $request, int $id): JsonResponse
    {
        $stoCode = StoCode::findOrFail($id);
        $stoCode->update($request->safe()->except('is_active'));

        if ($request->boolean('is_active')) {
            $this->stoService->activate($stoCode);
        } elseif ($stoCode->is_active && $request->has('is_active')) {
            $stoCode->update(['is_active' => false]);
        }

        return $this->success('STO berhasil diperbarui.');
    }

    public function activateSto(int $id): JsonResponse
    {
        $this->stoService->activate(StoCode::findOrFail($id));

        return $this->success('STO berhasil diaktifkan.');
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
        Plant::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success('Plant berhasil ditambahkan.');
    }

    public function updatePlant(UpsertPlantRequest $request, int $id): JsonResponse
    {
        Plant::findOrFail($id)->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return $this->success('Plant berhasil diperbarui.');
    }

    public function destroyPlant(int $id): JsonResponse
    {
        $plant = Plant::findOrFail($id);

        if ($plant->scanResults()->exists() || $plant->locations()->exists()) {
            return response()->json(['success' => false, 'message' => 'Plant tidak dapat dihapus karena sudah digunakan.'], 422);
        }

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
        MasterMaterial::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success('Material berhasil ditambahkan.');
    }

    public function updateMaterial(UpsertMaterialRequest $request, int $id): JsonResponse
    {
        MasterMaterial::findOrFail($id)->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return $this->success('Material berhasil diperbarui.');
    }

    public function destroyMaterial(int $id): JsonResponse
    {
        $material = MasterMaterial::findOrFail($id);

        if (ScanResult::where('material_code', $material->material_code)->exists()) {
            return response()->json(['success' => false, 'message' => 'Material tidak dapat dihapus karena sudah digunakan.'], 422);
        }

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
        MasterKeterangan::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);

        return $this->success('Keterangan berhasil ditambahkan.');
    }

    public function updateKeterangan(UpsertKeteranganRequest $request, int $id): JsonResponse
    {
        MasterKeterangan::findOrFail($id)->update($request->validated() + ['is_active' => $request->boolean('is_active')]);

        return $this->success('Keterangan berhasil diperbarui.');
    }

    public function destroyKeterangan(int $id): JsonResponse
    {
        $keterangan = MasterKeterangan::findOrFail($id);

        if ($keterangan->name === 'OK') {
            return response()->json(['success' => false, 'message' => 'Keterangan OK tidak dapat dihapus.'], 422);
        }

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
                'is_active' => $user->is_active,
            ]
        );
    }

    public function storeUser(UpsertUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        User::create($data);

        return $this->success('User berhasil ditambahkan.');
    }

    public function updateUser(UpsertUserRequest $request, int $id): JsonResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        User::findOrFail($id)->update($data);

        return $this->success('User berhasil diperbarui.');
    }

    public function destroyUser(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->scanResults()->exists()) {
            return response()->json(['success' => false, 'message' => 'User tidak dapat dihapus karena sudah memiliki scan.'], 422);
        }

        $user->delete();

        return $this->success('User berhasil dihapus.');
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
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);

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
                'apiBase' => '/admin/api/master-sto',
                'activateBase' => '/admin/api/master-sto',
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
                'apiBase' => '/admin/api/master-plant',
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
                'apiBase' => '/admin/api/master-material',
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
                'apiBase' => '/admin/api/master-keterangan',
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
                'apiBase' => '/admin/api/users',
                'columns' => [
                    ['data' => 'no', 'label' => 'No'],
                    ['data' => 'name', 'label' => 'Name'],
                    ['data' => 'username', 'label' => 'Username'],
                    ['data' => 'role', 'label' => 'Role'],
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
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                ],
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

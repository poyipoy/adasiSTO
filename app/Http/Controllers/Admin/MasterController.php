<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterController extends Controller
{
    // ═══════════════════════════════════════
    // PLANTS
    // ═══════════════════════════════════════

    public function plants(): View
    {
        $plants = Plant::orderBy('name')->get();
        return view('admin.master.plants', compact('plants'));
    }

    public function storePlant(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:plants,code',
            'name' => 'required|string|max:100',
        ]);

        $plant = Plant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil ditambahkan!',
            'data' => $plant,
        ]);
    }

    public function updatePlant(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:plants,code,' . $id,
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $plant = Plant::findOrFail($id);
        $plant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil diupdate!',
            'data' => $plant,
        ]);
    }

    public function destroyPlant(int $id): JsonResponse
    {
        $plant = Plant::findOrFail($id);
        $plant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Plant berhasil dihapus!',
        ]);
    }

    // ═══════════════════════════════════════
    // MATERIALS
    // ═══════════════════════════════════════

    public function materials(): View
    {
        $materials = MasterMaterial::orderBy('code')->get();
        return view('admin.master.materials', compact('materials'));
    }

    public function storeMaterial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:master_materials,code',
            'name' => 'required|string|max:100',
        ]);

        $material = MasterMaterial::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil ditambahkan!',
            'data' => $material,
        ]);
    }

    public function updateMaterial(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:master_materials,code,' . $id,
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $material = MasterMaterial::findOrFail($id);
        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil diupdate!',
            'data' => $material,
        ]);
    }

    public function destroyMaterial(int $id): JsonResponse
    {
        $material = MasterMaterial::findOrFail($id);
        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material berhasil dihapus!',
        ]);
    }

    // ═══════════════════════════════════════
    // KETERANGAN
    // ═══════════════════════════════════════

    public function keterangan(): View
    {
        $keteranganList = MasterKeterangan::orderBy('name')->get();
        return view('admin.master.keterangan', compact('keteranganList'));
    }

    public function storeKeterangan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $keterangan = MasterKeterangan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil ditambahkan!',
            'data' => $keterangan,
        ]);
    }

    public function updateKeterangan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $keterangan = MasterKeterangan::findOrFail($id);
        $keterangan->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil diupdate!',
            'data' => $keterangan,
        ]);
    }

    public function destroyKeterangan(int $id): JsonResponse
    {
        $keterangan = MasterKeterangan::findOrFail($id);
        $keterangan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil dihapus!',
        ]);
    }
}

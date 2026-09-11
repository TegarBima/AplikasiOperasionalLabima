<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterMitra;
use Illuminate\Http\Request;

class MasterMitraController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterMitra::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_mitra', 'like', '%' . $request->search . '%')
                ->orWhere('alamat_mitra', 'like', '%' . $request->search . '%')
                ->orWhere('telp_mitra', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'mitra_id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $limit = $request->get('limit', 10);
        $data  = $query->paginate($limit);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mitra'   => 'required|string|max:100',
            'alamat_mitra' => 'nullable|string|max:500',
            'telp_mitra'   => 'nullable|string|max:50',
            'fee_mitra'    => 'nullable|integer|min:0',
            'fee_tagih'    => 'nullable|integer|min:0',
            'periode_fee'  => 'nullable|in:bulan_berjalan,bulan_sebelum',
        ]);

        $checkMitra = MasterMitra::query()->whereRaw('LOWER(nama_mitra) = ?', [strtolower($validated['nama_mitra'])])
            ->where('telp_mitra', $validated['telp_mitra'])
            ->first();

        if ($checkMitra) {
            return response()->json([
                'message' => 'Data Mitra sudah terdaftar!',
            ], 409);
        }

        $mitra = MasterMitra::create($validated);

        return response()->json([
            'message' => 'Mitra berhasil ditambahkan',
            'data'    => $mitra,
        ], 201);
    }

    public function show(string $id)
    {
        $mitra = MasterMitra::findOrFail($id);
        if (! $mitra) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }
        return response()->json($mitra, 200);
    }

    public function update(Request $request, string $id)
    {
        $mitra = MasterMitra::find($id);
        if (! $mitra) {
            return response()->json(['message' => 'Mitra tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama_mitra'   => 'required|string|max:100',
            'alamat_mitra' => 'nullable|string|max:500',
            'telp_mitra'   => 'nullable|string|max:50',
            'fee_mitra'    => 'nullable|integer|min:0',
            'fee_tagih'    => 'nullable|integer|min:0',
            'periode_fee'  => 'nullable|in:bulan_berjalan,bulan_sebelum',
        ]);

        if ($mitra->nama_mitra !== $validated['nama_mitra']) {
            $checkMitra = MasterMitra::query()->whereRaw('LOWER(nama_mitra) = ?', [strtolower($validated['nama_mitra'])])
                ->where('telp_mitra', $validated['telp_mitra'])
                ->first();

            if ($checkMitra) {
                return response()->json([
                    'message' => 'Data Mitra sudah terdaftar!',
                ], 409);
            }
        }

        $mitra->update($validated);

    }
}

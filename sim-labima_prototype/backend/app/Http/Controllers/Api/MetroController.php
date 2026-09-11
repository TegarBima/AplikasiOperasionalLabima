<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Metro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Metro::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nama_cp', 'like', '%' . $searchTerm . '%')
                    ->orWhere('telp_cp', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email_cp', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $limit = $request->get('limit', 10);
        $data = $query->paginate($limit);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'nama_cp'    => 'nullable|string|max:255',
            'telp_cp'    => 'nullable|string|max:20',
            'email_cp'   => 'nullable|email|max:255',
            'status'     => 'nullable|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        $metro = Metro::create($validated);

        return response()->json([
            'message' => 'Data E-Metro berhasil disimpan',
            'data'    => $metro
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $metro = Metro::find($id);

        if (!$metro) {
            return response()->json(['message' => 'Data E-Metro tidak ditemukan'], 404);
        }

        return response()->json($metro);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $metro = Metro::findOrFail($id);

        $validated = $request->validate([
            'nama'       => 'required|string|max:255',
            'nama_cp'    => 'nullable|string|max:255',
            'telp_cp'    => 'nullable|string|max:20',
            'email_cp'   => 'nullable|email|max:255',
            'status'     => 'nullable|in:aktif,nonaktif',
            'keterangan' => 'nullable|string',
        ]);

        $metro->update($validated);

        return response()->json([
            'message' => 'Data E-Metro berhasil diupdate',
            'data'    => $metro
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $metro = Metro::findOrFail($id);

        DB::transaction(function () use ($metro) {
            $metro->delete(); // Soft Delete
        });

        return response()->json([
            'message' => 'Data E-Metro berhasil dihapus'
        ]);
    }
}
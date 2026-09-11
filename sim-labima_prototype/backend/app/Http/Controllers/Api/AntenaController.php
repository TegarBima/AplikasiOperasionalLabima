<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Antena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AntenaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Antena::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama', 'like', '%' . $searchTerm . '%')
                    ->orWhere('mac', 'like', '%' . $searchTerm . '%')
                    ->orWhere('ssid', 'like', '%' . $searchTerm . '%')
                    ->orWhere('alamat', 'like', '%' . $searchTerm . '%');
            });
        }

        $limit = $request->get('limit', 10);
        $data  = $query->paginate($limit);

        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'mac'          => 'nullable|string|max:255',
            'ssid'         => 'nullable|string|max:255',
            'password'     => 'nullable|string|max:255',
            'frequency'    => 'nullable|string|max:255',
            'channel'      => 'nullable|string|max:255',
            'alamat'       => 'required|string',
            'titik_lokasi' => 'required|string|max:255',
            'keterangan'   => 'nullable|string',
        ]);

        $antena = Antena::create($validated);

        return response()->json([
            'message' => 'Data antena berhasil disimpan',
            'data'    => $antena,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $antena = Antena::with([
            'master_registrasis' => function ($query) {
                $query
                    ->whereNull('deleted_at')
                    ->where('status_survey', 'accept')
                    ->with('mitra');
            },
        ])->find($id);

        if (! $antena) {
            return response()->json([
                'message' => 'Data antena tidak ditemukan',
            ], 404);
        }

        return response()->json($antena);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $antena = Antena::findOrFail($id);

        $validated = $request->validate([
            'nama'         => 'required|string|max:255',
            'mac'          => 'nullable|string|max:255',
            'ssid'         => 'nullable|string|max:255',
            'password'     => 'nullable|string|max:255',
            'frequency'    => 'nullable|string|max:255',
            'channel'      => 'nullable|string|max:255',
            'alamat'       => 'required|string',
            'titik_lokasi' => 'required|string|max:255',
            'keterangan'   => 'nullable|string',
        ]);

        $antena->update($validated);

        return response()->json([
            'message' => 'Data antena berhasil diupdate',
            'data'    => $antena
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $antena = Antena::findOrFail($id);

        DB::transaction(function () use ($antena) {
            $antena->delete();
        });

        return response()->json([
            'message' => 'Data antena berhasil dihapus'
        ]);
    }
}
<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Odc;
use Illuminate\Http\Request;

class OdcController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query  = Odc::with(['wilayah', 'odps.wilayah']);
        $search = $request->search;

        if ($search) {
            $query->where(function ($q) use ($search) {
                if (strlen($search) >= 5) {
                    $q->orWhereHas('wilayah', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(wilayah) LIKE ?', ['%' . strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(lingkup) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
                } else {
                    $q->whereRaw('LOWER(odc_id) LIKE ?', ['%' . strtolower($search) . '%']);
                }

            });
        }

        if ($request->has('kode_wilayah') && $request->search != 'kode_wilayah') {
            $query->where('kode_wilayah', $request->kode_wilayah);
        }

        if ($request->has('available_only')) {
            $query->where('sisa_port', '>', 0);
        }

        // Sorting
        $sortBy    = $request->get('sort_by', 'odc_id');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
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
            'odc_id'       => 'nullable|string|max:255',
            'lokasi_odc'   => 'required|string|max:255',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'jumlah_port'  => 'required|integer|min:0',
            'sisa_port'    => 'required|integer|min:0',
        ]);

        $validated['sisa_port'] = $validated['jumlah_port'];

        if ($validated['sisa_port'] > $validated['jumlah_port']) {
            return response()->json([
                'message' => 'Sisa port tidak boleh lebih dari jumlah port',
            ], 409);
        }

        try {
            $checkOdcTerhapus = Odc::query()->onlyTrashed()
                ->whereRaw('LOWER(odc_id) = ?', [strtolower($validated['odc_id'])])
                ->first();

            if ($checkOdcTerhapus) {
                $checkOdcTerhapus->restore();
                $odc = $checkOdcTerhapus->update([
                    'lokasi_odc'   => $validated['lokasi_odc'],
                    'kode_wilayah' => $validated['kode_wilayah'],
                    'jumlah_port'  => $validated['jumlah_port'],
                    'sisa_port'    => $validated['sisa_port'],
                ]);
                $odc = $checkOdcTerhapus;
            } else {
                $checkOdcAktif = Odc::query()->whereRaw('LOWER(odc_id) = ?', [strtolower($validated['odc_id'])])
                    ->first();

                if ($checkOdcAktif) {
                    return response()->json([
                        'message' => 'ODC ID sudah digunakan',
                        'data'    => $checkOdcAktif,
                    ], 422);
                }
                $odc = Odc::create($validated);
            }

            return response()->json([
                'message' => 'Odc berhasil ditambahkan',
                'data'    => $odc,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Gagal menambahkan Odc: duplikasi odc_id.',
                    'error'   => $e->getMessage(),
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $odc = Odc::with(['wilayah', 'odps.wilayah'])->find($id);

        if (! $odc) {
            return response()->json(['message' => 'Odc tidak ditemukan'], 404);
        }

        return response()->json($odc);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $odc = Odc::find($id);

        if (! $odc) {
            return response()->json(['message' => 'Odc tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'odc_id'       => 'nullable|string|max:255',
            'lokasi_odc'   => 'required|string|max:255',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'jumlah_port'  => 'required|integer|min:0',
            'sisa_port'    => 'required|integer|min:0',
        ]);

        $real_sisa_port = $odc->odps()->count();

        if ($real_sisa_port > $validated['jumlah_port']) {
            return response()->json([
                'message' => "Jumlah port tidak boleh kurang dari penggunaan port ($real_sisa_port port)",
            ], 409);
        }

        $validated['sisa_port'] = $validated['jumlah_port'] - $real_sisa_port;

        $normalizedNewOdcId     = strtolower($validated['odc_id']);
        $normalizedCurrentOdcId = strtolower($odc->odc_id);

        if ($normalizedNewOdcId !== $normalizedCurrentOdcId) {
            $validateNewOdcId = Odc::query()->whereRaw('LOWER(odc_id) = ?', [$normalizedNewOdcId])
                ->where('id', '!=', $odc->id)
                ->first();

            if ($validateNewOdcId) {
                return response()->json(['message' => 'ODC ID: ' . $validated['odc_id'] . ' Sudah digunakan oleh ODC lain!'], 409);
            }

        }

        $odc->update($validated);

        return response()->json([
            'message' => 'Odc berhasil diupdate',
            'data'    => $odc,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $odc = Odc::find($id);

        if (! $odc) {
            return response()->json(['message' => 'Odc tidak ditemukan'], 404);
        }

        if ($odc->odps()->exists() && ! $request->has('confirm')) {
            return response()->json(['message' => 'ODC memiliki ODP aktif'], 409);
        } else if ($odc->odps()->exists() && $request->has('confirm')) {
            $confirm = $request->input('confirm');
            if ($confirm) {
                $odc->delete();
                return response()->json(['message' => 'Odc berhasil dihapus']);
            }
        } else {
            $odc->delete();
        }

        return response()->json(['message' => 'Odc berhasil dihapus']);

    }
}
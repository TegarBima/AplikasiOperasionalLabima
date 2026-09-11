<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterRegistrasi;
use App\Models\Odc;
use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OdpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query  = Odp::with(['odc.wilayah', 'wilayah']);
        $search = $request->search;

        if ($search) {
            // Gunakan klausa WHERE bertingkat untuk mengelompokkan kondisi OR dengan benar
            $query->where(function ($q) use ($search) {
                if (strlen($search) >= 5) {
                    $q->orWhereHas('wilayah', function ($q2) use ($search) {
                        $q2->whereRaw('LOWER(wilayah) LIKE ?', ['%' . strtolower($search) . '%'])
                            ->orWhereRaw('LOWER(lingkup) LIKE ?', ['%' . strtolower($search) . '%']);
                    });
                } else {
                    $q->whereRaw('LOWER(odp_id) LIKE ?', ['%' . strtolower($search) . '%']);
                }

            });
        }

        if ($request->has('available_only')) {
            $query->where('sisa_port', '>', 0);
        }

        // Sorting
        $sortBy    = $request->get('sort_by', 'odp_id');
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
            'odp_id'       => 'nullable|string|max:255',
            'odc_id'       => 'required|exists:odcs,odc_id',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'lokasi_odp'   => 'required|string|max:255',
            'jumlah_port'  => 'required|integer|min:0',
            'sisa_port'    => 'required|integer|min:0',
        ]);

        if ($validated['sisa_port'] > $validated['jumlah_port']) {
            return response()->json([
                'message' => 'Sisa port tidak boleh lebih dari jumlah port',
            ], 409);
        }

        try {
            $odp = DB::transaction(function () use ($validated) {
                $odpID            = $validated['odp_id'];
                $checkOdpTerhapus = Odp::onlyTrashed()
                    ->whereRaw('LOWER(odp_id) = ?', [strtolower($odpID)])
                    ->first();

                $checkOdpID = Odp::query()->whereRaw('LOWER(odp_id) = ?', [strtolower($odpID)])
                    ->first();

                if ($checkOdpID) {
                    throw new \Exception("ODP ID sudah digunakan", 422);
                }

                if ($checkOdpTerhapus) {
                    $checkOdpTerhapus->restore();
                    $odp = $checkOdpTerhapus->update([
                        'odc_id'       => $validated['odc_id'],
                        'lokasi_odp'   => $validated['lokasi_odp'],
                        'kode_wilayah' => $validated['kode_wilayah'],
                        'jumlah_port'  => $validated['jumlah_port'],
                        'sisa_port'    => $validated['sisa_port'],
                    ]);
                    $odp = $checkOdpTerhapus;
                } else {
                    $odp = Odp::create($validated);
                }

                $odcUpdated = Odc::where('odc_id', $validated['odc_id'])
                    ->where('sisa_port', '>', 0)
                    ->decrement('sisa_port');

                if ($odcUpdated === 0) {
                    throw new \Exception("Port ODC Penuh!", 409);
                }

                return $odp;
            });

            return response()->json([
                'message' => 'Odp berhasil ditambahkan dan port ODC diperbarui.',
                'data'    => $odp,
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Gagal menambahkan Odp: duplikasi odp_id.',
                    'error'   => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'message' => 'Terjadi kesalahan saat menambahkan data.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $odp = Odp::with([
            'odc.wilayah',
            'wilayah',
            'psb' => function ($query) {
                $query
                    ->whereNull('deleted_at')
                    ->where('status_survey', 'accept')
                    ->with('mitra');
            },
        ])->find($id);

        if (! $odp) {
            return response()->json(['message' => 'Odp tidak ditemukan'], 404);
        }

        return response()->json($odp);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $odp = Odp::find($id);

        if (! $odp) {
            return response()->json(['message' => 'Odp tidak ditemukan'], 404);
        }
        $old_odc_id = $odp->odc_id;
        $old_odp_id = $odp->odp_id;

        $validated = $request->validate([
            'odp_id'       => 'nullable|string|max:255',
            'odc_id'       => 'required|exists:odcs,odc_id',
            'lokasi_odp'   => 'required|string|max:255',
            'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
            'jumlah_port'  => 'required|integer|min:0',
            'sisa_port'    => 'required|integer|min:0',
        ]);

        if (strtolower($odp->odp_id) !== strtolower($validated['odp_id'])) {
            $validated = $request->validate([
                'odp_id'       => 'nullable|string|max:255|unique:odps,odp_id',
                'odc_id'       => 'required|exists:odcs,odc_id',
                'lokasi_odp'   => 'required|string|max:255',
                'kode_wilayah' => 'required|exists:wilayah,kode_wilayah',
                'jumlah_port'  => 'required|integer|min:0',
                'sisa_port'    => 'required|integer|min:0',
            ]);
            $checkOdpID = Odp::query()->whereRaw('LOWER(odp_id) = ?', [strtolower($validated['odp_id'])])
                ->first();

            if ($checkOdpID) {
                throw new \Exception("ODP ID sudah digunakan", 422);
            }
        }

        $real_sisa_port = $odp->psb()->count();

        if ($real_sisa_port > $validated['jumlah_port']) {
            return response()->json([
                "Jumlah port tidak boleh kurang dari penggunaan port ($real_sisa_port port)",
            ], 409);
        }

        $new_odc_id = $validated['odc_id'];

        if ($old_odc_id !== $new_odc_id) {
            $new_odc = Odc::where('odc_id', $new_odc_id)->first();
            if (! $new_odc || $new_odc->sisa_port <= 0) {
                return response()->json([
                    'message' => 'Gagal memperbarui ODP. ODC baru (' . $new_odc_id . ') tidak memiliki sisa port yang tersedia.',
                ], 409);
            }
        }

        try {
            DB::transaction(function () use ($odp, $validated, $old_odc_id, $new_odc_id) {

                $odp->update($validated);

                if ($old_odc_id !== $new_odc_id) {
                    $old_odc = Odc::where('odc_id', $old_odc_id)->first();

                    if ($old_odc && $old_odc->sisa_port < $old_odc->jumlah_port) {
                        Odc::where('odc_id', $old_odc_id)->increment('sisa_port');
                    }

                    $odcUpdated = Odc::where('odc_id', $new_odc_id)
                        ->where('sisa_port', '>', 0)
                        ->decrement('sisa_port');

                    if ($odcUpdated === 0) {
                        throw new \Exception("Port ODC Penuh!", 409);
                    }
                }
            });

            return response()->json([
                'message' => 'Odp berhasil diupdate dan port ODC terkait telah disesuaikan.',
                'data'    => $odp->refresh(),
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => $e->getCode() === '23000'
                    ? 'Kode lama: ' . $old_odp_id . ' masih digunakan oleh data pelanggan'
                    : 'Terjadi kesalahan saat memperbarui data.',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $odp = Odp::find($id);

        if (! $odp) {
            return response()->json(['message' => 'Odp tidak ditemukan'], 404);
        }

        $odc_id_to_increment = $odp->odc_id;
        $checkPsb            = MasterRegistrasi::query()->where('odp_id', $odp->odp_id)->exists();
        $delete              = false;

        if ($checkPsb && ! $request->has('confirm')) {
            return response()->json(['message' => 'ODP memiliki Data Pelanggan'], 409);
        } else if ($checkPsb && $request->has('confirm')) {
            $confirm = $request->input('confirm');
            if ($confirm) {
                $delete = true;
            }
        } else {
            $delete = true;
        }

        if ($delete) {
            try {
                DB::transaction(function () use ($odp, $odc_id_to_increment) {
                    $odp->psb()->update(['odp_id' => null]);
                    // 1. Hapus ODP
                    $odp->delete();

                    $old_odc = Odc::where('odc_id', $odc_id_to_increment)->first();

                    if ($old_odc && $old_odc->sisa_port < $old_odc->jumlah_port) {
                        Odc::where('odc_id', $odc_id_to_increment)->increment('sisa_port');
                    }
                });

                return response()->json(['message' => 'Odp berhasil dihapus dan port ODC dikembalikan.']);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Gagal menghapus ODP dan mengembalikan port ODC.',
                    'error'   => $e->getMessage(),
                ], 500);
            }
        }
    }

    public function addPsb(Request $request, string $id)
    {
        $odp = Odp::findOrFail($id);

        $validated = $request->validate([
            'psb_id' => 'required|string',
        ]);

        $psbIds = array_filter(explode(',', $request->psb_id));

        return DB::transaction(function () use ($odp, $psbIds) {
            // Update PSB records: set odp_id and nullify antena_id
            MasterRegistrasi::whereIn('psb_id', $psbIds)->update([
                'odp_id'    => $odp->odp_id,
                'antena_id' => null,
            ]);

            return response()->json([
                'message' => 'Berhasil menambahkan ' . count($psbIds) . ' pelanggan ke ODP ' . $odp->odp_id,
                'data'    => $odp->fresh(),
            ]);
        });
    }

    public function deletePsb(Request $request, string $id)
    {
        $odp = Odp::findOrFail($id);

        $validated = $request->validate([
            'psb_id' => 'required|string',
        ]);

        $psbIds = array_filter(explode(',', $request->psb_id));

        return DB::transaction(function () use ($odp, $psbIds) {
            MasterRegistrasi::whereIn('psb_id', $psbIds)
                ->where('odp_id', $odp->odp_id)
                ->update(['odp_id' => null]);

            return response()->json([
                'message' => 'Berhasil menghapus ' . count($psbIds) . ' pelanggan dari ODP ' . $odp->odp_id,
                'data'    => $odp->fresh(),
            ]);
        });
    }
}
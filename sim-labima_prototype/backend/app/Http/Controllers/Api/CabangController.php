<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CabangController extends Controller
{
    public function index(Request $request)
    {
        $query = Cabang::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_cabang', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'cabang_id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $limit = $request->get('limit', 10);
        $data  = $query->paginate($limit);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_cabang'   => 'required|string|max:100',
            'alamat_cabang' => 'required|string|max:50',
            'lokasi_cabang' => 'required|string|max:50',
            'nama_PIC'      => 'required|string|max:100',
            'email_PIC'     => 'required|string|max:50',
            'telp_PIC'      => 'required|string|max:50',
        ]);

        $inputNamaCabang = strtolower($validated['nama_cabang']);
        $checkCabang     = Cabang::query()->whereRaw('LOWER(nama_cabang) = ?', [$inputNamaCabang])->first();

        if ($checkCabang) {
            return response()->json([
                'message' => 'Cabang ' . $validated['nama_cabang'] . ' sudah terdaftar!',
            ], 409);
        }

        $cabang = Cabang::create($validated);

        return response()->json([
            'message' => 'Cabang berhasil ditambahkan',
            'data'    => $cabang,
        ], 201);
    }

    public function show($id)
    {
        $cabang = Cabang::find($id);

        if (! $cabang) {
            return response()->json(['message' => 'Cabang tidak ditemukan'], 404);
        }

        return response()->json($cabang);
    }

    public function update(Request $request, $id)
    {
        $cabang = Cabang::find($id);

        if (! $cabang) {
            return response()->json(['message' => 'Cabang tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama_cabang'   => 'required|string|max:100',
            'alamat_cabang' => 'required|string|max:50',
            'lokasi_cabang' => 'required|string|max:100',
            'nama_PIC'      => 'required|string|max:50',
            'email_PIC'     => 'required|string|max:100',
            'telp_PIC'      => 'required|string|max:50',
        ]);

        if ($cabang->nama_cabang !== $validated['nama_cabang']) {
            $inputNamaCabang = strtolower($validated['nama_cabang']);
            $checkCabang     = Cabang::query()->whereRaw('LOWER(nama_cabang) = ?', [$inputNamaCabang])->first();

            if ($checkCabang) {
                return response()->json([
                    'message' => 'Cabang ' . $validated['nama_cabang'] . ' sudah terdaftar!',
                ], 409);
            }
        }

        $cabang->update($validated);

        return response()->json([
            'message' => 'Cabang berhasil diupdate',
            'data'    => $cabang,
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $cabang = Cabang::find($id);

            if (! $cabang) {
                DB::rollBack();
                return response()->json(['message' => 'Cabang tidak ditemukan'], 404);
            }

            $checkUses = $cabang->itemPsb()->exists();

            if ($checkUses) {
                DB::rollBack();
                return response()->json(['message' => 'Cabang masih digunakan oleh data pelanggan.'], 409);
            }

            if ($cabang->itemPsb()->exists()) {
                $cabang->itemPsb()->update(['cabang_id' => null]);
            }

            $cabang->delete();

            DB::commit();

            return response()->json(['message' => 'Cabang berhasil dihapus']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Terjadi kesalahan saat menghapus Cabang.', 'data' => $e], 500);
        }
    }

}
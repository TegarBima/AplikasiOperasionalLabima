<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdukController extends Controller
{
    /**
     * Mendapatkan semua data dan fitur pencarian, sorting.
     */
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'like', '%' . $request->search . '%')
                ->orWhere('kecepatan', 'like', '%' . $request->search . '%')
                ->orWhere('harga', 'like', '%' . $request->search . '%');
        }

        $sortBy    = $request->get('sort_by', 'produk_id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $limit = $request->get('limit', 10);
        $data  = $query->paginate($limit);

        return response()->json($data);
    }

    /**
     * Membuat data atau record baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk'   => 'required|string|max:255',
            'kecepatan'     => 'required|numeric|min:1',
            'harga'         => 'required|numeric|min:0',
            'local_address' => 'required|string|max:100',
            'pool'          => 'required|string|max:100',
        ]);

        $inputNamaProduk = strtolower($validated['nama_produk']);
        $checkProduk     = Produk::query()->whereRaw('LOWER(nama_produk) = ?', [$inputNamaProduk])->first();
        if ($checkProduk) {
            return response()->json([
                'message' => 'Produk ' . $validated['nama_produk'] . ' sudah terdaftar!',
            ], 409);
        }

        $formatted_nama_produk    = str_replace(' ', '-', strtolower($validated['nama_produk']));
        $validated['nama_produk'] = $formatted_nama_produk;

        $produk = Produk::create($validated);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $produk,
        ], 201);

    }

    /**
     * Tampilkan single data
     */
    public function show(string $id)
    {
        $produk = Produk::find($id);

        if (! $produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json($produk);
    }

    /**
     * Memperbarui data.
     */
    public function update(Request $request, string $id)
    {
        $produk = Produk::find($id);
        if (! $produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }
        $validated = $request->validate([
            'nama_produk'   => 'required|string|max:100',
            'kecepatan'     => 'required|numeric|min:1',
            'harga'         => 'required|numeric|min:0',
            'local_address' => 'required|string|max:100',
            'pool'          => 'required|string|max:100',
        ]);

        if ($validated['nama_produk'] != $produk->nama_produk) {
            $inputNamaProduk = strtolower($validated['nama_produk']);
            $checkProduk     = Produk::query()->whereRaw('LOWER(nama_produk) = ?', [$inputNamaProduk])->first();
            if ($checkProduk) {
                return response()->json([
                    'message' => 'Produk ' . $validated['nama_produk'] . ' sudah terdaftar!',
                ], 409);
            }
        }

        $formatted_nama_produk    = str_replace(' ', '-', strtolower($validated['nama_produk']));
        $validated['nama_produk'] = $formatted_nama_produk;

        $produk->update($validated);

        return response()->json([
            'message' => 'Produk berhasil diupdate',
            'data'    => $produk,
        ]);
    }

    /**
     * Hapus data secara spesifik.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $produk = Produk::find($id);

            if (!$produk) {
                DB::rollBack();
                return response()->json(['message' => 'Produk tidak ditemukan'], 404);
            }

            // Cek penggunaan produk yang statusnya BUKAN 'decline'
            $checkUses = $produk->registrasi()
                ->where('status', '!=', 'decline')
                ->exists();

            if ($checkUses) {
                DB::rollBack(); // Rollback jika produk masih digunakan
                return response()->json(['message' => 'Produk masih digunakan oleh data pelanggan.'], 409);
            }

            // 1. Update 'produk_id' di relasi (MPsb) menjadi NULL
            $produk->registrasi()->update(['produk_id' => null]);

            // 2. Hapus produk
            $produk->delete();

            // Commit transaksi jika semua langkah berhasil
            DB::commit();

            return response()->json(['message' => 'Produk berhasil dihapus']);

        } catch (\Exception $e) {
            // Rollback jika terjadi Exception/Error pada salah satu langkah di atas
            DB::rollBack();

            // Opsional: Log error untuk debugging
            // \Log::error('Gagal menghapus produk ID ' . $id . ': ' . $e->getMessage());

            return response()->json(['message' => 'Terjadi kesalahan saat menghapus produk.'], 500);
        }
    }
}
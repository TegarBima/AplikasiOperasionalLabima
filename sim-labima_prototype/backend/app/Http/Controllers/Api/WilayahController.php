<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class WilayahController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Wilayah::query();
        $data  = $query->paginate(10);

        if ($request->has('search') && $request->search != '') {
            $query->where('wilayah', 'like', '%' . $request->search . '%')
                ->orWhere('kode_wilayah', 'like', '%' . $request->search . '%')
                ->orWhere('lingkup', 'like', '%' . $request->search . '%');
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
            'kode_wilayah' => 'required|string|unique:wilayah,kode_wilayah',
            'wilayah'      => 'required|string',
            'type_wilayah' => [
                'required',
                Rule::in(['provinsi', 'kabupaten/kota', 'kecamatan', 'desa/kampung/kelurahan']),
            ],
            'lingkup'      => 'nullable|string',
        ]);

        $wilayah = Wilayah::create($validated);

        return response()->json([
            'message' => 'Wilayah berhasil ditambahkan',
            'data'    => $wilayah,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Wilayah $wilayah)
    {
        return response()->json([
            'message' => 'Sukses',
            'data'    => $wilayah,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Wilayah $wilayah)
    {

        $validated = $request->validate([
            'kode_wilayah' => "sometimes|string|unique:wilayah,kode_wilayah,{$wilayah->id}",
            'wilayah'      => 'sometimes|string',
            'type_wilayah' => [
                'sometimes',
                Rule::in(['provinsi', 'kabupaten/kota', 'kecamatan', 'desa/kampung/kelurahan']),
            ],
            'lingkup'      => "sometimes|string",
        ]);

        if ($wilayah->kode_wilayah !== $validated['kode_wilayah']) {
            $odcExist = $wilayah->odc()->exists();
            $odpExist = $wilayah->odp()->exists();
            if ($odcExist || $odpExist) {
                return response()->json([
                    'message' => 'Kode Wilayah: ' . $wilayah->kode_wilayah . ' masih digunakan',
                ], 409);
            }
        }

        $wilayah->update($validated);

        return response()->json([
            'message' => 'Sukses Update',
            'data'    => $wilayah,
        ], 201);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Wilayah $wilayah)
    {
        $odcExist = $wilayah->odc()->exists();
        $odpExist = $wilayah->odp()->exists();
        if ($odcExist || $odpExist) {
            return response()->json([
                'message' => 'Kode Wilayah: ' . $wilayah->kode_wilayah . ' masih digunakan',
            ], 409);
        }
        $wilayah->delete();
        return response()->json([
            'message' => 'Sukses Delete',
        ], 200);

    }

    public function byType(Request $request, string $type)
    {
        $allowed = ['provinsi', 'kabupaten', 'kecamatan', 'desa'];
        if (!in_array($type, $allowed)) {
            return response()->json(['message' => 'Invalid type_wilayah'], 422);
        }

        if ($type === 'kabupaten') {
            $type = 'kabupaten/kota';
        }

        if ($type === 'desa') {
            $type = 'desa/kampung/kelurahan';
        }

        $query = Wilayah::where('type_wilayah', $type);

        $allowed = ['provinsi', 'kabupaten/kota', 'kecamatan', 'desa/kampung/kelurahan'];
        if (!in_array($type, $allowed)) {
            return response()->json(['message' => 'Invalid type'], 422);
        }

        $query = Wilayah::where('type_wilayah', $type);

        // filter anak berdasarkan prefix kode
        if ($request->filled('prov')) {
            $query->where('kode_wilayah', 'like', $request->prov . '%');
        }
        if ($request->filled('kab')) {
            $query->where('kode_wilayah', 'like', $request->kab . '%');
        }
        if ($request->filled('kec')) {
            $query->where('kode_wilayah', 'like', $request->kec . '%');
        }

        $limit = $request->get('limit', 10);
        $data = $query->paginate($limit);

        return response()->json($data);

    }

    public function search(Request $request)
    {
        if (!$request->filled('q')) {
            return response()->json(['message' => 'Parameter q wajib diisi'], 422);
        }

        $parts = array_map('trim', explode(',', $request->q));

        $mapType = [
            0 => 'Desa/Kampung/Kelurahan',
            1 => 'Kecamatan',
            2 => 'Kabupaten/Kota',
            3 => 'Provinsi',
        ];

        $desa = $parts[0] ?? null;
        $kec = $parts[1] ?? null;
        $kab = $parts[2] ?? null;
        $prov = $parts[3] ?? null;

        $query = Wilayah::query()->where('type_wilayah', $mapType[0]);

        // filter desa
        if ($desa) {
            $query->where('wilayah', 'like', '%' . $desa . '%');
        }

        // filter kecamatan lewat lingkup
        if ($kec) {
            $query->where('lingkup', 'like', '%' . $kec . '%');
        }

        // filter kabupaten
        if ($kab) {
            $query->where('lingkup', 'like', '%' . $kab . '%');
        }

        // filter provinsi
        if ($prov) {
            $query->where('lingkup', 'like', '%' . $prov . '%');
        }

        $limit = $request->get('limit', 10);
        $data = $query->paginate($limit);

        return response()->json($data);
    }

     public function bulkStore(Request $request)
    {
        // $items = $request->all();

        // if (!is_array($items) || empty($items)) {
        //     return response()->json(['message' => 'Data harus berupa array'], 422);
        // }

        $request->validate([
            'file' => 'required|file|mimes:json,txt|max:5124',
        ]);

        // Baca isi file & decode
        $content = file_get_contents($request->file('file')->getRealPath());
        $items = json_decode($content, true);

        if (!is_array($items)) {
            return response()->json(['message' => 'Format file tidak valid (harus array JSON)'], 422);
        }

        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($items as $index => $item) {
            DB::beginTransaction();

            try {
                // --- format kode: hapus koma & spasi ---
                $kode = isset($item['kode_wilayah'])
                    ? str_replace(',', '', $item['kode_wilayah'])
                    : '';

                $kode = trim($kode);

                // Validasi dengan Validator
                $validator = Validator::make(
                    [
                        'kode_wilayah' => $kode,
                        'wilayah' => $item['wilayah'] ?? '',
                        'type_wilayah' => $item['type_wilayah'] ?? '',
                        'lingkup' => $item['lingkup'] ?? null,
                    ],
                    [
                        'kode_wilayah' => 'required|string|unique:wilayah,kode_wilayah',
                        'wilayah' => 'required|string',
                        'type_wilayah' => 'required|in:Provinsi,Kabupaten/Kota,Kecamatan,Desa/Kampung/Kelurahan',
                        'lingkup' => 'nullable|string',
                    ]
                );

                if ($validator->fails()) {
                    throw new ValidationException($validator);
                }

                Wilayah::create([
                    'kode_wilayah' => $kode,
                    'wilayah' => $item['wilayah'],
                    'type_wilayah' => $item['type_wilayah'],
                    'lingkup' => $item['lingkup'] ?? null,
                ]);

                DB::commit();
                $success++;
            } catch (ValidationException $e) {
                DB::rollBack();
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'message' => $e->validator->errors()->all(),
                    'data' => $item,
                ];
            } catch (Throwable $e) {
                DB::rollBack();
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'message' => $e->getMessage(),
                    'data' => $item,
                ];
            }
        }

        return response()->json([
            'total_success' => $success,
            'total_failed' => $failed,
            'errors' => $errors,
        ]);
    }
}
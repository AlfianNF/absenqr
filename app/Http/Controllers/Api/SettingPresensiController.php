<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Presensi;
use Illuminate\Http\Request;
use App\Models\SettingPresensi;
use Illuminate\Routing\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingPresensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin')->only(['store', 'update', 'destroy']);    
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {    
        $modelClass = SettingPresensi::class;
    
        $query = $modelClass::query();
    
        $filters = $request->only($modelClass::getAllowedFields('filter'));
        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                $query->where($field, 'LIKE', "%{$value}%");
            }
        }
    
        $search = $request->input('search');
        if ($search) {
            $allowedSearch = $modelClass::getAllowedFields('search');
            $query->where(function ($q) use ($search, $allowedSearch) {
                foreach ($allowedSearch as $field) {
                    if ($field === 'id_user') {
                        $q->orWhereHas('user', function ($uq) use ($search) {
                            $uq->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%']);
                        });
                    } else {
                        $q->orWhereRaw("LOWER(CAST($field AS TEXT)) LIKE ?", ["%" . strtolower($search) . "%"]);
                    }
                }
            });
        }

    
        $query->with((new $modelClass)->getRelations());
    
        $data = $query->orderBy('created_at', 'DESC')->get();
    
        
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $rules = SettingPresensi::getValidationRules('add');
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $request->all();
            $data['id_user'] = auth()->id();

            $setting = SettingPresensi::create($data);

            $users = User::where('role', 'user')->get();

            foreach ($users as $user) {
                Presensi::create([
                    'id_user' => $user->id,
                    'id_setting' => $setting->id,
                    'jam_masuk' => $data['jam_absen'],
                    'jam_keluar' => null,
                    'latitude' => '0',
                    'longitude' => '0',
                    'status' => 'alfa',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Setting Presensi berhasil dibuat dan presensi default alfa telah diinput untuk user.',
                'data' => $setting,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $modelClass = SettingPresensi::class;

        $data = $modelClass::with((new $modelClass)->getRelations())->find($id);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $modelClass = SettingPresensi::class;

        try {
            $rules = $modelClass::getValidationRules('edit');
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $setting = $modelClass::find($id);

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            $setting->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diperbarui.',
                'data' => $setting,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $modelClass = SettingPresensi::class;

        try {
            $setting = $modelClass::find($id);

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            $setting->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan pada database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

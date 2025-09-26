<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\Presensi;
use Illuminate\Http\Request;
use App\Models\SettingPresensi;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PresensiController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_admin')->only(['store', 'destroy']);    
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {    
        $modelClass = Presensi::class;
    
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

    public function store(Request $request,$id)
    {
        try {
            $rules = Presensi::getValidationRules('add');
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $request->only(Presensi::getAllowedFields('add'));

            $timezone = Config::get('app.timezone', 'Asia/Jakarta');
            $now = Carbon::now($timezone);

            if (!isset($data['id_user'])) {
                $data['id_user'] = auth()->id();
            }

            $setting = SettingPresensi::find($data['id_setting']);
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting Presensi tidak ditemukan.',
                ], 404);
            }

            $jamAbsenSetting = Carbon::parse($setting->jam_absen, $timezone);
            $selisihMenit = $jamAbsenSetting->diffInMinutes($now);
            $isTelat = $now->greaterThan($jamAbsenSetting);

            if ($isTelat && $selisihMenit > 120) {
                $status = '-';
            } elseif ($isTelat && $selisihMenit > 60) {
                $status = 'terlambat';
            } else {
                $status = 'tepat waktu';
            }

            $data['jam_masuk'] = $now->format('H:i:s');
            $data['status'] = $status;

            // Cek apakah user sudah presensi hari ini
            $existingPresensi = Presensi::where('id_user', $data['id_user'])
                ->where('id_setting', $data['id_setting'])
                ->whereDate('created_at', $now->toDateString())
                ->first();

            if ($existingPresensi) {
                $existingPresensi->update([
                    'jam_masuk' => $data['jam_masuk'],
                    'status' => $data['status'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Presensi hari ini berhasil diperbarui.',
                    'data' => $existingPresensi,
                ], 200);
            } else {
                $presensi = Presensi::create($data);

                return response()->json([
                    'success' => true,
                    'message' => 'Presensi berhasil disimpan.',
                    'data' => $presensi,
                ], 201);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan database.',
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
        $modelClass = Presensi::class;

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
        $modelClass = Presensi::class;

        try {
            $rules = $modelClass::getValidationRules('edit');
        
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $presensi = $modelClass::with('presensiSetting')->find($id);

            if (!$presensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            if ($presensi->userPresensi->id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah data ini.',
                ], 403);
            }

            $presensi->update($request->only('tugas'));

            return response()->json([
                'success' => true,
                'message' => 'Data tugas berhasil diperbarui.',
                'data' => $presensi,
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
        $modelClass = Presensi::class;

        try {
            $presensi = $modelClass::find($id);

            if (!$presensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404);
            }

            $presensi->delete();

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

    public function history(Request $request)
    {
        try {
            $userId = auth()->id();
            $timezone = Config::get('app.timezone');

            // Ambil bulan dan tahun dari request, atau gunakan waktu saat ini sebagai default
            $month = $request->input('month', now($timezone)->month);
            $year = $request->input('year', now($timezone)->year);

            // Tentukan awal dan akhir bulan
            $startOfMonth = Carbon::createFromDate($year, $month, 1, $timezone)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            // Buat daftar minggu dalam sebulan (Senin - Minggu)
            $weeks = [];
            $current = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
            while ($current <= $endOfMonth) {
                $weeks[] = [
                    'start' => $current->copy(),
                    'end' => $current->copy()->endOfWeek(Carbon::SUNDAY),
                ];
                $current->addWeek();
            }

            // Tentukan indeks minggu saat ini
            $now = Carbon::now($timezone);
            $currentWeekIndex = null;
            foreach ($weeks as $index => $week) {
                if ($now->between($week['start'], $week['end'])) {
                    $currentWeekIndex = $index;
                    break;
                }
            }

            // Ambil nomor minggu dari request, atau gunakan minggu saat ini sebagai default
            $weekNumber = $request->input('week', $currentWeekIndex !== null ? $currentWeekIndex + 1 : 1);

            // Validasi nomor minggu
            if (!isset($weeks[$weekNumber - 1])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minggu tidak valid.',
                ], 400);
            }

            // Ambil rentang tanggal untuk minggu yang dipilih
            $range = $weeks[$weekNumber - 1];

            // Ambil data presensi DENGAN data setting terkait menggunakan with('presensiSetting')
            $presensi = Presensi::with('presensiSetting') // Diubah sesuai nama relasi
                ->where('id_user', $userId)
                ->whereDate('created_at', '>=', $range['start'])
                ->whereDate('created_at', '<=', $range['end'])
                ->orderBy('created_at', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'week' => [
                    'start' => $range['start']->toDateString(),
                    'end' => $range['end']->toDateString(),
                    'number' => (int)$weekNumber
                ],
                'data' => $presensi
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

<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Image;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Hash;
use Endroid\QrCode\Encoding\Encoding;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{
    public function showUser($id){
        $user = User::find($id);
        if($user){
            return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $user
            ],200);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'Data tidak ditemukan',
            ],404);
        }
    }
    
    public function updateUser(Request $request, $id)
    {
        try {
            $authUser = auth()->user();

            // Hanya boleh edit dirinya sendiri
            if ($authUser->id != $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk mengubah user ini.'
                ], 403);
            }

            // Cari user
            $user = User::findOrFail($id);

            // Ambil rules untuk edit
            $rules = User::getValidationRules('edit');

            // Ubah semua "required" jadi "sometimes"
            foreach ($rules as $field => $rule) {
                $rules[$field] = str_replace('required', 'sometimes', $rule);
            }

            // Kalau password kosong, hapus rule password
            if (!$request->filled('password')) {
                unset($rules['password']);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Ambil data request
            $data = $request->only(User::getAllowedFields('edit'));

            // Isi dengan nilai lama jika tidak dikirim
            foreach (User::getAllowedFields('edit') as $field) {
                if (!array_key_exists($field, $data) || is_null($data[$field])) {
                    $data[$field] = $user->$field;
                }
            }

            // Kalau ada password → hash
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // Update user
            $user->update($data);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui.',
                'data' => $user
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function register(Request $request)
    {
        try {
            $rules = User::getValidationRules('add');
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $request->all();
            $data['password'] = Hash::make($data['password']);

            // 1. Buat user terlebih dahulu untuk mendapatkan ID-nya
            $user = User::create($data);

            /// 2. Tentukan data untuk QR code
            $qrCodeData = url('/api/scan/'.$user->id); 
            $fileName = 'user_' . $user->id . '.png';
            $filePath = 'qrcodes/' . $fileName;

            // 3. Generate QR
            $qrCode = QrCode::create($qrCodeData)
                ->setEncoding(new Encoding('UTF-8'))
                ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
                ->setSize(200);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // 4. Tentukan path & nama file
            $fileName = 'user_' . $user->id . '.png';
            $filePath = 'qrcodes/' . $fileName;

            // 5. Simpan ke storage publik
            Storage::disk('public')->put($filePath, $result->getString());

            // 6. Simpan path ke database
            $user->qrcode_path = $filePath;
            $user->save();

            // Tambahkan URL akses langsung
            $user->qrcode_url = config('app.url') . Storage::url($filePath);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat.',
                'data' => $user,
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

    
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid login credentials.',
                ], 401);
            }

            return $this->respondWithToken($token);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil logout.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(){
        return auth()->user();
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'message' => 'User berhasil login.',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'user' => auth('api')->user(),
            ]
        ]);
    }

    public function uploadImage(Request $request)
    {
        try {
            $rules = Image::getValidationRules('add');
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::where('id', $request->id_user)->firstOrFail();

            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('users/images', 'public');
            }

            $imageModel = Image::updateOrCreate(
                ['id_user' => $request->id_user],
                ['image' => $imagePath]
            );

            $user->update(['images' => $imagePath]);

            return response()->json([
                'success' => true,
                'message' => 'Gambar berhasil diupload.',
                'data' => $imageModel,
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kesalahan database.',
                'error' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function image(){
        $image = Image::with(['userImage'])->get();
        return response()->json([
                'success' => true,
                'message' => 'Data ditemukan',
                'data' => $image
            ], 200);
    }
}

<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

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
            $qrCodeData = url('/api/users/'.$user->id); // atau pakai url('/api/users/'.$user->id)
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
            $user->qrcode_url = Storage::url($filePath);

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

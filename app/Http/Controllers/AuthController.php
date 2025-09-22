<?php 
 
namespace App\Http\Controllers; 
 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Validator; 
use GuzzleHttp\Client; 
use Illuminate\Support\Facades\Cookie; 
 
class AuthController extends Controller 
{ 
    public function loginPage() 
    { 
        return view('auth.login'); 
    } 
 
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $client = new Client();
            $apiLoginUrl = env('APP_URL') . '/api/login';

            $response = $client->post($apiLoginUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'username' => $request->username,
                    'password' => $request->password,
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['success']) && $data['success'] === true && isset($data['token'])) {
                Log::info('Token diterima dari API: ' . substr($data['token'], 0, 10) . '...');

                session(['temp_token' => $data['token']]);
                session(['user' => $data['user'] ?? null]);

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('dashboard.index'),
                ]);
            } else {
                $errorMessage = $data['message'] ?? 'Login gagal. Periksa kembali username dan password Anda.';
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 401);
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $errorBody = json_decode($response->getBody()->getContents(), true);
            $errorMessage = $errorBody['message'] ?? 'Login gagal: kredensial tidak valid.';
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], $e->getCode());
        } catch (\Exception $e) {
            Log::error('Kesalahan login (Guzzle): ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencoba login.',
            ], 500);
        }
    }
    
    public function dashboard()
    {
        $token = session('temp_token');
        session()->forget('temp_token'); 

        return view('dashboard.index', ['token' => $token]);
    }
}
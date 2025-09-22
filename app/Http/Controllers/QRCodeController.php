<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    public function index()
    {
        return view('qr-code');
    }

    public function generate(Request $request)
    {
        $data = $request->input('data');
        $qrCode = QrCode::size(250)->generate($data);

        return view('qr-code', compact('qrCode', 'data'));
    }

     public function show($id)
    {
        // Ambil user dengan id=1
        $user = User::findOrFail($id);

        // Ambil field qrcode_path dari database
        $qrCode = $user->qrcode_path;

        return view('qr-code', compact('user', 'qrCode'));
    }
}
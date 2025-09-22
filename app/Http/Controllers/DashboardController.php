<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Presensi;
use Illuminate\Http\Request;
use App\Models\SettingPresensi;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahUser = User::where('role', 'user')->count();
        $jumlahAdmin = User::where('role', 'admin')->count();
        $jumlahPresensiHariIni = Presensi::where('status', '!=', 'alfa')
            ->whereDate('created_at', Carbon::today())
            ->count();

        return view('dashboard.index', compact(
            'jumlahUser',
            'jumlahAdmin',
            'jumlahPresensiHariIni'
        ));
    }
    public function user()
    {
        $users = User::paginate(20);
        return view('dashboard.user', compact('users'));
    }

    public function setting()
    {
        $settings = SettingPresensi::with(['user', 'presensi.userPresensi'])->get();
        return view('dashboard.setting', compact('settings'));
    }

    public function profil()
    {
        return view('dashboard.profil');
    }

}

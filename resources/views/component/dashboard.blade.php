<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="app-url" content="{{ env('APP_URL') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Dashboard Admin</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    html, body {
      height: 100%;
      overflow: hidden;
    }
  </style>
</head>
<body class="bg-gray-100 font-sans h-full">

  <div class="flex h-full">
    <!-- Sidebar -->
    <aside class="w-64 bg-[#034289] text-white flex-shrink-0 flex flex-col fixed top-0 left-0 bottom-0">
      <div class="px-6 py-4 border-b border-[#022e62] flex items-center space-x-2 text-2xl font-bold">
        <i class="fas fa-user-shield"></i>
        <span>Dashboard Admin</span>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <a href="{{ route('dashboard.index') }}"
           class="flex items-center px-3 py-2 rounded hover:bg-[#022e62] {{ request()->routeIs('dashboard.index') ? 'bg-[#022e62]' : '' }}">
          <i class="fas fa-home mr-2"></i> Dashboard
        </a>
        <a href="{{ route('dashboard.user') }}"
           class="flex items-center px-3 py-2 rounded hover:bg-[#022e62] {{ request()->routeIs('dashboard.user') ? 'bg-[#022e62]' : '' }}">
          <i class="fas fa-users mr-2"></i> User
        </a>
        <a href="{{ route('dashboard.setting') }}"
           class="flex items-center px-3 py-2 rounded hover:bg-[#022e62] {{ request()->routeIs('dashboard.setting') ? 'bg-[#022e62]' : '' }}">
          <i class="fas fa-cogs mr-2"></i> Setting Presensi
        </a>
        <a href="{{ route('dashboard.profil') }}"
           class="flex items-center px-3 py-2 rounded hover:bg-[#022e62] {{ request()->routeIs('dashboard.profil') ? 'bg-[#022e62]' : '' }}">
          <i class="fas fa-user-circle mr-2"></i> Profil Admin
        </a>
      </nav>

      <div class="px-6 py-4 border-t border-[#022e62]">
        <button id="logoutButton"
                class="w-full flex items-center px-3 py-2 rounded hover:bg-[#022e62] text-left text-sm text-white">
          <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 flex-1 h-full overflow-y-auto">
      <header class="bg-white shadow rounded-lg mb-6 sticky top-0 z-10">
        <div class="px-6 py-4 flex justify-between items-center">
          <h1 class="text-xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
          <div class="flex items-center space-x-4">
            <span id="userNameDisplay" class="text-gray-600"></span>
            <div class="relative">
              <a href="{{ route('dashboard.profil') }}">
                <button class="rounded-full bg-gray-200 p-1 w-10 h-10 flex items-center justify-center text-gray-700">
                  <i class="fas fa-user"></i>
                </button>
              </a>
            </div>
          </div>
        </div>
      </header>

      <main class="px-6 pb-6">
        <div class="bg-white shadow rounded-lg p-6">
          @yield('container')
        </div>
      </main>
    </div>
  </div>

  <script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>

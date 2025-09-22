@extends('component.dashboard')

@section('page-title', 'Dashboard')

@section('container')    
<main class="flex-1 p-8">
  <h1 class="text-2xl font-bold mb-6">Statistik Presensi</h1>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Total User</h2>
      <p class="text-4xl font-bold text-blue-600">{{ $jumlahUser }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Total Admin</h2>
      <p class="text-4xl font-bold text-purple-600">{{ $jumlahAdmin }}</p>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
      <h2 class="text-lg font-semibold mb-2">Presensi Hari Ini</h2>
      <p class="text-4xl font-bold text-green-600">{{ $jumlahPresensiHariIni }}</p>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-4">Grafik Statistik</h2>
    <canvas id="dashboardChart" width="400" height="200"></canvas>
  </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const jumlahUser = {{ $jumlahUser }};
    const jumlahAdmin = {{ $jumlahAdmin }};
    const jumlahPresensiHariIni = {{ $jumlahPresensiHariIni }};

    const ctx = document.getElementById('dashboardChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['User', 'Admin', 'Presensi Hari Ini'],
        datasets: [{
          label: 'Jumlah',
          data: [jumlahUser, jumlahAdmin, jumlahPresensiHariIni],
          backgroundColor: ['#3B82F6', '#8B5CF6', '#10B981'],
          borderColor: ['#2563EB', '#7C3AED', '#059669'],
          borderWidth: 1,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0
            }
          }
        }
      }
    });
  });
</script>
@endsection

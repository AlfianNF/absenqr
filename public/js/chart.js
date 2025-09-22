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
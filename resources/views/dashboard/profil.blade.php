@extends('component.dashboard')

@section('container')
<main class="p-6">
    <h1 class="text-2xl font-bold mb-6">Profil Pengguna</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-xl profile-card p-6">
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full bg-indigo-100 flex items-center justify-center mb-4">
                        <i class="fas fa-user-shield text-4xl text-indigo-500"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800" id="display-username">Memuat...</h3>
                    <span class="text-sm text-indigo-500 bg-indigo-50 px-3 py-1 rounded-full mt-2" id="display-role">Memuat...</span>

                    <div class="mt-6 w-full">
                        <div class="flex items-center justify-between py-2 border-b">
                            <span class="text-gray-500">Terdaftar Sejak</span>
                            <span class="text-gray-700" id="member-since">-</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b">
                            <span class="text-gray-500">Status</span>
                            <span class="text-green-700" id="last-login">Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-xl profile-card p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Informasi Profil</h3>
                </div>

                <form id="profile-form" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Username</label>
                        <input type="text" id="username" name="username"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly disabled>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly disabled>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly disabled>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Role</label>
                        <input type="text" id="role" name="role"
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50" readonly disabled>
                    </div>

                    <div id="action-buttons" class="hidden pt-4 flex justify-end space-x-3">
                        <button type="button" id="cancel-btn" class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatTanggalIndonesia(tanggalString) {
        const bulanIndo = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        const tanggal = new Date(tanggalString);
        const hari = tanggal.getDate();
        const bulan = bulanIndo[tanggal.getMonth()];
        const tahun = tanggal.getFullYear();

        return `${hari} ${bulan} ${tahun}`;
    }

    async function fetchUserProfile() {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                alert('Token tidak ditemukan. Pastikan Anda sudah login.');
                return;
            }

            const response = await fetch('/api/me', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) throw new Error('Gagal mengambil data pengguna');

            const user = await response.json();

            document.getElementById('display-username').textContent = user.username || '-';
            document.getElementById('display-role').textContent = user.role || '-';
            document.getElementById('member-since').textContent = user.created_at ? formatTanggalIndonesia(user.created_at) : '-';

            document.getElementById('username').value = user.username || '';
            document.getElementById('email').value = user.email || '';
            document.getElementById('phone').value = user.no_hp || '';
            document.getElementById('role').value = user.role || '';
        } catch (error) {
            console.error(error);
            alert('Gagal memuat data pengguna');
        }
    }

    fetchUserProfile();
});
</script>
@endsection

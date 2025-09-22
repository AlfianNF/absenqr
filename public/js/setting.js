document.addEventListener("DOMContentLoaded", function () {
    const baseUrl = document.querySelector('meta[name="app-url"]').getAttribute("content");
    const token = localStorage.getItem("token");

    // Utility: buka/tutup modal
    function toggleModal(modal, show = true) {
        modal.classList.toggle("hidden", !show);
    }

    // Tambah Presensi
    document.getElementById("addPresensiButton")?.addEventListener("click", () => toggleModal(presensiModalAdd, true));
    document.getElementById("closeModalAdd")?.addEventListener("click", () => toggleModal(presensiModalAdd, false));

    // Tampilkan Detail
    document.querySelectorAll(".showBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            const setting = JSON.parse(btn.dataset.full);

            // Tampilkan informasi setting presensi (ini sudah benar)
            document.getElementById("show_user").textContent = setting.user?.username ?? '—';
            document.getElementById("show_hari").textContent = formatTanggalIndonesia(setting.hari);
            document.getElementById("show_jam_absen").textContent = setting.jam_absen ?? '-';
            document.getElementById("show_jam_pulang").textContent = setting.jam_pulang ?? '-';

            // Bersihkan dan tampilkan daftar presensi dalam bentuk tabel
            const presensiTableBody = document.getElementById("presensi_table_body");
            presensiTableBody.innerHTML = ""; // Kosongkan isi tabel sebelumnya

            (setting.presensi ?? []).forEach(p => {
                const nama = p.user_presensi?.username ?? `User ID ${p.id_user}`;
                const status = p.status ?? '-';
                const masuk = p.jam_masuk ?? '-';
                const keluar = p.jam_keluar ?? '-';

                // Buat elemen baris tabel (<tr>)
                const row = document.createElement("tr");

                // Buat elemen sel tabel (<td>) untuk setiap kolom
                const namaCell = document.createElement("td");
                namaCell.className = "px-3 py-2 whitespace-nowrap"; // Tambahkan class styling
                namaCell.textContent = nama;

                const masukCell = document.createElement("td");
                masukCell.className = "px-3 py-2 whitespace-nowrap"; // Tambahkan class styling
                masukCell.textContent = masuk;

                const keluarCell = document.createElement("td");
                keluarCell.className = "px-3 py-2 whitespace-nowrap"; // Tambahkan class styling
                keluarCell.textContent = keluar;

                const statusCell = document.createElement("td");
                statusCell.className = "px-3 py-2 whitespace-nowrap"; // Tambahkan class styling
                statusCell.textContent = status;

                // Masukkan sel-sel ke dalam baris
                row.appendChild(namaCell);
                row.appendChild(masukCell);
                row.appendChild(keluarCell);
                row.appendChild(statusCell);

                // Masukkan baris ke dalam tbody tabel
                presensiTableBody.appendChild(row);
            });

            toggleModal(presensiModalShow, true);
        });
    });


    document.getElementById("closeModalShow")?.addEventListener("click", () => toggleModal(presensiModalShow, false));

    // Edit Presensi
    document.querySelectorAll(".editBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById("edit_id").value = btn.dataset.id;
            document.getElementById("edit_hari").value = btn.dataset.hari;
            document.getElementById("edit_jam_absen").value = btn.dataset.jam_absen;
            document.getElementById("edit_jam_pulang").value = btn.dataset.jam_pulang;
            toggleModal(presensiModalEdit, true);
        });
    });
    document.getElementById("closeModalEdit")?.addEventListener("click", () => toggleModal(presensiModalEdit, false));

    // Hapus Presensi
    document.querySelectorAll(".deleteBtn").forEach(btn => {
        btn.addEventListener("click", () => {
            document.getElementById("deletePresensi").dataset.id = btn.dataset.id;
            toggleModal(presensiModalDelete, true);
        });
    });
    document.getElementById("closeModalDelete")?.addEventListener("click", () => toggleModal(presensiModalDelete, false));

    // Fungsi ambil ID user dari /api/me
    async function getUserId() {
        const res = await fetch(`${baseUrl}/api/me`, {
            headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || "Gagal ambil user");
        return data.id;
    }

    // Tambah Data Presensi
    document.getElementById("presensiFormAdd")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (!token) return showError("Token tidak ditemukan. Silakan login ulang.");

        try {
            const userId = await getUserId();
            const hari = document.getElementById("add_hari").value;

            // Ambil nilai waktu dan tambahkan ":00"
            const jam_absen_input = document.getElementById("add_jam_absen").value;
            const jam_pulang_input = document.getElementById("add_jam_pulang").value;

            const jam_absen = jam_absen_input ? `${jam_absen_input}:00` : null;
            const jam_pulang = jam_pulang_input ? `${jam_pulang_input}:00` : null;

            const res = await fetch(`${baseUrl}/api/setting-presensi`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
                body: JSON.stringify({ hari, jam_absen, jam_pulang, id_user: userId }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            showSuccess(data.message);
        } catch (error) {
            showError(error.message);
        }
    });


    // Edit Data Presensi
    document.getElementById("presensiFormEdit")?.addEventListener("submit", async function (e) {
        e.preventDefault();
        if (!token) return showError("Token tidak ditemukan. Silakan login ulang.");

        try {
            const userId = await getUserId();
            const id = document.getElementById("edit_id").value;
            const hari = document.getElementById("edit_hari").value;

            // Tambahkan ":00" ke nilai jam
            const jam_absen_input = document.getElementById("edit_jam_absen").value;
            const jam_pulang_input = document.getElementById("edit_jam_pulang").value;

            const jam_absen = jam_absen_input ? `${jam_absen_input}:00` : null;
            const jam_pulang = jam_pulang_input ? `${jam_pulang_input}:00` : null;

            const res = await fetch(`${baseUrl}/api/setting-presensi/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
                body: JSON.stringify({ hari, jam_absen, jam_pulang, id_user: userId }),
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            showSuccess(data.message);
        } catch (error) {
            showError(error.message);
        }
    });


    // Hapus Data Presensi
    document.getElementById("deletePresensi")?.addEventListener("click", async function () {
        const id = this.dataset.id;
        if (!token) return showError("Token tidak ditemukan. Silakan login ulang.");

        try {
            const res = await fetch(`${baseUrl}/api/setting-presensi/${id}`, {
                method: "DELETE",
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: "application/json",
                },
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.message);
            showSuccess(data.message);
        } catch (error) {
            showError(error.message);
        }
    });

    // SweetAlert helpers
    function showSuccess(msg) {
        Swal.fire({ icon: "success", title: "Berhasil", text: msg, timer: 2000, showConfirmButton: false });
        setTimeout(() => window.location.reload(), 2000);
    }

    function showError(msg) {
        Swal.fire({ icon: "error", title: "Gagal", text: msg || "Terjadi kesalahan." });
    }

    function formatTanggalIndonesia(tanggalString) {
        const tanggal = new Date(tanggalString);
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        return tanggal.toLocaleDateString('id-ID', options);
    }

});  
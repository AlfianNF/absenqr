const baseUrl = document.querySelector('meta[name="app-url"]').getAttribute('content');

document.getElementById('loginForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;

  fetch(`${baseUrl}/api/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ username, password })
  })
  .then(res => {
    if (!res.ok) throw res;
    return res.json();
  })
  .then(data => {
    const token = data.data.access_token;
    const role = data.data.role;
    if(role == 'user'){
      Swal.fire({
        icon: 'failed',
        title: 'Login Gagal',
        showConfirmButton: true,
        timer: 1500
      }).then(() => {
        window.location.href = `${baseUrl}/`;
      });
    }
    if (token) {
      localStorage.setItem('token', token);

      Swal.fire({
        icon: 'success',
        title: 'Login Berhasil',
        showConfirmButton: false,
        timer: 1500
      }).then(() => {
        window.location.href = `${baseUrl}/dashboard`;
      });
    } else {
      console.warn('Token tidak ditemukan dalam respons server:', data);
      Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: 'Token tidak ditemukan dalam respons.',
        confirmButtonColor: '#034289'
      });
    }
  })
  .catch(async err => {
    let errorMessage = 'Terjadi kesalahan saat login.';
    if (err.status === 422) {
      const errorData = await err.json();
      errorMessage = Object.values(errorData.errors).flat().join('\n');
    } else if (err.status === 401) {
      errorMessage = 'Username atau password salah.';
    }

    Swal.fire({
      icon: 'error',
      title: 'Login Gagal',
      text: errorMessage,
      confirmButtonColor: '#034289'
    });
  });
});
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = document.querySelector('meta[name="app-url"]').getAttribute('content');

    function getToken() {
        return localStorage.getItem('token');
    }

    function fetchUserData() {
      const token = getToken();
      if (!token) {
          console.error('Token tidak ditemukan di localStorage');
          Swal.fire({
              title: 'Authentication Required',
              text: 'Please log in to access this page.',
              icon: 'warning',
              confirmButtonText: 'Login'
          }).then(() => {
              window.location.href = `${baseUrl}/`;
          });
          return;
      }

      fetch(`${baseUrl}/api/me`, {
          headers: {
              'Authorization': `Bearer ${token}`,
              'Accept': 'application/json'
          }
      })
      .then(res => {
          if (!res.ok) {
              throw new Error('Unauthorized');
          }
          return res.json();
      })
      .then(data => {
          if (data.role !== 'admin') {
              Swal.fire({
                  title: 'Unauthorized',
                  text: 'You do not have permission to access this page.',
                  icon: 'warning',
                  confirmButtonText: 'OK'
              }).then(() => {
                  localStorage.removeItem('token');
                  window.location.href = `${baseUrl}/`;
              });
              return;
          }

          const userNameDisplay = document.getElementById('userNameDisplay');
          if (userNameDisplay && data.user) {
              userNameDisplay.textContent = data.user.name || data.user.username || 'Admin';
          }
      })
      .catch(error => {
          console.error('Error fetching user data:', error);

          if (error.message === 'Unauthorized') {
              Swal.fire({
                  title: 'Session Expired',
                  text: 'Your session has expired. Please login again.',
                  icon: 'warning',
                  confirmButtonText: 'Login'
              }).then(() => {
                  localStorage.removeItem('token');
                  window.location.href = `${baseUrl}/`;
              });
          }
      });
  }

    fetchUserData();

    function handleLogout() {
        Swal.fire({
            title: 'Logout',
            text: 'Apakah Anda yakin ingin logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                localStorage.removeItem('token');
                window.location.href = `${baseUrl}/`;
            }
        });
    }

    document.getElementById('logoutButton')?.addEventListener('click', handleLogout);

});

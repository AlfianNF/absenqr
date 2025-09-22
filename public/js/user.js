const addUserButton = document.getElementById('addUserButton');
const addUserModal = document.getElementById('addUserModal');
const closeButtons = document.querySelectorAll('.close-button');
const userTableBody = document.getElementById('userTableBody');
const addForm = document.getElementById('addForm');
const baseUrl = 'http://presensi.test'

let token = localStorage.getItem('token');
console.log(token);

let userIdToEdit = null;
let userIdToDelete = null;

addUserButton.addEventListener('click', () => {
  addUserModal.classList.remove('hidden');
  document.body.classList.add('overflow-hidden');
});

closeButtons.forEach(button => {
  button.addEventListener('click', () => {
    addUserModal.classList.add('hidden');
    editUserModal.classList.add('hidden');
    deleteUserModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  });
});



window.addEventListener('click', (event) => {
  if (event.target.classList.contains('fixed')) {
    addUserModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }
});

addForm.addEventListener('submit', (event) => {
  event.preventDefault();

  const no_hp = document.getElementById('add_no_hp').value;
  const username = document.getElementById('add_username').value;
  const email = document.getElementById('add_email').value;
  const password = document.getElementById('add_password').value;
  const role = document.getElementById('add_role').value;


  fetch(`${baseUrl}/api/register`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    },
    body: JSON.stringify({ no_hp, username, email, password, role }),
  })
  .then(response => {
    if (!response.ok) {
      return response.json().then(data => {
        throw new Error(data.message || 'Failed to add user');
      });
    }
    return response.json();
  })
  .then(data => {
    const newUserRow = document.createElement('tr');
    newUserRow.classList.add('hover:bg-gray-50');
    newUserRow.dataset.userId = data.data.id;
    newUserRow.innerHTML = `
      <td class="px-6 py-4">${userTableBody.children.length + 1}</td>
      <td class="px-6 py-4">${data.data.no_hp}</td>
      <td class="px-6 py-4">${data.data.username}</td>
      <td class="px-6 py-4">${data.data.email}</td>
      <td class="px-6 py-4 capitalize">${data.data.role}</td>
    `;
    userTableBody.appendChild(newUserRow);

    addForm.reset();
    addUserModal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: data.message,
          timer: 2000,
          showConfirmButton: false
        });


  })
    .catch(async error => {
      let message = 'Terjadi kesalahan.';

      if (error.response && error.response.status === 422) {
        const errorData = await error.response.json();
        if (errorData.errors) {
          message = Object.values(errorData.errors).flat().join('\n');
        } else if (errorData.message) {
          message = errorData.message;
        }
      } else if (error.message) {
        message = error.message;
      }

      Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        text: message
      });
    });

});

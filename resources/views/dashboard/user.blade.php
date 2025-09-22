@extends('component.dashboard')

@section('container')
<main class="flex-1 p-8">
  <h1 class="text-2xl font-bold mb-6">Daftar Pengguna</h1>

  <div class="mb-4">
    <button id="addUserButton" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
      <i class="fas fa-plus mr-2"></i> Tambah Pengguna
    </button>
  </div>

  <div class="overflow-x-auto bg-white shadow-md rounded-lg">
    <table class="min-w-full divide-y divide-gray-200 text-sm text-left">
      <thead class="bg-[#034289] text-white">
        <tr>
          <th class="px-6 py-3 font-semibold">No</th>
          <th class="px-6 py-3 font-semibold">No HP</th>
          <th class="px-6 py-3 font-semibold">Username</th>
          <th class="px-6 py-3 font-semibold">Email</th>
          <th class="px-6 py-3 font-semibold">Role</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200" id="userTableBody">
        @foreach ($users as $index => $user)
        <tr class="hover:bg-gray-50" data-user-id="{{ $user->id }}">
          <td class="px-6 py-4">{{ $index + 1 }}</td>
          <td class="px-6 py-4">{{ $user->no_hp }}</td>
          <td class="px-6 py-4">{{ $user->username }}</td>
          <td class="px-6 py-4">{{ $user->email }}</td>
          <td class="px-6 py-4 capitalize">{{ $user->role }}</td>
        </tr>
        @endforeach

        @if ($users->isEmpty())
        <tr>
          <td colspan="5" class="text-center px-6 py-4 text-gray-500">Tidak ada data pengguna.</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
</main>

<div id="addUserModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 transition-opacity" aria-hidden="true">
      <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h2 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Tambah Pengguna
            </h2>
            <form id="addForm" class="mt-4 space-y-4">
              <div>
                <label for="add_name" class="block text-gray-700 text-sm font-bold mb-2">No HP</label>
                <input type="text" id="add_no_hp" name="no_hp" required
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
              </div>
              <div>
                <label for="add_username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="add_username" name="username" required
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
              </div>
              <div>
                <label for="add_email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="add_email" name="email" required
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
              </div>
              <div>
                <label for="add_password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="add_password" name="password" required
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" />
              </div>
              <div>
                <label for="add_role" class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                <select id="add_role" name="role" required
                  class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                </select>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('addForm').dispatchEvent(new Event('submit'))">
          Simpan
        </button>
        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-button">
          Batal
        </button>
      </div>
    </div>
  </div>
</div>
<script src="{{ asset('js/user.js') }}"></script>
@endsection

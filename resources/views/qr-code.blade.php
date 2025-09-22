<!DOCTYPE html>
<html>
<head>
    <title>QR Code User #1</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>Detail User #{{ $user->id }}</h2>

    <ul>
        <li><strong>Username:</strong> {{ $user->username }}</li>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>No HP:</strong> {{ $user->no_hp }}</li>
        <li><strong>Role:</strong> {{ $user->role }}</li>
    </ul>

    <h3>QR Code (dari database)</h3>
    <div>
        {{-- tampilkan QR Code dari base64 --}}
        <img src="{{ $qrCode }}" alt="QR Code User">
    </div>
</body>
</html>

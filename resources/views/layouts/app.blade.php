<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'S-KOLAK')
    </title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800">

    @include('components.navbar')

    <main class="min-h-screen">
        @yield('content')
    </main>

    @include('components.footer')

</body>

</html>
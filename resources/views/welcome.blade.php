<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sange Integrator</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 text-gray-900 flex items-center justify-center min-h-screen">
<div class="text-center">
    <h1 class="text-4xl font-bold mb-4">Sange Integrator</h1>
    <p class="text-lg text-gray-600">Laravel + PostgreSQL + Tailwind CSS + Docker</p>
</div>
</body>
</html>

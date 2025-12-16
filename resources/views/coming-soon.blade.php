<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PowerGenStock - Coming Soon</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Outfit', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased bg-gray-50 flex items-center justify-center min-h-screen text-gray-800">
        <div class="text-center p-8">
            <h1 class="text-5xl md:text-6xl font-bold tracking-tight text-gray-900 mb-4">
                PowerGenStock
            </h1>
            <p class="text-xl md:text-2xl font-light text-gray-600 mb-8">
                Power Generation Stock Inventory
            </p>
            <div class="inline-block px-8 py-3 border border-gray-300 rounded-full bg-white shadow-sm text-sm uppercase tracking-widest font-semibold text-gray-500">
                Coming Soon
            </div>
            
            <div class="mt-12 text-sm text-gray-400">
                &copy; {{ date('Y') }} PowerGenStock. All rights reserved.
            </div>
        </div>
    </body>
</html>

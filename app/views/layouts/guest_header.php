<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Guest Area' ?> - Survey Parking</title>

    <link rel="stylesheet" href="<?php echo BASE_URL ?>/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style> [x-cloak] { display: none !important; } </style>
</head>
<body class="bg-gray-100">

    <nav class="bg-white shadow-md print:hidden">
        <div class="container mx-auto px-6 py-3">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-xl font-bold text-gray-800">
                    <a href="<?php echo BASE_URL ?>/guest">Survey Parking</a>
                </div>
                <div class="mt-2 md:mt-0">
                    <span class="text-gray-700 mr-4">Halo,                                                           <?php echo htmlspecialchars($_SESSION['username']) ?>!</span>
                    <a href="<?php echo BASE_URL ?>/auth/logout" id="logout-link" class="px-4 py-2 bg-red-500 text-white text-sm rounded-md hover:bg-red-600 transition-colors">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 md:p-6">
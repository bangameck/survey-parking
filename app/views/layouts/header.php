<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Survey Parking' ?> - Dashboard</title>

    <link rel="stylesheet" href="<?php echo BASE_URL ?>/css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style> [x-cloak] { display: none !important; } </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-200">

    <div :class="sidebarOpen ? 'block' : 'hidden'" @click="sidebarOpen = false" class="fixed inset-0 z-20 transition-opacity bg-black opacity-50 lg:hidden print:hidden"></div>
    <div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition duration-300 transform bg-white shadow-lg lg:translate-x-0 lg:static lg:inset-0 print:hidden">

        <div class="flex items-center justify-center mt-8">
            <div class="flex items-center">
                <svg class="w-12 h-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="ml-2 text-2xl font-semibold text-gray-800">SURVEY PARKIR</span>
            </div>
        </div>

        <nav class="mt-10">
            <a class="flex items-center px-6 py-2 mt-4 text-gray-700                                                                                                                                                                                                                                                                                                                                                     <?php echo(strpos($_GET['url'], 'admin') !== false) ? 'bg-gray-200' : 'hover:bg-gray-200' ?>" href="<?php echo BASE_URL ?>/admin">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="mx-3">Dashboard</span>
            </a>
            <a class="flex items-center px-6 py-2 mt-4 text-gray-500                                                                                                                                                                                                                                                                                                                                                     <?php echo(strpos($_GET['url'], 'fieldcoordinators') !== false) ? 'bg-gray-200 text-gray-700' : 'hover:bg-gray-200 hover:text-gray-700' ?>" href="<?php echo BASE_URL ?>/fieldcoordinators">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="mx-3">Koordinator</span>
            </a>
            <a class="flex items-center px-6 py-2 mt-4 text-gray-500                                                                                                                                                                                                                                                                                                                                                     <?php echo(strpos($_GET['url'], 'parkinglocations') !== false) ? 'bg-gray-200 text-gray-700' : 'hover:bg-gray-200 hover:text-gray-700' ?>" href="<?php echo BASE_URL ?>/parkinglocations">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="mx-3">Lokasi Parkir</span>
            </a>
            <a class="flex items-center px-6 py-2 mt-4 text-gray-500                                                                                                                                                                                                                                                                                                                                                     <?php echo(strpos($_GET['url'], 'parkingdeposits') !== false) ? 'bg-gray-200 text-gray-700' : 'hover:bg-gray-200 hover:text-gray-700' ?>" href="<?php echo BASE_URL ?>/parkingdeposits">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="mx-3">Input Setoran</span>
            </a>
            <a class="flex items-center px-6 py-2 mt-4 text-gray-500 hover:bg-gray-200 hover:text-gray-700" href="<?php echo BASE_URL ?>/backup">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4M4 7l8 4l8-4m-8 4v10"></path></svg>
    <span class="mx-3">Backup Database</span>
</a>
        </nav>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">

        <header class="flex items-center justify-between px-6 py-4 bg-white border-b-2 print:hidden">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none"><path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 ml-2"><?php echo $title ?? 'Dashboard' ?></h2>
            </div>

            <div class="flex items-center">
                <div x-data="{ dropdownOpen: false }" class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="relative block h-8 w-8 overflow-hidden rounded-full shadow focus:outline-none">
                        <img class="object-cover w-full h-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'A') ?>&background=random" alt="Avatar">
                    </button>
                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-cloak class="absolute right-0 w-48 mt-2 py-2 bg-white rounded-md shadow-xl z-10">
                        <a href="<?php echo BASE_URL ?>/auth/logout" id="logout-link" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-500 hover:text-white">Logout</a>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="container mx-auto">
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Flood Warning System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <i class="fas fa-database text-4xl text-blue-600 mb-4"></i>
                <h2 class="text-3xl font-extrabold text-gray-900">Setup Database</h2>
                <p class="mt-2 text-sm text-gray-600">
                    Inisialisasi database untuk Flood Warning System
                </p>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                echo '<div id="setupResult" class="mb-6">';
                
                try {
                    require_once 'config/database.php';
                    
                    $db = new Database();
                    $db->createTables();
                    
                    echo '<div class="bg-green-50 border border-green-200 rounded-lg p-4">';
                    echo '<div class="flex">';
                    echo '<i class="fas fa-check-circle text-green-400 mt-0.5 mr-3"></i>';
                    echo '<div>';
                    echo '<h3 class="text-sm font-medium text-green-800">Setup Berhasil!</h3>';
                    echo '<div class="mt-2 text-sm text-green-700">';
                    echo '<p>Database dan tabel berhasil dibuat:</p>';
                    echo '<ul class="list-disc list-inside mt-2">';
                    echo '<li>sensor_readings - untuk data sensor</li>';
                    echo '<li>system_events - untuk log sistem</li>';
                    echo '<li>system_config - untuk konfigurasi</li>';
                    echo '<li>alert_history - untuk riwayat alert</li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    echo '<div class="text-center">';
                    echo '<a href="index.html" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">';
                    echo '<i class="fas fa-arrow-right mr-2"></i>';
                    echo 'Lanjut ke Dashboard';
                    echo '</a>';
                    echo '</div>';
                    
                } catch (Exception $e) {
                    echo '<div class="bg-red-50 border border-red-200 rounded-lg p-4">';
                    echo '<div class="flex">';
                    echo '<i class="fas fa-exclamation-triangle text-red-400 mt-0.5 mr-3"></i>';
                    echo '<div>';
                    echo '<h3 class="text-sm font-medium text-red-800">Setup Gagal!</h3>';
                    echo '<div class="mt-2 text-sm text-red-700">';
                    echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                    echo '<p class="mt-2">Pastikan:</p>';
                    echo '<ul class="list-disc list-inside mt-1">';
                    echo '<li>MySQL server sudah running</li>';
                    echo '<li>Database "flood_warning_db" sudah dibuat</li>';
                    echo '<li>User memiliki permission yang cukup</li>';
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
            }
            ?>

            <form method="POST" class="mt-8 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Konfigurasi Database
                    </label>
                    <div class="bg-white p-4 border border-gray-300 rounded-lg">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Host:</span>
                                <span class="font-medium">localhost</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database:</span>
                                <span class="font-medium">flood_warning_db</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Username:</span>
                                <span class="font-medium">root</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Password:</span>
                                <span class="font-medium">(empty)</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Ubah konfigurasi di file config/database.php jika diperlukan
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="fas fa-info-circle text-blue-400 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-medium">Sebelum setup, pastikan:</p>
                            <ul class="list-disc list-inside mt-1 space-y-1">
                                <li>MySQL/MariaDB sudah terinstall dan running</li>
                                <li>Database "flood_warning_db" sudah dibuat</li>
                                <li>PHP extension mysqli sudah aktif</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-play text-blue-500 group-hover:text-blue-400"></i>
                        </span>
                        Mulai Setup Database
                    </button>
                </div>
            </form>

            <div class="text-center text-sm text-gray-500">
                <p>Flood Warning System v1.0</p>
                <p>Powered by Emergent AI</p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
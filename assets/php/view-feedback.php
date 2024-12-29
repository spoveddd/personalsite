<?php
session_start();
date_default_timezone_set('Europe/Moscow'); // Устанавливаем московский часовой пояс

// Проверка, если не авторизован, перенаправляем на страницу логина
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Подключение к базе данных
$db = new SQLite3('../db/feedback.db');

// Получение всех записей
$result = $db->query('SELECT * FROM feedback ORDER BY created_at DESC');

// Функция для запроса данных из Prometheus API
function fetchPrometheusData($query) {
    $url = 'http://prometheus.pavlovich.live/api/v1/query?query=' . urlencode($query);
    $response = file_get_contents($url);
    if ($response === false) {
        return null;
    }
    $data = json_decode($response, true);
    if ($data['status'] === 'success' && isset($data['data']['result'][0]['value'])) {
        return $data['data']['result'][0]['value'][1];
    }
    return null;
}

// Получаем метрики из Prometheus
$serverUptime = fetchPrometheusData('node_time_seconds - node_boot_time_seconds');
$loadAverage = fetchPrometheusData('node_load1');
$cpuUsage = fetchPrometheusData('100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[1m])) * 100)'); // Загрузка CPU
$ramUsed = fetchPrometheusData('(node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes) / node_memory_MemTotal_bytes * 100'); // Использование RAM в %
$diskUsage = fetchPrometheusData('(node_filesystem_size_bytes{mountpoint="/"} - node_filesystem_free_bytes{mountpoint="/"}) / node_filesystem_size_bytes{mountpoint="/"} * 100'); // Использование диска
$networkTraffic = fetchPrometheusData('rate(node_network_receive_bytes_total[1m]) + rate(node_network_transmit_bytes_total[1m])'); // Сетевой трафик в байтах/сек
$responseTime = fetchPrometheusData('probe_duration_seconds{job="blackbox"}');

// Обработка выхода (сброс сессии)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        session_destroy(); // Завершаем сессию
        header("Location: /index.html"); // Перенаправляем на главную страницу
        exit;
    }

    // Обработка удаления всех отзывов
    if (isset($_POST['clear_reviews'])) {
        // Получаем все файлы из базы данных
        $result = $db->query('SELECT file FROM feedback');
        while ($row = $result->fetchArray()) {
            $filePath = $row['file'];
    
            // Проверяем, что путь к файлу существует, и если файл есть, удаляем его
            if (file_exists($filePath)) {
                unlink($filePath); // Удаляем файл
            }
        }
    
        // Теперь удаляем все записи из базы данных
        $db->exec('DELETE FROM feedback');
    
        // Перенаправляем на страницу с отзывами
        header("Location: view-feedback.php"); 
        exit;
    }
    
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <section class="feedback-section">
        <div class="header">
            <h2 class="section-title">Отзывы</h2>
            <form method="post" class="logout-form">
                <button type="submit" name="logout" class="button">Вернуться на главную</button>
                <button type="submit" name="clear_reviews" class="button">Очистить отзывы</button>

            </form>
        </div>
       
        <!-- Блок мониторинга -->
        <div class="monitoring-section">
            <h3 class="monitoring-title">Состояниие сервера</h3>
            <div class="monitoring-metrics">
                <p><strong>Uptime сервера:</strong> <?php echo htmlspecialchars($serverUptime ? gmdate('H:i:s', $serverUptime) : 'Недоступно'); ?></p>
                <p><strong>Средняя нагрузка (LA):</strong> <?php echo htmlspecialchars($loadAverage ? number_format($loadAverage, 2) : 'Недоступно'); ?></p>
                <p><strong>Использование CPU:</strong> <?php echo htmlspecialchars($cpuUsage ? number_format($cpuUsage, 2) : 'Недоступно'); ?>%</p>
                <p><strong>Использование RAM:</strong> <?php echo htmlspecialchars($ramUsed ? number_format($ramUsed, 2) : 'Недоступно'); ?>%</p>
                <p><strong>Использование диска:</strong> <?php echo htmlspecialchars($diskUsage ? number_format($diskUsage, 2) : 'Недоступно'); ?>%</p>
                <p><strong>Сетевой трафик:</strong> <?php echo htmlspecialchars($networkTraffic ? number_format($networkTraffic / 1024 / 1024, 2) : 'Недоступно'); ?> MB/s</p>
                <p><strong>Время отклика:</strong> <?php echo htmlspecialchars($responseTime ? number_format($responseTime, 3) . ' сек.' : 'Недоступно'); ?></p>          
            </div>
        </div>

        <div class="feedback-list">
            <?php while ($row = $result->fetchArray()) { ?>
                <div class="feedback-item">
                    <div class="feedback-header">
                        <div class="feedback-field">
                            <strong>Имя:</strong>
                            <span class="feedback-name"><?php echo htmlspecialchars($row['name']); ?></span>
                        </div>
                        <div class="feedback-field">
                            <strong>Название компании:</strong>
                            <span class="feedback-company"><?php echo htmlspecialchars($row['company']); ?></span>
                        </div>
                        <div class="feedback-field">
                            <strong>Почта:</strong>
                            <span class="feedback-email"><?php echo htmlspecialchars($row['email']); ?></span>
                        </div>
                    </div>
                    <div class="feedback-message-container">
                        <strong>Содержание:</strong>
                        <p class="feedback-message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                    </div>
                    <small class="feedback-date">
                        <?php
                        $created_at = new DateTime($row['created_at'], new DateTimeZone('UTC')); // Исходная дата в UTC
                        $created_at->setTimezone(new DateTimeZone('Europe/Moscow')); // Переводим в Московский часовой пояс
                        echo "Оставлено: " . $created_at->format('Y-m-d H:i:s'); // Форматируем для вывода
                        ?>
                    </small>
                    <?php if (!empty($row['file'])) { // Если есть файл ?>
                        <div class="feedback-file">
                            <strong>Загруженный файл:</strong>
                            <?php 
                            $filePath = $row['file']; 
                            $fileName = basename($filePath);
                            $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
                            
                            // Показать имя файла
                            echo "<span class='feedback-file-name'>$fileName - </span>";
                            
                            // Проверяем тип файла
                            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) { 
                                echo "<img src='../uploads/$fileName' alt='$fileName' class='feedback-file-image' />";
                            } elseif (in_array($fileExtension, ['pdf'])) {
                                echo "<a href='../uploads/$fileName' target='_blank' class='feedback-file-link'>Открыть PDF</a>";
                            } elseif (in_array($fileExtension, ['doc', 'docx'])) {
                                echo "<a href='../uploads/$fileName' target='_blank' class='feedback-file-link'>Скачать Word документ</a>";
                            } else {
                                echo "<a href='../uploads/$fileName' target='_blank' class='feedback-file-link'>Скачать файл</a>";
                            }
                            ?>                              
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>
</body>
</html>

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

// Обработка выхода (сброс сессии)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        session_destroy(); // Завершаем сессию
        header("Location: index.html"); // Перенаправляем на главную страницу
        exit;
    }

    // Обработка удаления всех отзывов
    if (isset($_POST['clear_reviews'])) {
        $db->exec('DELETE FROM feedback'); // Удаляем все записи из базы
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
    <link rel="stylesheet" href="assets/css/styles.css">
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
                </div>
            <?php } ?>
        </div>
    </section>
</body>
</html>

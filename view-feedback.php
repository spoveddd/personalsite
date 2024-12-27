<?php
// Подключение к базе данных
$db = new SQLite3('feedback.db');

// Получение всех записей
$result = $db->query('SELECT * FROM feedback ORDER BY created_at DESC');

// Отображение данных
echo "<h1>Отзывы</h1>";
while ($row = $result->fetchArray()) {
    echo "<div>";
    // Имя пользователя и почта
    echo "<h3>" . htmlspecialchars($row['name']);
    
    // Добавляем название компании, если оно есть
    if (!empty($row['company'])) {
        echo " <small>(" . htmlspecialchars($row['company']) . ")</small>";
    }
    echo " (" . htmlspecialchars($row['email']) . ")</h3>";
    
    // Сообщение
    echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>";
    
    // Дата создания отзыва
    echo "<small>Оставлено: " . $row['created_at'] . "</small>";
    echo "</div><hr>";
}
?>

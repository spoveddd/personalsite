<?php
// Подключение к базе данных
$db = new SQLite3('feedback.db');

// Получение всех записей
$result = $db->query('SELECT * FROM feedback ORDER BY created_at DESC');

// Отображение данных
echo "<h1>Отзывы</h1>";
while ($row = $result->fetchArray()) {
    echo "<div>";
    echo "<h3>" . htmlspecialchars($row['name']) . " (" . htmlspecialchars($row['email']) . ")</h3>";
    echo "<p>" . nl2br(htmlspecialchars($row['message'])) . "</p>";
    echo "<small>Оставлено: " . $row['created_at'] . "</small>";
    echo "</div><hr>";
}
?>

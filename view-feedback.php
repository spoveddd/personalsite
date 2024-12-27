<?php
// Открытие базы данных
$db = new SQLite3('feedback.db');

// Запрос на получение всех отзывов
$query = "SELECT * FROM feedback ORDER BY id DESC";
$result = $db->query($query);

echo "Отзывы";
echo "<table>";
echo "<tr><th>Имя</th><th>Компания</th><th>Почта</th><th>Сообщение</th></tr>";

while ($row = $result->fetchArray()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['company']) . "</td>";  // Показываем название компании
    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
    echo "<td>" . htmlspecialchars($row['message']) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>

<?php
// Подключение к базе данных
$db = new SQLite3('feedback.db');

// Получение данных из формы
$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];

// Защита от SQL-инъекций
$stmt = $db->prepare('INSERT INTO feedback (name, email, message) VALUES (:name, :email, :message)');
$stmt->bindValue(':name', $name, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);

// Выполнение запроса
if ($stmt->execute()) {
    echo "Ваше сообщение успешно отправлено!";
} else {
    echo "Ошибка при отправке сообщения.";
}
?>

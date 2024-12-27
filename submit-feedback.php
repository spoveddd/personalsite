<?php
// Открытие базы данных
$db = new SQLite3('feedback.db');

// Получаем данные из формы
$name = $_POST['name'];
$company = isset($_POST['company']) ? $_POST['company'] : ''; 
$email = isset($_POST['email']) ? $_POST['email'] : ''; 
$message = $_POST['message'];

// Вставка данных в базу
$query = "INSERT INTO feedback (name, company, email, message) VALUES (:name, :company, :email, :message)";
$stmt = $db->prepare($query);

// Привязываем параметры
$stmt->bindValue(':name', $name, SQLITE3_TEXT);
$stmt->bindValue(':company', $company, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);

// Выполняем запрос и возвращаем JSON ответ
$response = [];
if ($stmt->execute()) {
    $response['status'] = 'success';
    $response['message'] = 'Спасибо за ваш отзыв!';
} else {
    $response['status'] = 'error';
    $response['message'] = 'Произошла ошибка при отправке.';
}

// Возвращаем ответ в формате JSON
echo json_encode($response);
?>

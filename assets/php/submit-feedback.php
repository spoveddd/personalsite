<?php

header('Content-Type: application/json; charset=utf-8'); 

// Открытие базы данных
$db = new SQLite3('../db/feedback.db');

// Получаем данные из формы
$name = $_POST['name'];
$company = isset($_POST['company']) ? $_POST['company'] : ''; 
$email = isset($_POST['email']) ? $_POST['email'] : ''; 
$message = $_POST['message'];

// Обработка загруженного файла
$file = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Директория для сохранения файлов
    $uploadDir = '../uploads/';
    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDir . $fileName;

    // Перемещение загруженного файла в нужную директорию
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $file = $filePath;  // Сохраняем путь к файлу
    } else {
        echo json_encode(["status" => "error", "message" => "Ошибка при загрузке файла."]);
        exit;
    }
}

// Вставка данных в базу
$query = "INSERT INTO feedback (name, company, email, message) VALUES (:name, :company, :email, :message)";
$stmt = $db->prepare($query);

// Привязываем параметры
$stmt->bindValue(':name', $name, SQLITE3_TEXT);
$stmt->bindValue(':company', $company, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);

// Выполняем запрос
if ($stmt->execute()) {
    // Ответ с успешной отправкой
    echo json_encode(["status" => "success", "message" => "Спасибо за ваш отзыв!"]);
} else {
    // Ответ с ошибкой
    echo json_encode(["status" => "error", "message" => "Произошла ошибка при отправке."]);
}
?>

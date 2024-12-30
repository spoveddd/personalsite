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

if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $fileError = $_FILES['file']['error'];

    // Проверяем наличие ошибок при загрузке
    if ($fileError === UPLOAD_ERR_OK) {
        $allowedTypes = [
            'image/jpeg', 
            'image/png', 
            'application/pdf', 
            'application/msword',       
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' 
        ];

        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            echo json_encode(["status" => "error", "message" => "Неверный тип файла."]);
            exit;
        }

        // Ограничение размера файла
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES['file']['size'] > $maxFileSize) {
            echo json_encode(["status" => "error", "message" => "Размер файла слишком большой."]);
            exit;
        }

        // Директория для сохранения файлов
        $uploadDir = '../uploads/';
        $fileName = basename($_FILES['file']['name']);
        $filePath = $uploadDir . $fileName;

        // Перемещение загруженного файла в нужную директорию
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $file = $filePath;  // Сохраняем путь к файлу
        } else {
            echo json_encode(["status" => "error", "message" => "Не удалось переместить файл."]);
            exit;
        }
    } else {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'Размер файла превышает допустимый сервером.',
            UPLOAD_ERR_FORM_SIZE => 'Размер файла превышает допустимый формой.',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен частично.',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен.',
            UPLOAD_ERR_NO_TMP_DIR => 'Временная папка отсутствует.',
            UPLOAD_ERR_CANT_WRITE => 'Ошибка записи файла на диск.',
            UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением PHP.',
        ];
        $message = isset($errorMessages[$fileError]) ? $errorMessages[$fileError] : 'Неизвестная ошибка.';
        echo json_encode(["status" => "error", "message" => "Ошибка при загрузке файла: " . $message]);
        exit;
    }
}

// Вставка данных в базу
$query = "INSERT INTO feedback (name, company, email, message, file) VALUES (:name, :company, :email, :message, :file)";
$stmt = $db->prepare($query);

// Привязываем параметры
$stmt->bindValue(':name', $name, SQLITE3_TEXT);
$stmt->bindValue(':company', $company, SQLITE3_TEXT);
$stmt->bindValue(':email', $email, SQLITE3_TEXT);
$stmt->bindValue(':message', $message, SQLITE3_TEXT);
$stmt->bindValue(':file', $file, SQLITE3_TEXT);  

// Выполняем запрос
if ($stmt->execute()) {
    // Ответ с успешной отправкой
    echo json_encode(["status" => "success", "message" => "Спасибо за ваш отзыв!"]);
} else {
    // Ответ с ошибкой
    echo json_encode(["status" => "error", "message" => "Произошла ошибка при отправке."]);
}

?>

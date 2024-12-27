<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных
$db = new SQLite3('feedback.db');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Проверяем, существует ли пользователь с таким логином
        $query = $db->prepare("SELECT * FROM users WHERE username = :username");
        $query->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $query->execute();
        $user = $result->fetchArray();

        if ($user && password_verify($password, $user['password'])) {
            // Успешный вход, сохраняем имя пользователя и статус авторизации в сессии
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;  // Устанавливаем статус авторизации
            header('Location: view-feedback.php');
            exit;
        } else {
            $error = "Неверный логин или пароль!";
        }
    } else {
        $error = "Пожалуйста, заполните все поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <section class="login-section">
        <h2 class="section-title">Авторизация</h2>
        <?php if (isset($error)) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>
        <form action="" method="post">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Войти</button>
        </form>
    </section>
</body>
</html>

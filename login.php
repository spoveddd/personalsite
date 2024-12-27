<?php
session_start(); // Стартуем сессию

// Проверка, если уже авторизован, перенаправляем на страницу отзывов
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: view-feedback.php');
    exit;
}

// Обработчик формы логина
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Устанавливаем логин и пароль для админа
    $admin_username = 'admin';
    $admin_password = 'admin_password'; // Лучше хранить пароли в зашифрованном виде!

    // Проверяем данные
    if ($username === $admin_username && $password === $admin_password) {
        // Успешный логин
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: view-feedback.php'); // Перенаправляем на страницу с отзывами
        exit;
    } else {
        $error = 'Неверное имя пользователя или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логин</title>
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

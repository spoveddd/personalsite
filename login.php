<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных
echo "Текущий путь: " . __DIR__;  // нужно удалить после отладки
$db = new SQLite3('feedback.db');

$db_path = realpath('feedback.db');
echo "PHP использует базу данных по пути: $db_path";

// Устанавливаем максимальное количество попыток
$max_attempts = 3;
$wait_time = 5 * 60; // 5 минут

// Если превышен лимит попыток
if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= $max_attempts) {
    if (isset($_SESSION['last_attempt']) && time() - $_SESSION['last_attempt'] < $wait_time) {
        $remaining_time = $wait_time - (time() - $_SESSION['last_attempt']);
        $error = "Слишком много неудачных попыток. Попробуйте снова через " . gmdate("i:s", $remaining_time) . ".";
    } else {
        // Сброс попыток, если прошло достаточно времени
        unset($_SESSION['attempts']);
        unset($_SESSION['last_attempt']);
    }
}

$tables = $db->query("SELECT name FROM feedback WHERE type='table';");  // нужно удалить после отладки
while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {  // нужно удалить после отладки
    echo "Таблица: " . $table['name'] . "<br>";  // нужно удалить после отладки
}  // нужно удалить после отладки
if (!$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchArray()) {  // нужно удалить после отладки
    echo "Таблица 'users' не найдена в базе данных."; // нужно удалить после отладки
} // нужно удалить после отладки


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
            // Успешный вход, сбрасываем счетчик попыток
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            unset($_SESSION['attempts']);
            unset($_SESSION['last_attempt']);
            header('Location: view-feedback.php');
            exit;
        } else {
            // Неверный логин или пароль
            $error = "Неверный логин или пароль!";

            // Увеличиваем количество попыток
            if (!isset($_SESSION['attempts'])) {
                $_SESSION['attempts'] = 0;
            }
            $_SESSION['attempts']++;

            // Записываем время последней попытки
            $_SESSION['last_attempt'] = time();

            // Если попыток больше чем максимально допустимое количество
            if ($_SESSION['attempts'] >= $max_attempts) {
                $error = "Слишком много неудачных попыток. Попробуйте снова через 5 минут.";
            }
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

<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Подключаемся к базе данных users.db
echo "Текущий путь: " . __DIR__; // отладка
$db = new SQLite3('users.db');

$db_path = realpath('users.db');
echo "PHP использует базу данных по пути: $db_path";

// Проверяем наличие таблицы users
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';");
while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
    echo "Таблица: " . $table['name'] . "<br>";
}
if (!$db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchArray()) {
    die("Таблица 'users' не найдена в базе данных."); // Прекращаем выполнение
}

// Логика ограничения попыток
$max_attempts = 3;
$wait_time = 5 * 60; // 5 минут

if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= $max_attempts) {
    if (isset($_SESSION['last_attempt']) && time() - $_SESSION['last_attempt'] < $wait_time) {
        $remaining_time = $wait_time - (time() - $_SESSION['last_attempt']);
        $error = "Слишком много неудачных попыток. Попробуйте снова через " . gmdate("i:s", $remaining_time) . ".";
    } else {
        unset($_SESSION['attempts']);
        unset($_SESSION['last_attempt']);
    }
}

// Логика обработки формы
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username'], $_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Проверяем пользователя
        $query = $db->prepare("SELECT * FROM users WHERE username = :username");
        $query->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $query->execute();
        $user = $result->fetchArray();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['logged_in'] = true;
            unset($_SESSION['attempts']);
            unset($_SESSION['last_attempt']);
            header('Location: view-feedback.php');
            exit;
        } else {
            $error = "Неверный логин или пароль!";
            if (!isset($_SESSION['attempts'])) {
                $_SESSION['attempts'] = 0;
            }
            $_SESSION['attempts']++;
            $_SESSION['last_attempt'] = time();
            if ($_SESSION['attempts'] >= $max_attempts) {
                $error = "Слишком много неудачных попыток. Попробуйте снова через 5 минут.";
            }
        }
    } else {
        $error = "Пожалуйста, заполните все поля!";
    }
}
?>

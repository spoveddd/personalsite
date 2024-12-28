<?php
session_start(); // Стартуем сессию
session_destroy(); // Уничтожаем сессию
header('Location: login.php'); // Перенаправляем на страницу логина
exit;

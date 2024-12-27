<?php
// Подключение к базе данных
$db = new SQLite3('feedback.db');

// Получение всех записей
$result = $db->query('SELECT * FROM feedback ORDER BY created_at DESC');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <section class="feedback-section">
        <h2 class="section-title">Отзывы</h2>
        <div class="feedback-list">
            <?php while ($row = $result->fetchArray()) { ?>
                <div class="feedback-item">
                    <div class="feedback-header">
                        <h3 class="feedback-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <?php if (!empty($row['company'])) { ?>
                            <span class="feedback-company"><?php echo "(" . htmlspecialchars($row['company']) . ")"; ?></span>
                        <?php } ?>
                        <span class="feedback-email"><?php echo "(" . htmlspecialchars($row['email']) . ")"; ?></span>
                    </div>
                    <p class="feedback-message"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                    <small class="feedback-date"><?php echo "Оставлено: " . $row['created_at']; ?></small>
                </div>
                <hr>
            <?php } ?>
        </div>
    </section>
</body>
</html>

<?php
  require_once 'function.php';
  if (!hasRole('guest')) {
    http_response_code(403);
    die('Доступ запрещен');
  }
  $list = getData();
  if (count($list) === 0){
    echo '<h1>Тесты не найдены</h1>';
    die;
  }
  $url = $_SERVER['REQUEST_URI'];
  $url = str_replace('list', 'test', $url);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="assets/style.css">
  <title>Список тестов</title>
</head>
<body>
  <div id="fullscreen_bg" class="fullscreen_bg"/>
  <h1>Список тестов:</h1>
  <?php foreach ($list as $key => $value): ?>
    <p><a href="<?= $url . '?list=' . $value['number'] ?>" target="_blank"><?= $value['number'] . ') ' . $value['question'] ?></a></p>
  <?php endforeach;?>
  <?php if (hasRole('admin')) { ?>
    <h1><a href="admin.php" target="_blank">Добавить тест</a></h1>
    <h1><a href="delete.php" target="_blank">Удалить тест</a></h1>
  <?php } ?>
  <a href="logout.php">Выход</a><br/>
</body>
</html>

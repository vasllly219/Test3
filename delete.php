<?php
  require_once 'function.php';
  if (!hasRole('admin')) {
    http_response_code(403);
    die('Доступ запрещен');
  }
  $list = getData();
  if (count($list) === 0){
    echo '<h1>Тесты не найдены</h1>';
    die;
  }
  $errors = [];
  $inputData = getInputValuesDel([]);
  if(isPOST()) {
    $inputData = getInputValuesDel(getParam('Data'));
    $errors = validateData($inputData);
    if (empty($errors) && delData($inputData['number'])) {
      header('location: list.php');
    } else {
      if (empty($errors)) {
        $errors[] = 'При сохранении произошла ошибка';
      }
    }
  }
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
    <p><a><?= $value['number'] . ') ' . $value['question'] ?></a></p>
  <?php endforeach;?>
  <form method="POST">
    <label for="number">Номер теста, который надо удалить: </label>
    <input name="Data[number]" value="<?= $inputData['number'] ?>" id="number">
    <button type="submit">Удалить</button>
  </form>
  <h1><a href="admin.php" target="_blank">Добавить тест</a></h1>
  <h1><a href="list.php" target="_blank">Тесты</a></h1>
  <a href="logout.php">Выход</a><br/>
</body>
</html>

<?php
  require_once 'function.php';
  if (!hasRole('admin')) {
    http_response_code(403);
    die('Доступ запрещен');
  }
  $errors = [];
  $inputData = getInputValues([]);
  if(isPOST()) {
    $inputData = getInputValues(getParam('Data'));
    $errors = validateData($inputData);
    if (empty($errors) && setData($inputData)) {
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
  <title>Админка</title>
</head>
<body>
  <div id="fullscreen_bg" class="fullscreen_bg"/>
  <h1>Поле ввода тестов:</h1>
  <form method="POST">
    <label for="question">Вопрос: </label>
    <input name="Data[question]" value="<?= $inputData['question'] ?>" id="question"><br />

    <label for="answer">Ответ: </label>
    <input name="Data[answer]" value="<?= $inputData['answer'] ?>" id="answer">
    </br>
    <button type="submit" >Отправить</button>
  </form>
  </br>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
      <li><?= getLabel($field) ?> - <?= $error ?></li>
    <?php endforeach; ?>
    <h1><a href="list.php" target="_blank">Тесты</a></h1>
    <a href="logout.php">Выход</a><br/>
  </ul>
</body>
</html>

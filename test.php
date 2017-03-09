<?php
  require_once 'function.php';
  if (!hasRole('guest')) {
    http_response_code(403);
    die('Доступ запрещен');
  }
  $list = getData();
  $test = findTest($list);
  $title = getTitle($test);
  $errors = [];
  $inputData = getInputValuesTest([]);
  if(isPOST()) {
    $inputData = getInputValuesTest(getParam('Data'));
    $errors = validateData($inputData);
    if (empty($errors) && validAnswer($inputData, $test['answer'])) {
      echo '<img src="certificate.php">';
      die;
    } else {
      if (empty($errors)) {
        $errors[] = 'Не верно';
      }
    }
  }
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="assets/style.css">
  <title>Тест</title>
</head>
<body>
  <div id="fullscreen_bg" class="fullscreen_bg"/>
  <h1><?= $title ?></h1>
  <p><?= $test['question'] ?></p>
  <form method="POST">
    <label for="answer">Ответ: </label>
    <input name="Data[answer]" value="<?= $inputData['answer'] ?>" id="answer"><br />
    <button type="submit" >Отправить</button>
  </form>
  </br>
  <ul>
    <?php foreach ($errors as $field => $error): ?>
      <li><?= getLabel($field) . $error ?></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>

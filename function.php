<?php
session_start();
define('FILE_DATA', __DIR__ . '/tests.json');
define('FILE_CERTIFICATES', __DIR__ . '/certificates.json');
define('FILE_AUTH', __DIR__ . '/auth.json');
define('FILE_ERRORS', __DIR__ . '/error.json');
define('CAPTCHA_FILE', __DIR__ . '/captcha.json');
date_default_timezone_set('UTC');

function auth($login, $password)
{
    $userData = getUser($login);
    $hash = hashPassword($login, $password);
    if (!$userData || $userData['password'] != $hash) {
        return false;
    }
    $_SESSION['user'] = $userData;
    return true;
}

function getUser($login)
{
    $filePath = FILE_AUTH;
    if (!$filePath) {
        return false;
    }
    $jsonRaw = file_get_contents($filePath);
    $userDataList = json_decode($jsonRaw, true);
    if (!$userDataList) {
        return false;
    }
    foreach ($userDataList as $user) {
        if ($user['login'] == $login) {
            return $user;
        }
    }
    return false;
}

function hashPassword($login, $password)
{
    return md5($login . ':::' . $password);
}

function authGuest($login, $password){
  if ($password !== ''){
    return false;
  }
  if ($userData = getUser($login)){
    $_SESSION['user'] = $userData;
    return false;
  } else {
    $_SESSION['user'] = setUser($login);
    return true;
  }
}

function setUser($login){
  $data = getData(FILE_AUTH);
  $out = [
    "id" => count($data) + 1,
    "name" => $login,
    "roles" => ["guest"],
    "login" => $login,
    "password" => hashPassword($login, '')
  ];
  $data[] = $out;
  file_put_contents(FILE_AUTH, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  return $out;
}

function hasRole($role)
{
  if (!isset($_SESSION['user']['roles'])){
    return false;
  }
  if (in_array($role, $_SESSION['user']['roles'])) {
    return true;
  }
  return false;
}

function logout()
{
    session_destroy();
}

function setError($code){
  $data = getData(FILE_ERRORS);
  $ip = getClientIp();
  if ($code === 0){
    $data[$ip] = 0;
    file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return 0;
  }
  if ($code === 8){
    $data['blocklist'][$ip] = time();
    $data[$ip] = 9;
    file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    return 9;
  }
  foreach ($data as $key => $value) {
    if ($key === $ip){
      $data[$ip] = $value + 1;
      file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      return $value;
    }
  }
  $data[$ip] = 1;
  file_put_contents(FILE_ERRORS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
  return 1;
}

function getFlug(){
  $data = getData(FILE_ERRORS);
  $ip = getClientIp();
  if (isset($data[$ip])){
    return $data[$ip];
  }
  return 0;
}

function getTimeBlock(){
  $data = getData(FILE_ERRORS);
  $ip = getClientIp();
  foreach ($data['blocklist'] as $key => $value) {
    if ($key === $ip){
      return $value + 3600 - time();
    }
  }
}

//функции к admin.php

function getInputValues($inputData)
{
  $defaultInputData = [
    'number' => '0',
    'question' => '',
    'answer' => ''
  ];
  return array_merge($defaultInputData, $inputData);
}

function isPOST()
{
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function getParam($name, $defaultValue = null)
{
  return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $defaultValue;
}

function validateData($inputData)//fixme
{
  $errors = [];
  foreach ($inputData as $item => $value) {
    if($value === ''){
      $message = 'не должно быть пустым';
      $errors[$item] = $message;
    }
  }
return $errors;
}

function setData($inputData)
{
  $data = getData();
  $inputData['number'] = count($data) + 1;
  $data[] = $inputData;
  if(file_put_contents(FILE_DATA, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
    return true;
  } else {
    return false;
  }
}

function getData($fileName = FILE_DATA)
{
  $data = [];
  if (file_exists($fileName)) {
    $data = json_decode(file_get_contents($fileName), true);
    if (!$data) {
      return [];
    }
  }
  return $data;
}

function getLabel($name)
{
  $labels =  [
    'question' => 'Вопрос - ',
    'answer' => 'Ответ - ',
    'name' => 'Ваше имя - ',
    '0' => ''
  ];
  return isset($labels[$name]) ? $labels[$name] : $name;
}

//функции к test.php

function getInputValuesTest($inputData)
{
  $defaultInputData = [
    'answer' => ''
  ];
  return array_merge($defaultInputData, $inputData);
}

function findTest($list){
  if (!isset($list) || !isset($_GET['list'])){
    return false;
  }
  foreach ($list as $value) {
    if ((string)$value['number'] === $_GET['list']){
      return $value;
    }
  }
}

function getTitle($test){
  if (is_array($test)){
    return 'Тест №' . $test['number'];
  } else{
    header("HTTP/1.0 404 Not Found");
    echo '<h1>Тест не найден</h1><p>404 Not Found</p>';
    die;
  }
}

function getOutputs($post, $answer){
  $output = '';
  if ($post['answer'] !== '' && $post['name'] !== ''){
    $output = validAnswer($answer, $post);
  }
  return $output;
}

function validAnswer($post, $answerTest){
  $answerUser = $post['answer'];
  if ($answerTest === $answerUser){
    setCertificateName();
    return true;
  }
  return false;
}

function setCertificateName(){
  $data = getData(FILE_CERTIFICATES);
  $ip = getClientIp();
  $data[] = [
    $ip => $_SESSION['user']['name']
  ];
  file_put_contents(FILE_CERTIFICATES, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function getClientIp()
{
    return $_SERVER['REMOTE_ADDR'];
}

//функции к certificate.php

function getName(){
  return $_SESSION['user']['name'];
}

function generateCertificate($name){
  $im = imagecreatetruecolor(1300, 920);
  // RGB
  $backColor = imagecolorallocate($im, 255, 224, 221);
  $textColor = imagecolorallocate($im, 129, 15, 90);
  $fontFile = __DIR__ . '/assets/FONT.TTF';
  $imBox = imagecreatefrompng(__DIR__ . '/assets/present.png');
  imagefill($im, 0, 0, $backColor);
  imagecopy($im, $imBox, 0, 0, 0, 0, 1300, 920);
  imagettftext($im, 25, 0, 600, 430, $textColor, $fontFile, $name);
  imagettftext($im, 25, 0, 230, 727, $textColor, $fontFile, date("F j, Y"));
  imagettftext($im, 25, 0, 1000, 727, $textColor, $fontFile, ';)');
  imagepng($im);
  imagedestroy($im);
}

//функции к captcha.php

function generateCaptchaText($length = 6)
{
    $symbols = '12345678890qwertyuiopasdfghjklzxcvbnm';
    $result = [];
    for($i = 0; $i < $length; $i++) {
        $result[$i] = $symbols[mt_rand(0, strlen($symbols) - 1)];
    }
    return implode('', $result);
}

function saveCaptcha($code)
{
    $ip = getClientIp();
    $data = getCaptchaCodes();
    $data[$ip] = $code;
    file_put_contents(CAPTCHA_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function getCaptchaCodes()
{
    if (!file_exists(CAPTCHA_FILE)) {
        return [];
    }
    $data = file_get_contents(CAPTCHA_FILE);
    $data = json_decode($data, true);
    if (!$data) {
        $data = [];
    }
    return $data;
}

function renderCaptcha($code)
{
    $im = imagecreatetruecolor(320, 240);
    // RGB
    $backColor = imagecolorallocate($im, 255, 224, 221);
    $textColor = imagecolorallocate($im, 129, 15, 90);
    $fontFile = __DIR__ . '/assets/FONT.TTF';
    $imBox = imagecreatefrompng(__DIR__ . '/assets/captcha.png');
    imagefill($im, 0, 0, $backColor);
    imagecopy($im, $imBox, 0, 0, 0, 0, 320, 240);
    imagettftext($im, 25, 0, 30, 30, $textColor, $fontFile, $code);
    imagepng($im);
    imagedestroy($im);
}

function checkCaptcha($code) {
    $ip = getClientIp();
    $codes = getCaptchaCodes();
    if(isset($codes[$ip]) && strcmp($codes[$ip], $code) === 0) {
        return true;
    } else {
        return false;
    }
}

//функции к delete.php

function getInputValuesDel($inputData)
{
  $defaultInputData = [
    'number' => ''
  ];
  return array_merge($defaultInputData, $inputData);
}


function delData($number){
  $data = getData();
  foreach ($data as $key => $value) {
    if($value['number'] === (int)$number){
      unset($data[$key]);
      $data = refuktoringData($data);
      file_put_contents(FILE_DATA, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
      return true;
    }
  }
  return false;
}

function refuktoringData($data){
  $count = 1;
  $reData = [];
  foreach ($data as $key => $value) {
    $value['number'] = $count;
    $reData[$count] = $value;
    $count++;
  }
  return $reData;
}

<?php
require_once 'function.php';
if (!hasRole('guest')) {
  http_response_code(403);
  die('Доступ запрещен');
}
header('Content-Type: image/png');
$name = getName();
generateCertificate($name);

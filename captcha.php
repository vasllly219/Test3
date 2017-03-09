<?php
require_once 'function.php';
header('Content-Type: image/png');
$code = generateCaptchaText(3);
saveCaptcha($code);
renderCaptcha($code);

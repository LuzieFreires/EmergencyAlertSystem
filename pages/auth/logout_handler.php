<?php
require_once '../../config.php';

$auth = Auth::getInstance();
$auth->logout();

header('Location: ../login.php');
exit();
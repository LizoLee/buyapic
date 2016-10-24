<?php
//Выход из аккаунта=============================================================

include_once 'buyapic_header.php';

session_unset();
unset($_GET);
unset($_POST);
setcookie('id', NULL);
setcookie('PHPSESSID', NULL);
header('Location: buyapic_index.php');
?>

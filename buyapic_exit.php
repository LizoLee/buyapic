<?php
//Выход из аккаунта=============================================================

error_reporting(-1);
if ( !session_id() ) { session_start(); }

session_unset();
unset($_GET);
unset($_POST);
setcookie('id', NULL);
setcookie('PHPSESSID', NULL);
header('Location: buyapic_index.php');
?>

<?php
//Выход из аккаунта=============================================================

include_once 'buyapic_header.php';

session_unset();
unset($_GET);
unset($_POST);

$tmpFolder = 'uploads/tmp/' . $_COOKIE['id'];
if ( is_dir($tmpFolder) ) {
    include_once 'buyapic_functions.php';
    deleteFolder($tmpFolder);
}
setcookie('id', NULL);
setcookie('PHPSESSID', NULL);
header('Location: buyapic_index.php');
?>

<?php
//Логика отображения страницы (только перенаправляет на другие страницы)========

include_once 'buyapic_header.php';

if( !isset($_GET['action']) ) {
    header('Location: buyapic_index.php?action=main');
}

else {
    switch ($_GET['action'])
    {
        case 'main':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
            } else {
                $_SESSION['pageInfo'] = $dbConnectionObject->getUserInfoDB($_COOKIE['id']);
            }
            unset($_GET['action']);
            include 'buyapic_main.html';
            break;
        case 'authorization':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_authorization.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_main.html';
            }
            break;
        case 'registration':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_registration.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_main.html';
            }
            break;
        case 'show_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
                include 'buyapic_userdetails.html';
            }
            break;
        case 'config_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
            include 'buyapic_config_userdetails.html';
            }
            break;
        case 'add_picture':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
            include 'buyapic_add_picture.html';
            }
            break;
        case 'show_my_pictures':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_main.html';
            } else {
                unset($_GET['action']);
            include 'buyapic_my_pictures.html';
            }
            break;
    }
}
?>
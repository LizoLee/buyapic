<?php
//Логика отображения страницы (только перенаправляет на другие страницы)========

error_reporting(-1);
if ( !session_id() ) {
    session_start();
}

//Если пользователь уже авторизовался
if( isset($_SESSION['authorized']) )
{
    include_once 'buyapic_db.php';
    $db = new BuyAPic;
    $_SESSION['pageInfo'] = $db->getUserInfoDB($_COOKIE['id']);
    header('Location: buyapic_main.html');
}
//Если  возникла ошибка
else if ( isset($_SESSION['error']) ) 
{
    //Ошибка авторизации
    if ( $_SESSION['error']['block'] == 'authorization' ) {
        var_dump($_SESSION);
    header('Location: buyapic_authorization.html');
    }
    
    //Ошибка при регистрации
    if ( $_SESSION['error']['block'] == 'registration' ) {
    header('Location: buyapic_registration.html');
    }
}
else if ( isset($_GET['action']) ) 
{
    //Регистрация
    if ( $_GET['action'] == 'registration' ) {
    header('Location: buyapic_registration.html');
    }
    unset($_GET['action']);
}
//Если пользователь не авторизовался или вышел из аккаунта
else
{
    $_SESSION['pageInfo'] = [ 'userName'=>'anonim' ];
    header('Location: buyapic_main.html');
    echo '!!!!!!!!!!anonim!!!!!!!!!!!!';
}
?>
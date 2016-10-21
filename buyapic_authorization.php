<?php
//Логика приложения. Авторизация================================================

error_reporting(-1);
if ( !session_id() ) { session_start(); }

//var_dump($_POST);
if( isset($_POST['email']) && isset($_POST['password']) )
{
    //Получаем id пользователя и хеш пароля из БД (если имя есть в БД)
    include_once 'buyapic_db.php';
    $authorization = new BuyAPic;
    if( $id_hash = $authorization->getAuthorizationDataDB ($_POST['email']) )
    {
        include_once 'buyapic_functions.php';
        //Проверка пароля
        if ( check_password($id_hash['hash'], $_POST['password']) ) {
            setcookie("id", $id_hash['userid']);
            $_SESSION['authorized'] = TRUE;
            unset($_POST);
            header('Location: buyapic_index.php');
//            include 'buyapic_index.php';
        } else {
            $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Введен неверный пароль для '.$_POST['email'] ];
            unset($_POST);
            header('Location: buyapic_index.php');
        }
    } else {
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Пользователь с email '.$_POST['email'].' не найден!' ];
        unset($_POST);
        header('Location: buyapic_index.php');
    }
}

?>


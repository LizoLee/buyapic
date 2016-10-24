<?php
//Логика приложения. Авторизация================================================

include_once 'buyapic_header.php';

if( isset($_POST['email']) && isset($_POST['password']) )
{
    //Получаем id пользователя и хеш пароля из БД (если имя есть в БД)
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Введен некорректный email' ];
        unset($_POST);
        header('Location: buyapic_index.php?action=authorization');
    }
    else if( $id_hash = $dbConnectionObject->getAuthorizationDataDB ($_POST['email']) )
    {
        include_once 'buyapic_functions.php';
        //Проверка пароля
        if ( check_password($id_hash['hash'], $_POST['password']) ) {
            setcookie("id", $id_hash['userid']);
            $_SESSION['authorized'] = TRUE;
            unset($_POST);
            header('Location: buyapic_index.php?action=main');
//            include 'buyapic_index.php';
        } else {
            $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Введен неверный пароль для '.$_POST['email'] ];
            unset($_POST);
            header('Location: buyapic_index.php?action=authorization');
        }
    } else {
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Пользователь с email '.$_POST['email'].' не найден!' ];
        unset($_POST);
        header('Location: buyapic_index.php?action=authorization');
    }
}
?>


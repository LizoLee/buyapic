<?php
//Логика приложения. Авторизация================================================

include_once 'buyapic_header.php';

if( isset($_POST['email']) && isset($_POST['password']) )
{
    //Проверяем корректность email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
    {
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Введен некорректный email' ];
        header('Location: buyapic_index.php?action=authorization');
    }
    //Получаем id пользователя и хеш пароля из БД (если имя есть в БД)
    else if( $id_hash = $dbConnectionObject->getAuthorizationDataDB ($_POST['email']) )
    {
        include_once 'buyapic_functions.php';
        //Проверка пароля
        if ( check_password($id_hash['hash'], $_POST['password']) ) {
            setcookie("id", $id_hash['userId']);
            $_SESSION['authorized'] = TRUE;
            header('Location: buyapic_index.php');
        } else {
            //Если хеш введенного пароля не совпал с хешем из бд
            $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Введен неверный пароль для '.$_POST['email'] ];
            header('Location: buyapic_index.php?action=authorization');
        }
    } else {
        //Если в БД не найден такой email (getAuthorizationDataDB вернула NULL)
        $_SESSION['error'] = [ 'block'=>'authorization', 
                'message'=>'Пользователь с email '.$_POST['email'].' не найден!' ];
        header('Location: buyapic_index.php?action=authorization');
    }
}
?>


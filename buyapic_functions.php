<?php

//Проверка пароля
function check_password ( $hash, $password ) {  
    // hash: $2a$10$0s1s2s3s4s5s6s7s8s9s0sh0h1h2h3h4h5h6h7h8h9h0h1h2h3h4h,
    // где $2a$10$ - алгоритм шифрования, и "сила замедления" для crypt(),
    // 0s1s2s3s4s5s6s7s8s9s0s - 22 символа соли,
    // h0h1h2h3h4h5h6h7h8h9h0h1h2h3h4h - результат шифрования пароля
    // 
    // первые 29 символов хеша, включающие алгоритм, «силу замедления» и 
    // оригинальную «соль» поместим в переменную  $full_salt
    $full_salt = substr ( $hash, 0, 29 );
    // выполним хеш-функцию для переменной $password  
    $new_hash = crypt ( $password, $full_salt );   
    // возвращаем результат («истина» или «ложь»)  
    return ( $hash == $new_hash );
}

//Создание хеша для заданного пароля
function makeHash ($password) {
    $salt = substr(sha1(mt_rand()),0,22);
    return crypt ( $password, '$2a$10$'.$salt );
}

//Проверка загружаемой картинки
function checkUploadPicture ( string $name )
{
    if ( !($_FILES[$name]['type'] == 'image/jpeg') ) {
        return 'Картинка должна быть в формате jpeg';
    }
    return NULL;
}

//Освобождает и удаляет папку
function deleteFolder ( string $folder )
{
    $files = scandir($folder);
    array_shift($files);
    array_shift($files);
    foreach($files as $file) {
        unlink( $folder . '/' . $file );
    }
    rmdir ($folder);
    return NULL;
}

?>
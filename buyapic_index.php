<?php
//Логика отображения страницы (только перенаправляет на другие страницы)========

include_once 'buyapic_header.php';
if( !isset($_GET['action']) ) {
    header('Location: buyapic_index.php?action=main&page=1');
}

else {
    switch ($_GET['action'])
    {
        case 'main':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            } else {
                $_SESSION['userInfo'] = $dbConnectionObject->getUserInfoDB($_COOKIE['id']);
            }
            
            $picturesAmount = $dbConnectionObject->countShownPicturesDB (); //всего картинок
            $pageNumber = isset($_GET['page'])?$_GET['page']:1;        //номер страницы
            $picturesOnPage = isset($_GET['onpage'])?$_GET['onpage']:5;   //картинок выводить на странице
            
            $lastPageAmount = $picturesAmount % $picturesOnPage;             //картинок на последней странице
            $pagesAmount = ( $lastPageAmount==0 ) ? 
                            intdiv ( $picturesAmount, $picturesOnPage ) :
                            intdiv ( $picturesAmount, $picturesOnPage ) + 1; //количество страниц

            //Если последняя страница
            if( $pageNumber == $pagesAmount ) {
                $_SESSION['pictureList'] = $dbConnectionObject->
                    getShownPicturesDB( $picturesOnPage*($pageNumber-1), 
                            ($lastPageAmount==0) ? $picturesOnPage : $lastPageAmount );
            } else {
                $_SESSION['pictureList'] = $dbConnectionObject->
                    getShownPicturesDB( $picturesOnPage*($pageNumber-1), $picturesOnPage );
            }
            $_SESSION['pagesAmount'] = $pagesAmount;
            
            unset($_GET['action']);
            include 'buyapic_main.html';
            break;
        case 'authorization':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_authorization.html';
            } else {
                header('Location: buyapic_index.php?action=main&page=1');
            }
            break;
        case 'registration':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
                unset($_GET['action']);
                include 'buyapic_registration.html';
            } else {
                header('Location: buyapic_index.php?action=main&page=1');
            }
            break;
        case 'show_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            }
            $_SESSION['pageInfo'] = $dbConnectionObject->getUserInfoDB($_GET['id']);
            unset($_GET['action']);
            include 'buyapic_userdetails.html';
            break;
        case 'config_userdetails':
            if (!isset($_SESSION['authorized']) ) {
                header('Location: buyapic_index.php?action=main&page=1');
            } else {
                unset($_GET['action']);
                include 'buyapic_config_userdetails.html';
            }
            break;
        case 'add_picture':
            if (!isset($_SESSION['authorized']) ) {
                header('Location: buyapic_index.php?action=main&page=1');
            } else {
                unset($_GET['action']);
                if ( isset($_SESSION['pictureInfo']['pictureId']) ) {
                    unset($_SESSION['pictureInfo']);
                }
                $_SESSION['pictureInfo']['show'] = 'add';
                include 'buyapic_config_picture.html';
            }
            break;
        case 'show_my_pictures':
            if (!isset($_SESSION['authorized']) ) {
                header('Location: buyapic_index.php?action=main&page=1');
            } else {
                $_SESSION['pictureList'] = $dbConnectionObject->
                        getUserPicturesDB ($_SESSION['userInfo']['userId']);
                unset($_GET['action']);
                include 'buyapic_my_pictures.html';
            }
            break;
        case 'view_picture':
            if (!isset($_SESSION['authorized']) ) {
                $_SESSION['userInfo'] = [ 'userId'=>'anonim' ];
            }
            $_SESSION['pictureInfo'] = $dbConnectionObject->getPictureInfoDB ($_GET['id']);
            unset($_GET['action']);
            include 'buyapic_one_picture.html';
            break;
        case 'config_picture':
            if (!isset($_SESSION['authorized']) ) {
                header('Location: buyapic_index.php?action=main&page=1');
            } else {
                if ( isset($_GET['id']) ) {
                    $_SESSION['pictureInfo'] = $dbConnectionObject
                            ->getPictureInfoDB ($_GET['id']);
                } else {
                    $_SESSION['pictureInfo'] = $dbConnectionObject
                            ->getPictureInfoDB ($_SESSION['pictureInfo']['pictureId']);
                }
                $_SESSION['pictureInfo']['oldPreviewLink'] = $_SESSION['pictureInfo']['previewLink'];
                $_SESSION['pictureInfo']['oldHDLink'] = $_SESSION['pictureInfo']['hdLink'];
                unset($_GET['action']);
                $_SESSION['pictureInfo']['show'] = 'change';
                include 'buyapic_config_picture.html';
            }
            break;
        case 'show_users':
            if ( isset($_SESSION['userInfo']['accessLevel']) ) {
                if ( $_SESSION['userInfo']['accessLevel'] == 'Moderator' ) {
                    $artistsAmount = $dbConnectionObject->countArtistsDB (); //всего художников
                    $pageNumber = isset($_GET['page'])?$_GET['page']:1;        //номер страницы
                    $artistsOnPage = isset($_GET['onpage'])?$_GET['onpage']:20;   //выводить на странице

                    $lastPageAmount = $artistsAmount % $artistsOnPage;             //на последней странице
                    $pagesAmount = ( $lastPageAmount==0) ? 
                                    intdiv ( $artistsAmount, $artistsOnPage ) :
                                    intdiv ( $artistsAmount, $artistsOnPage )+ 1; //количество страниц

                    //Если последняя страница
                    if( $pageNumber == $pagesAmount ) {
                        $_SESSION['artistList'] = $dbConnectionObject->
                            getArtistsDB( $artistsOnPage*($pageNumber-1), 
                                    ($lastPageAmount==0) ? $artistsOnPage : $lastPageAmount );
                    } else {
                        $_SESSION['artistList'] = $dbConnectionObject->
                            getArtistsDB( $artistsOnPage*($pageNumber-1), $artistsOnPage );
                    }
                    $_SESSION['pagesAmount'] = $pagesAmount;

                    unset($_GET['action']);
                    include 'buyapic_moderator_artists.html';
                } else {
                    header('Location: buyapic_index.php?action=main&page=1');
                }
            } else {
                header('Location: buyapic_index.php?action=main&page=1');
            }
            break;
        case 'show_pictures':
            if ( isset($_SESSION['userInfo']['accessLevel']) ) {
                if ( $_SESSION['userInfo']['accessLevel'] == 'Moderator' ) {
                    $picturesAmount = $dbConnectionObject->countPicturesDB (); //всего картинок
                    $pageNumber = isset($_GET['page'])?$_GET['page']:1;        //номер страницы
                    $picturesOnPage = isset($_GET['onpage'])?$_GET['onpage']:10;   //картинок выводить на странице

                    $lastPageAmount = $picturesAmount % $picturesOnPage;           //картинок на последней странице
                    $pagesAmount = ( $lastPageAmount==0 ) ? 
                                intdiv ( $picturesAmount, $picturesOnPage ) :
                                intdiv ( $picturesAmount, $picturesOnPage ) + 1; //количество страниц

                    //Если последняя страница
                    if( $pageNumber == $pagesAmount ) {
                        $_SESSION['pictureList'] = $dbConnectionObject->
                            getAllPicturesDB( $picturesOnPage*($pageNumber-1), 
                                    ($lastPageAmount==0) ? $picturesOnPage : $lastPageAmount );
                    } else {
                        $_SESSION['pictureList'] = $dbConnectionObject->
                            getAllPicturesDB( $picturesOnPage*($pageNumber-1), $picturesOnPage );
                    }
                    $_SESSION['pagesAmount'] = $pagesAmount;

                    unset($_GET['action']);
                    include 'buyapic_moderator_pictures.html';
                } else {
                    header('Location: buyapic_index.php?action=main&page=1');
                }
            } else {
                header('Location: buyapic_index.php?action=main&page=1');
            }
            break;
        case 'show_requests':
            if ( isset($_SESSION['userInfo']['accessLevel']) ) {
                if ( $_SESSION['userInfo']['accessLevel'] == 'Moderator' ) {
                    $requestsAmount = $dbConnectionObject->countRequestsDB (); //всего заявок
                    $pageNumber = isset($_GET['page'])?$_GET['page']:1;        //номер страницы
                    $requestsOnPage = isset($_GET['onpage'])?$_GET['onpage']:20;   //выводить на странице

                    $lastPageAmount = $requestsAmount % $requestsOnPage;             //на последней странице
                    $pagesAmount = ($lastPageAmount==0) ?
                                intdiv ( $requestsAmount, $requestsOnPage ) :
                                intdiv ( $requestsAmount, $requestsOnPage )+ 1; //количество страниц

                    //Если последняя страница
                    if( $pageNumber == $pagesAmount ) {
                        $_SESSION['requestList'] = $dbConnectionObject->
                            getRequestsDB( $requestsOnPage*($pageNumber-1), 
                                    ($lastPageAmount==0) ? $requestsOnPage : $lastPageAmount );
                    } else {
                        $_SESSION['requestList'] = $dbConnectionObject->
                            getRequestsDB( $requestsOnPage*($pageNumber-1), $requestsOnPage );
                    }
                    $_SESSION['pagesAmount'] = $pagesAmount;

                    unset($_GET['action']);
                    include 'buyapic_moderator_requests.html';
                } else {
                    header('Location: buyapic_index.php?action=main&page=1');
                }
            } else {
                header('Location: buyapic_index.php?action=main&page=1');
            }
            break;
        default:
            header('Location: buyapic_index.php?action=main&page=1');
    }
}
?>
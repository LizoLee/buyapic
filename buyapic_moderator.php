<?php
//Логика работы модератора======================================================

include_once 'buyapic_header.php';

if ( isset($_POST['activateArtist']) ) {
    $dbConnectionObject->changeUserDB ('ArtistStatus', $_POST['artistId'], 'Active');
    header('Location: '.$_SERVER["HTTP_REFERER"]);
} 
else if ( isset($_POST['blockArtist']) ) {
    $dbConnectionObject->changeUserDB ('ArtistStatus', $_POST['artistId'], 'Blocked');
    header('Location: '.$_SERVER["HTTP_REFERER"]);
}
else if ( isset($_POST['requestPaid']) ) {
    $dbConnectionObject->requestPaidDB ($_POST['requestId']);
    header('Location: '.$_SERVER["HTTP_REFERER"]);
}
?>
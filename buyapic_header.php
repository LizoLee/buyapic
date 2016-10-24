<?php
error_reporting(-1);
if ( !session_id() ) {
    session_start();
}
include_once 'buyapic_db.php';
$dbConnectionObject = new BuyAPicDataBaseConnection;

?>


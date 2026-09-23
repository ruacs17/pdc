<?php if(!isset($_SESSION)) session_start();
if( !isset($_SESSION['username']) || $_SESSION['role_id']!="14" ){
    header("Location: ../");
    die();
}
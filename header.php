<?php
$db = new PDO('mysql:host=crmtech.local;dbname=local', 'root', 'root');

$flg_success_status = FLAG_DEFAULT;
$flg_post_status = FLAG_DEFAULT;
$flg_required_field_status = FLAG_DEFAULT;
$flg_invalid_field_status = FLAG_DEFAULT;

$array_required_field = array();
$array_invalid_field = array();

ob_start();
?>

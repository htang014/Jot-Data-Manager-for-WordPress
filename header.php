<?php
// if (!defined('ABSPATH')) {
//     header('HTTP/1.0 403 Forbidden');
//     echo 'Direct access not permitted.';
//     exit;
// }

$flg_success_status = FLAG_DEFAULT;
$flg_post_status = FLAG_DEFAULT;
$flg_required_field_status = FLAG_DEFAULT;
$flg_invalid_field_status = FLAG_DEFAULT;

$array_required_field = array();
$array_invalid_field = array();

ob_start();
?>

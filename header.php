<?php
if (!defined('ABSPATH')) {
    header('HTTP/1.0 403 Forbidden');
    echo 'Direct access not permitted.';
    exit;
}

$jotdm_flg_success_status = JOTDM_FLAG_DEFAULT;
$jotdm_flg_post_status = JOTDM_FLAG_DEFAULT;
$jotdm_flg_required_field_status = JOTDM_FLAG_DEFAULT;
$jotdm_flg_invalid_field_status = JOTDM_FLAG_DEFAULT;

$jotdm_array_required_field = array();
$jotdm_array_invalid_field = array();

$jotdm_ajax_nonce = null;

$jotdm_base_files = array(JOTDM_DEFAULT_PROFILE_IMAGE);

ob_start();
?>

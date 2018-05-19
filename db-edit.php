<?php
require_once('includes.php');
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");

$flg_success_status = FLAG_SUCCESS;

if (isset($task) && isset($_POST['menu-id'])){
    // Escape all $_POST again just in case
    foreach ($_POST as &$p){
        $p = ms_escape_string($p);
    }
    // if (empty($_POST['menu-id'])){
    //     $_POST['menu-id'] = 0;
    // }

    //  Input sanitation
    $task = sanitize_text_field($_POST['task']);
    $menu_id = (int) $_POST['menu-id'];

    $ini = parse_ini_file("settings.ini",true);
    $options = $ini[$_POST['menu-id']];
    $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);

    if ( $task=='row-edit' ){

        $fields = $_POST;
        unset($fields['menu-id']);
        unset($fields['task']);

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }

        if (isset($_POST['position'])){
            $pos = (int) $_POST['position'];

            if (isset($_POST['remove-image'])) {
                edit_db_image($options, $pos, $db);
            }
            elseif (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])){
                $image = $_FILES['image'];
                edit_db_image($options, $pos, $db, $image);
            }
            edit_db_entry($options, $pos, $fields, $db);
        }
        else {
            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;
        }
    }
    elseif ( $task=='row-reorder' ){

        if( isset($_POST['move']) &&
            isset($_POST['position'])){

            $move = sanitize_text_field($_POST['move']);
            $pos = (int) $_POST['position'];

            move_db_entry( $options, $pos, $move, $db);
        }
        else {
            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;     
        }
    }
    elseif ( $task=='row-delete' ){

        if (isset($_POST['position'])){
            $pos = (array) $_POST['position'];
            foreach($pos as &$p){
                $p = (int) $p;
                delete_db_entry($options, $p, $db);
            }
 
        }
        else {
            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;  
        }
    }
    elseif ( $task=='row-add' ){

        $flg_success_status = FLAG_SUCCESS;

        $fields = $_POST;
        unset($fields['menu-id']);
        unset($fields['task']);

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }

        $image = (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])) ? $_FILES['image'] : NULL;
        add_db_entry($options, $fields, $db, $image);
    }
    else {

        $flg_post_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;  
    }
}
elseif ( $_POST ){
    $flg_post_status = FLAG_ERROR;
    $flg_success_status = FLAG_ERROR;  
}

// Error handling
if ($flg_success_status == FLAG_SUCCESS){
    header("HTTP/1.1 200 OK");
}
else {
    if ($flg_post_status == FLAG_ERROR){
        header("HTTP/1.0 400 Bad Request");
        echo "Invalid post request";
    }
    if ($flg_required_field_status == FLAG_ERROR){
        header("HTTP/1.0 400 Bad Request");
        echo "The following required fields are missing:";
        foreach ($array_required_field as $msg){
            echo $msg;
        }
    }
    if ($flg_invalid_field_status == FLAG_ERROR){
        header("HTTP/1.0 400 Bad Request");
        foreach ($array_invalid_field as $msg){
            echo $msg;
        }
    }
}
?>

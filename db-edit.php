<?php
require_once('includes.php');
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
?>

<?php
$flg_success_status = FLAG_SUCCESS;
if (isset($_POST['task']) && isset($_POST['menu-id'])){
    foreach ($_POST as &$p){
        $p = ms_escape_string($p);
    }

    $ini = parse_ini_file("settings.ini",true);
    $options = $ini[$_POST['menu-id']];

    if ( $_POST['task']=='image-clear' ){

        if (!isset ($_POST['position'])){

            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;
        }
        else {
            edit_team_db_image($_POST['position']);
        }

    }
    elseif ( $_POST['task']=='row-edit' ){

        $flg_success_status = FLAG_SUCCESS;

        $fields = $_POST;
        unset($fields['menu-id']);
        unset($fields['task']);

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }

        if (!isset($_POST['position'])){

            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;
        }
        else {
            if (isset($_POST['remove-image'])) {
                edit_db_image($options, $_POST['position']);
            }
            elseif (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])){
                edit_db_image($options, $_POST['position'],$_FILES['image']);
            }
            edit_db_entry($options, $_POST['position'], $fields);
        }
    }
    elseif ( $_POST['task']=='row-reorder' ){

        if( !isset($_POST['move']) ||
            !isset($_POST['position'])){

            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;         
        }
        else {
            move_db_entry( $options, $_POST['position'], $_POST['move'] );
        }
    }
    elseif ( $_POST['task']=='row-delete' ){

        if (!isset($_POST['position'])){

            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;   
        }
        else {
            foreach((array) $_POST['position'] as $pos){
                delete_db_entry($options, $pos);
            }
        }
    }
    elseif ( $_POST['task']=='row-add' ){

        $flg_success_status = FLAG_SUCCESS;

        $fields = $_POST;
        unset($fields['menu-id']);
        unset($fields['task']);

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }

        $image = (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])) ? $_FILES['image'] : NULL;
        add_db_entry($options, $fields, $image );
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

if ($flg_success_status == FLAG_SUCCESS){
    header("HTTP/1.1 200 OK");
}
else {
    header("HTTP/1.0 400 Bad Request");
}
?>

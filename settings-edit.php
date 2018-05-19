<?php
require_once('includes.php');
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");

$flg_success_status = FLAG_SUCCESS;

$ini = parse_ini_file("settings.ini",true);
$options = array();

if (isset($_POST['delete'])){
    // Escape all $_POST again just in case
    foreach ($_POST as &$p){
        $p = ms_escape_string($p);
    }

    $delete = sanitize_text_field($_POST['delete']);

    if (isset($_POST['menu-select'])){
        $menu_id = intval($_POST['menu-select']);

        if ($menu_id < 0){
            $flg_post_status = FLAG_ERROR;
            $flg_success_status = FLAG_ERROR;
            goto err;
        }

        unset($ini[$menu_id]);
        $ini = array_values($ini);
        write_ini_file("settings.ini", $ini);
    }
    else {
        $flg_post_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        goto err;
    }
}
else if (isset($_POST['db-host']) &&
    isset($_POST['db-name']) &&
    isset($_POST['db-user']) &&
    isset($_POST['db-pass']) &&
    isset($_POST['table-select'])){

    // Escape all $_POST again just in case
    foreach ($_POST as &$p){
        $p = ms_escape_string($p);
    }

    $db_host = sanitize_text_field($_POST['db-host']);
    $db_name = sanitize_text_field($_POST['db-name']);
    $db_user = sanitize_text_field($_POST['db-user']);
    $db_pass = sanitize_text_field($_POST['db-pass']);
    $db_table = sanitize_text_field($_POST['table-select']);

    if (isset($_POST['menu-title']) &&
        isset($_POST['display-fields']) &&
        isset($_POST['table-id']) &&
        isset($_POST['primary-field'])) {

        $menu_title = sanitize_text_field($_POST['menu-title']);
        $disp_fields = array();
        foreach ($_POST['display-fields'] as $field){
            $disp_fields[] = sanitize_text_field($field);
        }
        $id_field = sanitize_text_field($_POST['table-id']);
        $prim_field = sanitize_text_field($_POST['primary-field']);

        if (isset($_POST['menu-select'])){
            $menu_id = intval($_POST['menu-select']);

            if ($menu_id < 0){
                $flg_post_status = FLAG_ERROR;
                $flg_success_status = FLAG_ERROR;
                goto err;
            }

            unset($ini[$menu_id ]);
            $ini = array_values($ini);
        }

        $options['dbhost'] = $db_host;
        $options['dbname'] = $db_name;
        $options['dbuser'] = $db_user;
        $options['dbpass'] = $db_pass;
        $options['dataTable'] = $db_table;
        $options['name'] = $menu_title;

        $disp_fields = array_diff($disp_fields, array($prim_field));
        $options['displayColumns'] = array_merge(array($prim_field),$disp_fields);
        $options['tableId'] = $id_field;

        if (isset($_POST['image'])){
            $image = boolval($_POST['image']);

            if (isset($_POST['img-url-root']) &&
                isset($_POST['imgsrc'])) {

                $img_url = sanitize_text_field($_POST['img-url-root']);
                $img_field = sanitize_text_field($_POST['imgsrc']);
                
                $img_url = str_replace("\\", "/", $img_url);
                if (substr($img_url, -1) != "/"){
                    $img_url = $img_url."/";
                }
                if ($img_url[0] != "/"){
                    $img_url = "/".$img_url;
                }

                $options['image'] = 1;
                $options['imageUrlRoot'] = $img_url;
                $options['imageSource'] = $img_field;
            }
            else {
                $flg_post_status = FLAG_ERROR;
                $flg_success_status = FLAG_ERROR;
                goto err;
            }
        }
        else {
            $options['image'] = 0;
        }
        
        if (isset($_POST['split'])){
            $split = boolval($_POST['split']);

            if (isset($_POST['split-by'])){
                $split_by = sanitize_text_field($_POST['split-by']);

                $options['split'] = 1;
                $options['splitBy'] = $split_by;
            }
            else {
                $flg_post_status = FLAG_ERROR;
                $flg_success_status = FLAG_ERROR;
                goto err;
            }
        }
        else {
            $options['split'] = 0;
        }

        if (isset($_POST['order'])){
            $order = boolval($_POST['order']);

            if (isset($_POST['order-by'])){
                $order_by = sanitize_text_field($_POST['order-by']);

                $options['order'] = 1;
                $options['orderBy'] = $order_by;
            }
            else {
                $flg_post_status = FLAG_ERROR;
                $flg_success_status = FLAG_ERROR;
                goto err;
            }
        }
        else {
            $options['order'] = 0;
        }
        if (isset($_POST['icon'])){
            $icon = sanitize_text_field($_POST['icon']);
            $options['icon'] = $icon;
        }

        $ini[] = $options;
        write_ini_file("settings.ini", $ini);
        
    }
    else {
        $flg_post_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        goto err;
    }
}
else {
    $flg_post_status = FLAG_ERROR;
    $flg_success_status = FLAG_ERROR;
    goto err;
}

err:
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
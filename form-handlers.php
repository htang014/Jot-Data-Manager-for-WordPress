<?php
// Table management
function row_add_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;

    if (isset($_POST['menu-id'])){

        $fields = $_POST;
        unset($fields['action']);
        unset($fields['menu-id']);  

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }
        $menu_id = intval($_POST['menu-id']);

        $ini = parse_ini_file("settings.ini",true);
        $options = $ini[$menu_id];
        $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);

        $image = (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])) ? $_FILES['image'] : NULL;
        jotdm_add_db_entry($options, $fields, $db, $image);
    }
    else {
        $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
    }
    jotdm_error_status_handler();
    wp_die();
}

function row_edit_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;
    
    if (isset($_POST['menu-id']) &&
        isset($_POST['position'])){

        $fields = $_POST;
        unset($fields['action']);
        unset($fields['menu-id']);
        unset($fields['position']);
        unset($fields['remove-image']);

        foreach ($fields as &$field){
            $field = sanitize_text_field($field);
        }
        $menu_id = intval($_POST['menu-id']);
        $pos = intval($_POST['position']);

        $ini = parse_ini_file("settings.ini",true);
        $options = $ini[$menu_id];
        $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);

        if (isset($_POST['remove-image'])) {
            jotdm_edit_db_image($options, $pos, $db);
        }
        elseif (array_key_exists('image',$_FILES) && file_exists($_FILES['image']['tmp_name'])){
            $image = $_FILES['image'];
            jotdm_edit_db_image($options, $pos, $db, $image);
        }
        jotdm_edit_db_entry($options, $pos, $fields, $db);
    }
    else {
        $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
    }
    jotdm_error_status_handler();
    wp_die();
}

function row_reorder_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;

    if( isset($_POST['menu-id']) &&
        isset($_POST['move']) &&
        isset($_POST['position'])){

        $menu_id = intval($_POST['menu-id']);
        $move = sanitize_text_field($_POST['move']);
        $pos = intval($_POST['position']);

        $ini = parse_ini_file("settings.ini",true);
        $options = $ini[$menu_id];
        $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);

        jotdm_move_db_entry( $options, $pos, $move, $db);
    }
    else {
        $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;     
    }
    jotdm_error_status_handler();
    wp_die();
}

function row_delete_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;

    if (isset($_POST['menu-id']) &&
        isset($_POST['position'])){

        $menu_id = intval($_POST['menu-id']);
        $pos = (array) $_POST['position'];

        $ini = parse_ini_file("settings.ini",true);
        $options = $ini[$menu_id];
        $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);

        foreach($pos as &$p){
            $p = intval($p);
            jotdm_delete_db_entry($options, $p, $db);
        }
    }
    else {
        $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;  
    }
    jotdm_error_status_handler();
    wp_die();
}


// Menu management
function menu_delete_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;

    if (isset($_POST['menu-select'])){
        $ini = parse_ini_file("settings.ini",true);
        $options = array();

        $menu_id = intval($_POST['menu-select']);

        if ($menu_id < 0){
            $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
            $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        }

        unset($ini[$menu_id]);
        $ini = array_values($ini);
        jotdm_write_ini_file("settings.ini", $ini);
    }
    else {
        $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
    }
}

function menu_edit_handler(){
    $jotdm_flg_success_status = JOTDM_FLAG_SUCCESS;

    if (isset($_POST['db-host']) &&
        isset($_POST['db-name']) &&
        isset($_POST['db-user']) &&
        isset($_POST['db-pass']) &&
        isset($_POST['table-select'])){

        $ini = parse_ini_file("settings.ini",true);
        $options = array();

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
                    $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
                    $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
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
                    $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
                    $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
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
                    $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
                    $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
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
                    $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
                    $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
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
            jotdm_write_ini_file("settings.ini", $ini);
            
        }
        else {
            $jotdm_flg_post_status = JOTDM_FLAG_ERROR;
            $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        }
    }
}
?>

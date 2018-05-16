<?php

function ms_escape_string($data) {
    if ( !isset($data) or empty($data) ) return '';
    if ( is_numeric($data) ) return $data;

    $non_displayables = array(
        '/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
        '/%1[0-9a-f]/',             // url encoded 16-31
        '/[\x00-\x08]/',            // 00-08
        '/\x0b/',                   // 11
        '/\x0c/',                   // 12
        '/[\x0e-\x1f]/'             // 14-31
    );
    foreach ( $non_displayables as $regex )
        $data = preg_replace( $regex, '', $data );
    $data = str_replace("'", "''", $data );
    return $data;
}

function upload_image($image, $path){
    global $flg_invalid_field_status;
    global $array_invalid_field;
    global $flg_success_status;

    $image_basename = basename($image["name"]);
    $image_file_type = strtolower(pathinfo($image_basename,PATHINFO_EXTENSION));

    $target_dir = $_SERVER['DOCUMENT_ROOT'] . $path;
    $target_file = uniqid() . "." . $image_file_type;
    $target_file_path = $target_dir . $target_file;

    $check = getimagesize($image["tmp_name"]);
    if($check == false) {
        $flg_invalid_field_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        $array_invalid_field[] = "Image: File is not an image.";
        return null;
    }
    while (file_exists($target_file_path)) {
        $target_file = uniqid() . "." . $image_file_type;
        $target_file_path = $target_dir . $target_file;
    }
    if ($image["size"] > MAX_IMAGE_SIZE) {
        $flg_invalid_field_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        $array_invalid_field[] = "Image: Too large (Maximum size: 4MB).";
        return null;
    }
    if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg") {
        $flg_invalid_field_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        $array_invalid_field[] = "Image: Unsupported file type (jpg, jpeg, and png only).";
        return null;
    }
    if (move_uploaded_file($image["tmp_name"], $target_file_path)) {
        return $target_file;
    } else {
        $flg_invalid_field_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        $array_invalid_field[] = "Image: Problem uploading file.";
        return null;
    }
}

function delete_image($path, $file){
    global $base_files;
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . $file) &&
        !in_array($file,$base_files)){

        unlink($_SERVER['DOCUMENT_ROOT'] . $path . $file);
    }
}

function parse_title($title, $max_count=null){
    global $flg_invalid_field_status;
    global $array_invalid_field;
    global $flg_success_status;
    
    $title_copy = $title;
    $title_copy = str_replace(',', '<br/>', $title_copy, $i);
    if (isset($max_count) && ($i > $max_count-1)){
        $flg_invalid_field_status = FLAG_ERROR;
        $flg_success_status = FLAG_ERROR;
        $array_invalid_field[] = "Too many arguments (Max: ".$max_count.")";

        $title_copy = explode('<br/>', $title_copy);
        $title_copy = array_slice($title_copy,0,$max_count);
        $title_copy = implode('<br/>', $title_copy);

        return $title_copy;
    }
    else {
        return $title_copy;
    }
}

function get_fields_from_table($table){
    global $db;

    $fields = array();
    $statement = $db->prepare("DESCRIBE `".$table."`");
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $fields[] = $row['Field'];
    }
    return $fields;
}

function add_db_entry($options, $fields, $image=null){
    global $db;

    $table = $options['dataTable'];
    $order_field = $options['order'] ? $options['orderBy'] : NULL;
    $image_field = $options['image'] ? $options['imageSource'] : NULL;
    $split_field = $options['split'] ? $options['splitBy'] : NULL;
    $split_value = $fields[$split_field];

    $order = 0;
    if (isset($order_field)){
        if (isset($split_field)){
            $statement=$db->prepare("SELECT * FROM `".$table."` WHERE `".$split_field."`='".$split_value."' ORDER BY `".$order_field."` DESC LIMIT 1");
        }
        else {
            $statement=$db->prepare("SELECT * FROM `".$table."` ORDER BY `".$order_field."` DESC LIMIT 1");  
        }
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        while ( $row = $statement->fetch() ) {
            $order = $row[$order_field] + 1;
        }
    }

    $tfields = $fields;
    $fields_clause = array();
    $values_clause = array();

    if (isset($image_field)){
        $imgsrc = isset($image) ? upload_image($image, $options['imageUrlRoot']) : DEFAULT_PROFILE_IMAGE;
        $tfields[$image_field] = $imgsrc;
    }
    if (isset($order_field)){
        $tfields[$order_field] = $order;
    }
    if (isset($split_field)){
        $tfields[$split_field] = $split_value;
    }

    foreach ($tfields as $field=>&$value){
        $fields_clause[] = '`'.$field.'`';
        $values_clause[] = '\''.$value.'\'';
    }

    $fields_clause = implode(",",$fields_clause);
    $values_clause = implode(",",$values_clause);
    $statement = $db->prepare("INSERT INTO `".$table."` (".$fields_clause.") VALUES (".$values_clause.")");
    $statement->execute();
}

function edit_db_entry($options, $pos, $fields){
    global $db;
    $table = $options['dataTable'];
    $table_id = $options['tableId'];

    if ($options['order'] && $options['split']){
        $order_field = $options['orderBy'];
        $split_field = $options['splitBy'];

        $statement = $db->prepare("SELECT * FROM `".$table."` WHERE `".$table_id."`=".$pos);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        while ( $row = $statement->fetch() ) {
            $curr_split = $row[$split_field];
            $curr_order = $row[$order_field];
        }

        if ( $curr_split != $fields[$split_field] ){
            $statement=$db->prepare("SELECT * FROM `".$table."` WHERE `".$split_field."`='".$fields[$split_field]."' ORDER BY `".$order_field."` DESC LIMIT 1");
            $statement->execute();
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            while ( $row = $statement->fetch() ) {
                $fields[$order_field] = $row[$order_field] + 1;
            }

            $statement = $db->prepare(
                "UPDATE `".$table."` ".
                "SET `".$order_field."`=`".$order_field."`-1 ".
                "WHERE `".$order_field."`>".$curr_order.
                " and `".$split_field."`='".$curr_split."'"
            );
            $statement->execute();
        }
    }

    foreach ($fields as $field=>$value){
        $statement = $db->prepare("UPDATE `".$table."` ".
        "SET `".$field."`='".$value."' ".
        "WHERE `".$table_id."`=".$pos);
        $statement->execute();
    }
}

function move_db_entry($options, $pos, $move){
    global $db;
    $order;

    $table = $options['dataTable'];
    $table_id = $options['tableId'];
    $order_field = $options['order'] ? $options['orderBy'] : NULL;
    $image_field = $options['image'] ? $options['imageSource'] : NULL;
    $split_field = $options['split'] ? $options['splitBy'] : NULL;

    $statement = $db->prepare("SELECT * FROM `".$table."` WHERE `".$table_id."`=".$pos);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $split_value = $row[$split_field];
        $order = $row[$order_field];
    }

    $new_order = ($move=='up') ? $order-1 : $order+1;

    $statement = $db->prepare("UPDATE `".$table."` AS a JOIN `".$table."` AS b ".
    "SET a.`".$order_field."`=".$new_order.", b.`".$order_field."`=".$order." ".
    "WHERE a.`".$table_id."`=".$pos." and b.`".$order_field."`=".$new_order." and b.`".$split_field."`='".$split_value."'");
    $statement->execute();
}

function delete_db_entry($options ,$pos){
    global $db;
    $table = $options['dataTable'];
    $table_id = $options['tableId'];
    $order_field = $options['order'] ? $options['orderBy'] : NULL;
    $image_field = $options['image'] ? $options['imageSource'] : NULL;
    $split_field = $options['split'] ? $options['splitBy'] : NULL;

    $statement = $db->prepare("SELECT * FROM `".$table."` WHERE `".$table_id."`=".$pos);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $split_value = $row[$split_field];
        $order = $row[$order_field];
        $imgsrc = $row[$image_field];
    }

    $statement = $db->prepare("DELETE FROM `".$table."` WHERE `".$table_id."`=".$pos);
    $statement->execute();

    if (isset($order_field)){
        $sql_str = "UPDATE `".$table."` ".
            "SET `".$order_field."`=`".$order_field."`-1 ".
            "WHERE `".$order_field."`>".$order;

        if (isset($split_field)){
            $sql_str .= " and `".$split_field."`='".$split_value."'";
        }
    
        $statement = $db->prepare($sql_str);
        $statement->execute();
    }
    if (isset($image_field)){
        delete_image($options['imageUrlRoot'] ,$imgsrc);
    }
}

function edit_db_image($options, $pos, $image=null){
    global $db;

    $statement = $db->prepare("SELECT * FROM `".$options['dataTable']."` WHERE `".$options['tableId']."`=".$pos);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $imgsrc = $row[$options['imageSource']];
    }
    delete_image($options['imageUrlRoot'] ,$imgsrc);

    if (isset($image)){
        $imgsrc = upload_image($image, $options['imageUrlRoot']);
        $statement = $db->prepare("UPDATE `".$options['dataTable']."` SET `".$options['imageSource']."`='".$imgsrc."' WHERE `".$options['tableId']."`=".$pos);
        $statement->execute();
    }
    else {
        edit_db_entry($options, $pos, array($options['imageSource'] => DEFAULT_PROFILE_IMAGE));
    }
}

?>
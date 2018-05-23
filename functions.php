<?php
// INI Editing Functions
function jotdm_write_ini_file($file, $array = []) {
    // check first argument is string
    if (!is_string($file)) {
        throw new \InvalidArgumentException('Function argument 1 must be a string.');
    }

    // check second argument is array
    if (!is_array($array)) {
        throw new \InvalidArgumentException('Function argument 2 must be an array.');
    }

    // process array
    $data = array();
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $data[] = "[$key]";
            foreach ($val as $skey => $sval) {
                if (is_array($sval)) {
                    foreach ($sval as $_skey => $_sval) {
                        if (is_numeric($_skey)) {
                            $data[] = $skey.'[] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        } else {
                            $data[] = $skey.'['.$_skey.'] = '.(is_numeric($_sval) ? $_sval : (ctype_upper($_sval) ? $_sval : '"'.$_sval.'"'));
                        }
                    }
                } else {
                    $data[] = $skey.' = '.(is_numeric($sval) ? $sval : (ctype_upper($sval) ? $sval : '"'.$sval.'"'));
                }
            }
        } else {
            $data[] = $key.' = '.(is_numeric($val) ? $val : (ctype_upper($val) ? $val : '"'.$val.'"'));
        }
        // empty line
        $data[] = null;
    }

    // open file pointer, init flock options
    $fp = fopen($file, 'w');
    $retries = 0;
    $max_retries = 100;

    if (!$fp) {
        return false;
    }

    // loop until get lock, or reach max retries
    do {
        if ($retries > 0) {
            usleep(rand(1, 5000));
        }
        $retries += 1;
    } while (!flock($fp, LOCK_EX) && $retries <= $max_retries);

    // couldn't get the lock
    if ($retries == $max_retries) {
        return false;
    }

    // got lock, write data
    fwrite($fp, implode(PHP_EOL, $data).PHP_EOL);

    // release lock
    flock($fp, LOCK_UN);
    fclose($fp);

    return true;
}

// Error handling
function jotdm_error_status_handler(){
    if ($jotdm_flg_success_status == JOTDM_FLAG_SUCCESS){
        header("HTTP/1.1 200 OK");
    }
    else {
        if ($jotdm_flg_post_status == JOTDM_FLAG_ERROR){
            header("HTTP/1.0 400 Bad Request");
            echo "Invalid post request";
        }
        if ($jotdm_flg_required_field_status == JOTDM_FLAG_ERROR){
            header("HTTP/1.0 400 Bad Request");
            echo "The following required fields are missing:";
            foreach ($jotdm_array_required_field as $msg){
                echo $msg;
            }
        }
        if ($jotdm_flg_invalid_field_status == JOTDM_FLAG_ERROR){
            header("HTTP/1.0 400 Bad Request");
            foreach ($jotdm_array_invalid_field as $msg){
                echo $msg;
            }
        }
    }
}

// General Functions
function jotdm_get_fields_from_table($table, $db){
    $fields = array();
    $statement = $db->prepare("DESCRIBE `".$table."`");
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $fields[] = $row['Field'];
    }
    return $fields;
}

function jotdm_upload_image($image, $path){
    global $jotdm_flg_invalid_field_status;
    global $jotdm_array_invalid_field;
    global $jotdm_flg_success_status;

    $image_basename = basename($image["name"]);
    $image_file_type = strtolower(pathinfo($image_basename,PATHINFO_EXTENSION));

    $target_dir = $_SERVER['DOCUMENT_ROOT'] . $path;
    $target_file = uniqid() . "." . $image_file_type;
    $target_file_path = $target_dir . $target_file;

    $check = getimagesize($image["tmp_name"]);
    if($check == false) {
        $jotdm_flg_invalid_field_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        $jotdm_array_invalid_field[] = "Image: File is not an image.";
        return null;
    }
    while (file_exists($target_file_path)) {
        $target_file = uniqid() . "." . $image_file_type;
        $target_file_path = $target_dir . $target_file;
    }
    if ($image["size"] > JOTDM_MAX_IMAGE_SIZE) {
        $jotdm_flg_invalid_field_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        $jotdm_array_invalid_field[] = "Image: Too large (Maximum size: 4MB).";
        return null;
    }
    if ($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg") {
        $jotdm_flg_invalid_field_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        $jotdm_array_invalid_field[] = "Image: Unsupported file type (jpg, jpeg, and png only).";
        return null;
    }
    if (move_uploaded_file($image["tmp_name"], $target_file_path)) {
        return $target_file;
    } else {
        $jotdm_flg_invalid_field_status = JOTDM_FLAG_ERROR;
        $jotdm_flg_success_status = JOTDM_FLAG_ERROR;
        $jotdm_array_invalid_field[] = "Image: Problem uploading file.";
        return null;
    }
}

function jotdm_delete_image($path, $file){
    global $jotdm_base_files;
    if (file_exists($_SERVER['DOCUMENT_ROOT'] . $path . $file) &&
        !in_array($file,$jotdm_base_files)){

        unlink($_SERVER['DOCUMENT_ROOT'] . $path . $file);
    }
}

// DB Editing Functions
function jotdm_add_db_entry($options, $fields, &$db, $image=null){
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
        $imgsrc = isset($image) ? jotdm_upload_image($image, $options['imageUrlRoot']) : JOTDM_DEFAULT_PROFILE_IMAGE;
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

    //print_r("INSERT INTO `".$table."` (".$fields_clause.") VALUES (".$values_clause.")");
}

function jotdm_edit_db_entry($options, $pos, $fields, &$db){
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

        if (empty($fields[$split_field])){
            $fields[$split_field] = 0;
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

function jotdm_move_db_entry($options, $pos, $move, &$db){
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

    $sql_str = "UPDATE `".$table."` AS a JOIN `".$table."` AS b ".
    "SET a.`".$order_field."`=".$new_order.", b.`".$order_field."`=".$order." ".
    "WHERE a.`".$table_id."`=".$pos." and b.`".$order_field."`=".$new_order;

    if ($options['split']){
        $sql_str .= " and b.`".$split_field."`='".$split_value."'";
    }

    $statement = $db->prepare($sql_str);
    $statement->execute();
}

function jotdm_delete_db_entry($options ,$pos, &$db){
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
        jotdm_delete_image($options['imageUrlRoot'] ,$imgsrc);
    }
}

function jotdm_edit_db_image($options, $pos, &$db, $image=null){
    $statement = $db->prepare("SELECT * FROM `".$options['dataTable']."` WHERE `".$options['tableId']."`=".$pos);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ( $row = $statement->fetch() ) {
        $imgsrc = $row[$options['imageSource']];
    }
    jotdm_delete_image($options['imageUrlRoot'] ,$imgsrc);

    if (isset($image)){
        $imgsrc = jotdm_upload_image($image, $options['imageUrlRoot']);
        $statement = $db->prepare("UPDATE `".$options['dataTable']."` SET `".$options['imageSource']."`='".$imgsrc."' WHERE `".$options['tableId']."`=".$pos);
        $statement->execute();
    }
    else {
        jotdm_edit_db_entry($options, $pos, array($options['imageSource'] => JOTDM_DEFAULT_PROFILE_IMAGE), $db);
    }
}

?>

<?php
// HTML OBJECT GENERATORS

function jotdm_generate_table($id, $options, &$db, $split_state=null, $filter=null){
    if (isset($_GET['orderby']) &&
        isset($_GET['order'])){

        $order_by = sanitize_text_field($_GET['orderby']);
        $order = sanitize_text_field($_GET['order']);
    }
    $colspan = count($options['displayColumns']) + 1;
    if ($options['order']) {
        $colspan++;
    }
    ob_start();
?>
    <div class="table-wrapper">

    <?php if ( $options['split'] ): ?>

        <h2 class="table-label"><?php echo esc_html($options['splitBy']."=".$split_state) ?></h2>

    <?php endif; ?>

        <table class="widefat fixed striped" style="<?php echo $options['split'] ? esc_attr('') : esc_attr('border-top: none') ?>" >
            <thead>
                <?php 
                echo jotdm_generate_table_header($options['displayColumns'], isset($filter) ? false : $options['order']);
                ?>
            </thead>
            </tbody>
            <?php
            $sql_str = "SELECT * FROM `".$options['dataTable']."`";
            if ($options['split']){
               $sql_str .= " WHERE `".$options['splitBy']."`='".$split_state."'";
            }
            if (isset($filter)){
                $sql_str .= " AND (";

                $cond_arr = array();
                foreach ($options['displayColumns'] as $field){
                    $cond_arr[] = "`".$field."` LIKE '%".$filter."%'";
                }
                $sql_str .= implode(" OR ", $cond_arr).")";
            }
            if ($options['order']){
                $sql_str .= " ORDER BY `".$options['orderBy']."`";
            }
            elseif (isset($order_by) && isset($order)){
                $sql_str .= " ORDER BY `".$order_by."` ".$order;
            }
            $statement = $db->prepare( $sql_str );
            $statement->execute();
            $statement->setFetchMode( PDO::FETCH_ASSOC );
            $fetch_empty = true;

            while ( $row = $statement->fetch() ) {
                $fetch_empty = false;
                $values = array();
                foreach ( $options['displayColumns'] as $field ){
                    $values[$field] = $row[$field];
                }
                $imgsrc = $options['image'] ? $options['imageUrlRoot'] . $row[$options['imageSource']] : NULL; 
                echo jotdm_generate_table_item($id, $row[$options['tableId']], $values, isset($filter) ? false : $options['order'], $imgsrc );

            }
            if ($fetch_empty):
            ?>
            <tr class="table-row no-items">
                <td class="colspanchange" colspan=<?php echo esc_attr($colspan) ?>>
                    No entries found.
                </td>
            </tr>
            <?php endif; ?>

            </tbody>

            <tfoot>
                <?php
                echo jotdm_generate_table_header($options['displayColumns'], isset($filter) ? false : $options['order']);
                ?>
            </tfoot>
        </table>
    </div>

<?php
    return ob_get_clean();
}

function jotdm_generate_table_item($menu_name, $pos, $values, $db_ordered, $imgsrc=NULL)
{
    ob_start();
?>
    <tr data-pos=<?php echo $pos ?>>
        <th class="check-column table-row-item">
            <input type="checkbox" class="administrator" name="position[]" value="<?php echo esc_attr($pos) ?>"/>
        </th>
        <?php
        reset($values);
        $first = key($values);
        foreach ($values as $key => $value): 
        ?>
        <td class=<?php echo esc_attr('table-row-item table-row-'.$key) ?>>
            <?php
            if ( $key === $first ):
                if ( isset( $imgsrc ) ):
            ?>
                <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . $imgsrc) ? esc_url($imgsrc) : esc_url(plugins_url('img/blank-profile-picture.jpg', __FILE__)) ?>" />
            <?php
                endif;
            ?>
            <strong>
                <a href="<?php echo esc_url("admin.php?page=db-edit%2F".$menu_name."-edit.php&position=".$pos) ?>"><?php echo esc_html($value) ?></a>
            </strong>
            <?php
            else:
                echo esc_html($value);
            endif;
            ?>
        </td>
        <?php 
        endforeach;

        if ($db_ordered):
        ?>
        <td class="table-row-item table-row-rearrange clickable">
            <span class="dashicons dashicons-arrow-up-alt2" data-up-down="up"></span>
            <span class="dashicons dashicons-arrow-down-alt2" data-up-down="down"></span>
        </td>
        <?php
        endif;
        ?>
    </tr>

<?php
    return ob_get_clean();
}

function jotdm_generate_table_header($fields, $db_ordered){
    if (isset($_GET['page'])){
        $page = sanitize_text_field($_GET['page']);
    }
    if (isset($_GET['orderby']) &&
        isset($_GET['order'])){

        $order_by = sanitize_text_field($_GET['orderby']);
        $order = sanitize_text_field($_GET['order']);
    }

    ob_start();
    ?>

    <tr>
        <td class="check-column">
            <input type="checkbox" class="administrator"/>
        </td>

    <?php 
    foreach ($fields as $field): 
    ?>

            <th 
                class="row-title <?php 
                if (!$db_ordered){
                    if (isset($order_by) && $order_by==$field){
                        echo 'sorted '; 
                    }
                    else {
                        echo 'sortable ';
                    }
                }
                echo isset($order) ? esc_attr($order) : 'desc';
                ?>"
            >

        <?php 
        if (!$db_ordered):
        ?>

                <a 
                    class="sort-link clickable"
                    href=<?php echo
                        $_SERVER['PHP_SELF'].
                        '?page='.esc_attr($page).
                        '&orderby='.esc_attr($field).
                        '&order='.(isset($order) && $order=='asc' ? 'desc' : 'asc') 
                    ?>
                >
                    <span>
            
        <?php
        endif;
        ?>
                        <?php echo esc_html(ucwords( $field )) ?>

        <?php 
        if (!$db_ordered):
        ?>

                    </span>
                    <span class="sorting-indicator"></span>
                </a>
            </th>

        <?php
        endif;
        ?>

    <?php 
    endforeach;

    if ($db_ordered):
    ?>

        <th class="row-title table-row-rearrange">Rearrange</th>

    <?php
    endif;
    ?>

    </tr>

    <?php
    return ob_get_clean();
}
?>
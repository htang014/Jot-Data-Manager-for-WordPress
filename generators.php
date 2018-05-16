<?php
// HTML OBJECT GENERATORS

function generate_table($id, $options, $split_state=null, $filter=null){
    global $db;
    $colspan = count($options['displayColumns']) + 1;
    if ($options['order']) {
        $colspan++;
    }
    ob_start();
?>
    <div class="table-wrapper">

    <?php if ( $options['split'] ): ?>

        <h2 class="table-label"><?php echo $options['splitBy']."=".$split_state ?></h2>

    <?php endif; ?>

        <table class="widefat fixed striped" style=<?php echo $options['split'] ? '""' : '"border-top: none"' ?> >
            <thead>
                <?php 
                echo generate_table_header($options['displayColumns'], isset($filter) ? false : $options['order']);
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
                echo generate_table_item($id, $row[$options['tableId']], $values, isset($filter) ? false : $options['order'], $imgsrc );

            }
            if ($fetch_empty):
            ?>
            <tr class="table-row no-items">
                <td class="colspanchange" colspan=<?php echo $colspan ?>>
                    No entries found.
                </td>
            </tr>
            <?php endif; ?>

            </tbody>

            <tfoot>
                <?php
                echo generate_table_header($options['displayColumns'], isset($filter) ? false : $options['order']);
                ?>
            </tfoot>
        </table>
    </div>

<?php
    return ob_get_clean();
}

function generate_table_item($menu_name, $pos, $values, $order, $imgsrc=NULL)
{
    ob_start();
?>
    <tr data-pos=<?php echo $pos ?>>
        <th class="check-column table-row-item">
            <input type="checkbox" class="administrator" name="position[]" value="<?php echo $pos ?>"/>
        </th>
        <?php
        reset($values);
        $first = key($values);
        foreach ($values as $key => $value): 
        ?>
        <td class=<?php echo 'table-row-item table-row-'.$key ?>>
            <?php
            if ( $key === $first ):
                if ( isset( $imgsrc ) ):
            ?>
                <img src="<?php echo file_exists($_SERVER['DOCUMENT_ROOT'] . $imgsrc) ? $imgsrc : plugins_url('img/blank-profile-picture.jpg', __FILE__) ?>" />
            <?php
                endif;
            ?>
            <strong>
                <a href="<?php echo "admin.php?page=db-edit%2F".$menu_name."-edit.php&position=".$pos ?>"><?php echo $value ?></a>
            </strong>
            <?php
            else:
                echo $value;
            endif;
            ?>
        </td>
        <?php 
        endforeach;

        if ($order):
        ?>
        <td class="table-row-item table-row-rearrange">
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

function generate_table_header($fields, $order){
    ob_start();
    ?>

    <tr>
        <td class="check-column">
            <input type="checkbox" class="administrator"/>
        </td>
        <?php
        foreach ($fields as $field): 
        ?>
            <th class="row-title"><?php echo ($order?'':'<a>').ucwords( $field ).($order?'':'</a>') ?></th>
        <?php 
        endforeach;

        if ($order):
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
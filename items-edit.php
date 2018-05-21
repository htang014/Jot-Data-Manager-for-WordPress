<?php

function jotdm_fill_edit_page($id, $options, &$db){
    global $jotdm_ajax_nonce;
    $init_pos = isset($_GET['position']) ? intval($_GET['position']) : -1;
    $db_fields = jotdm_get_fields_from_table($options['dataTable'], $db);
    $edit_fields = $options['displayColumns'];
    if ($options['split']) {
        $edit_fields[] = $options['splitBy'];
    }

    foreach ($options['displayColumns'] as $value) {
        assert(in_array($value, $db_fields));
    }

    // Prevent automatically-managed fields from being edited
    $edit_fields = array_diff($edit_fields, array($options['tableId']));
    if ($options['image']){
        $edit_fields = array_diff($edit_fields, array($options['imageSource']));
    }
    if ($options['order']){
        $edit_fields = array_diff($edit_fields, array($options['orderBy']));
    }
?>

<div class="wrap" data-id="<?php echo esc_attr($id) ?>" data-name="<?php echo esc_attr($id) ?>">
    <h1 class="wp-heading-inline">Edit Existing</h1>
    <p>Edit an existing entry in this table.</p>
    <form class="ajax-form">
        <input type="hidden" name="security" value="<?php echo $jotdm_ajax_nonce ?>"/>
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="table-entry-select">Entry Selection</label>
                </th>
                <td>
                    <select name="position" id="table-entry-select">
                        <option selected value="-1">Select Entry</option>

<?php
    $statement = $db->prepare("SELECT * FROM `" . $options['dataTable'] . "`");
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
?>

    <?php while ($row = $statement->fetch()) : ?>

                        <option
                            <?php echo $init_pos == $row[$options['tableId']] ? esc_attr("selected") : esc_attr("") ?>
                            value="<?php echo esc_attr($row[$options['tableId']]) ?>"  
                            label="<?php echo esc_attr($row[$options['displayColumns'][0]]) ?>"
                        />

    <?php endwhile; ?>

                    </select>
                </td>
            </tr>
            
    <?php if ($options['image']): ?>

            <tr>
                <th>
                    <label>Image</label>
                </th>
                <td>
                    <input type="checkbox" name="remove-image" class="form-input" id="remove-image-checkbox"/>
                    <label style="cursor: default" for="remove-image">Remove</label>
                    <br>
                    <input type="file" name="image" class="form-input" id="upload-image-button"/>
                </td>
            </tr>

    <?php 
    endif;

    foreach ($edit_fields as $field): 
        $statement = $db->prepare("SELECT * FROM `" . $options['dataTable'] . "` WHERE ".$options['tableId']."=".$init_pos);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        while ($row = $statement->fetch()) {
            $field_value = $row[$field];
        }
        ?>
            <tr class="form-field form-required">
                <th>
                    <label for=<?php echo esc_attr($field . '-input') ?>>
                        <?php echo esc_attr(ucwords($field)) ?>
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <input 
                        type="text"
                        name="<?php echo esc_attr($field) ?>" 
                        id="<?php echo esc_attr($field . '-input') ?>" 
                        class="form-input" 
                        value="<?php echo isset($field_value) ? esc_attr($field_value) : "" ?>" />
                </td>
            </tr>

    <?php endforeach;?>

            </tbody>
        </table>

        <input type="hidden" name="menu-id" value="<?php echo esc_attr($id) ?>"/>
        <input type="hidden" name="action" value="row_edit"/>
        <input type="submit" class="button button-primary" value="Update Entry"/>

    </form>
</div>

<?php
}
?>
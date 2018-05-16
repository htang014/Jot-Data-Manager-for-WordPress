<?php

function fill_edit_page($id, $options){
    global $db;
    if (isset($_GET['position'])){
        $init_pos = $_GET['position'];
    }

    $db_fields = get_fields_from_table($options['dataTable']);
    $edit_fields = $options['displayColumns'];
    if ($options['split']) {
        $edit_fields[] = $options['splitBy'];
    }

    foreach ($options['displayColumns'] as $value) {
        assert(in_array($value, $db_fields));
    }
?>

<div class="wrap" data-id="<?php echo $id ?>" data-name="<?php echo htmlspecialchars($options['name']) ?>">
    <h1 class="wp-heading-inline">Edit Existing</h1>
    <p>Edit an existing entry in this table.</p>
    <form class="ajax-form" action="<?php echo plugins_url('db-edit.php', __FILE__) ?>" method="post" accept-charset="utf-8">
        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    Entry Selection
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
                            <?php echo $init_pos == $row[$options['tableId']] ? "selected" : "" ?>
                            value="<?php echo $row[$options['tableId']] ?>"  
                            label="<?php echo htmlspecialchars($row[$options['displayColumns'][0]]) ?>"
                        />

    <?php endwhile; ?>

                    </select>
                </td>
            </tr>
            
    <?php if ($options['image']): ?>

            <tr>
                <th>
                    Image
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
                    <label for=<?php echo $field . '-input' ?>>
                        <?php echo ucwords($field) ?>
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <input 
                        type="text"
                        name="<?php echo $field ?>" 
                        id="<?php echo $field . '-input' ?>" 
                        class="form-input" 
                        value="<?php echo  htmlspecialchars($field_value) ?>" />
                </td>
            </tr>

    <?php endforeach;?>

            </tbody>
        </table>

        <input type="hidden" name="menu-id" value="<?php echo $id ?>"/>
        <input type="hidden" name="task" value="<?php echo 'row-edit' ?>"/>
        <input type="submit" class="button button-primary" value="Update Entry"/>

    </form>
</div>

<?php
}
?>
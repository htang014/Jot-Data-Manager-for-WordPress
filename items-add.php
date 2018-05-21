<?php

function jotdm_fill_add_page($id, $options, &$db)
{
    global $jotdm_ajax_nonce;
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
    <h1 class="wp-heading-inline">Add New</h1>
    <p>Add a new entry to this table.</p>
    <form class="ajax-form">
        <input type="hidden" name="security" value="<?php echo $jotdm_ajax_nonce ?>"/>
        <table class="form-table">
            <tbody>

    <?php if ($options['image']): ?>

            <tr>
                <th>
                    <label for=<?php echo esc_attr($field . '-input') ?>>Image</label>
                </th>
                <td>
                    <input type="file" name=image id="image-input" class="form-input"/>
                </td>
            </tr>

    <?php endif; ?>

    <?php foreach ($edit_fields as $field): ?>

                <tr class="form-field form-required">
                    <th>
                        <label for=<?php echo esc_attr($field . '-input') ?>>
                            <?php echo esc_attr(ucwords($field)) ?>
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="<?php echo esc_attr($field) ?>" id="<?php echo esc_attr($field . '-input') ?>" class="form-input"/>
                    </td>
                </tr>

    <?php endforeach;?>

            </tbody>
        </table>
        <input type="hidden" name="menu-id" value="<?php echo esc_attr($id) ?>"/>
        <input type="hidden" name="action" value="row_add"/>
        <input type="submit" class="button button-primary" value="Add New"/>
    </form>
</div>

<?php
}
?>
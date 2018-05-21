<?php
function fill_list_page($id, $options, &$db)
{
    global $jotdm_ajax_nonce;
    $db_fields = jotdm_get_fields_from_table($options['dataTable'], $db);
    $order_field = $options['order'] ? $options['orderBy'] : null;
    $image_field = $options['image'] ? $options['imageSource'] : null;
    $split_field = $options['split'] ? $options['splitBy'] : null;

    $filter = isset($_GET['filter']) ? $_GET['filter'] : null;
    ?>
    
    <div class="wrap" data-id="<?php echo esc_attr($id) ?>" data-name="<?php echo esc_attr($id) ?>">

	<?php
    foreach ($options['displayColumns'] as $value) {
        assert(in_array($value, $db_fields));
    }
    ?>

<h1 class="wp-heading-inline"><?php echo esc_html($options['name']) ?></h1>
<a href=<?php esc_url(menu_page_url('db-edit/' . $id . '-add.php'))?> class="page-title-action">Add New</a>


<form action="admin.php" method="get" >
    <p class="search-box">
        <input type="hidden" name="page" value="<?php echo esc_attr("db-edit/".$id."-list.php") ?>"/>
        <input type="search" name="filter"/>
        <input class="button-secondary" type="submit" value="Search"/>
    </p>
</form>

<form class="ajax-form">
    <input type="hidden" name="security" value="<?php echo $jotdm_ajax_nonce ?>"/>
    <div class="tablenav top">
        <select name="action">
            <option selected value="-1">Bulk Actions</option>
            <option value="row_delete">Delete</option>
        </select>
        <input type="hidden" name="menu-id" value="<?php echo esc_attr($id) ?>"/>
        <input class="button-secondary" type="submit" value="Apply" />
    </div>

	<?php
    $sql_str = $options['split'] ? 
        "SELECT DISTINCT `" . $options['splitBy'] . "` FROM `" . $options['dataTable'] . "`" :
        "SELECT * FROM `" . $options['dataTable'] . "` LIMIT 1";

    $statement = $db->prepare($sql_str);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ($row = $statement->fetch()) {
        echo jotdm_generate_table($id, $options, $db,
            $options['split'] ? $row[$options['splitBy']] : NULL,
            $filter);
    }
    ?>
		</form>
	</div>
<?php
}
?>
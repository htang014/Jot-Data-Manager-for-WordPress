<?php
function fill_list_page($id, $options)
{
    global $db;
    $db_fields = get_fields_from_table($options['dataTable']);
    $order_field = $options['order'] ? $options['orderBy'] : null;
    $image_field = $options['image'] ? $options['imageSource'] : null;
    $split_field = $options['split'] ? $options['splitBy'] : null;

    if (isset($_GET['filter'])){
        $filter = $_GET['filter'];
    }
    ?>
    
	<div class="wrap" data-id="<?php echo $id ?>" data-name="<?php echo htmlspecialchars($options['name']) ?>">
	<?php
    foreach ($options['displayColumns'] as $value) {
        assert(in_array($value, $db_fields));
    }
    ?>

<h1 class="wp-heading-inline"><?php echo $options['name'] ?></h1>
<a href=<?php menu_page_url('db-edit/' . $options['name'] . '-add.php')?> class="page-title-action">Add New</a>

<form action="<?php echo "admin.php" ?>" method="get" >
    <p class="search-box">
        <input type="hidden" name="page" value="<?php echo "db-edit/".$options['name']."-list.php" ?>"/>
        <input type="search" name="filter"/>
        <input class="button-secondary" type="submit" value="Search"/>
    </p>
</form>

<form class="ajax-form" action="<?php echo plugins_url('db-edit.php', __FILE__) ?>" method="post" >
    <div class="tablenav top">
        <select name="task">
            <option selected value="-1">Bulk Actions</option>
            <option value="row-delete">Delete</option>
        </select>
        <input type="hidden" name="menu-id" value="<?php echo $id ?>"/>
        <input class="button-secondary" type="submit" value="<?php esc_attr_e('Apply');?>" />
    </div>

	<?php
    $sql_str = $options['split'] ? 
        "SELECT DISTINCT `" . $options['splitBy'] . "` FROM `" . $options['dataTable'] . "`" :
        "SELECT * FROM `" . $options['dataTable'] . "` LIMIT 1";

    $statement = $db->prepare($sql_str);
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    while ($row = $statement->fetch()) {
        echo generate_table($options,
            $options['split'] ? $row[$options['splitBy']] : NULL,
            $filter);
    }
    ?>
		</form>
	</div>
<?php
}
?>
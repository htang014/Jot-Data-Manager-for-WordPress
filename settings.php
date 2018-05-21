<?php
require_once 'includes.php';

function jotdm_fill_settings_page()
{
    //STAGE 1
    $form_stage = 0;
    $ini = parse_ini_file("settings.ini",true);
    $menu_key = isset($_GET['menu-select']) ? intval($_GET['menu-select']) : -1;
    $options = ($menu_key>=0) ? $ini[$menu_key] : null;

    if (isset($options)){
        $db_host = $options['dbhost'];
        $db_name = $options['dbname'];
        $db_user = $options['dbuser'];
        $db_pass = $options['dbpass'];

        if (!isset($_GET['table-select'])){
            $table_select = $options['dataTable'];
        }
        $form_stage++;
    }
    elseif (isset($_GET['db-host']) ||
        isset($_GET['db-name']) ||
        isset($_GET['db-user']) ||
        isset($_GET['db-pass']) ){

        if (!isset($_GET['db-host']) ||
            !isset($_GET['db-name']) ||
            !isset($_GET['db-user']) ||
            !isset($_GET['db-pass'])) {
?>

<div class="notice notice-error">
    <p><strong>Error:</strong> Invalid GET request.</p>
</div>

<?php
        }
        else {
            $db_host = sanitize_text_field($_GET['db-host']);
            $db_name = sanitize_text_field($_GET['db-name']);
            $db_user = sanitize_text_field($_GET['db-user']);
            $db_pass = sanitize_text_field($_GET['db-pass']);

            $form_stage++;
        }
    }
?>
<div class="wrap">

    <h1 class="wp-heading-inline">Jot Settings</h1>
    <p>Add an admin menu for a single data table or edit an existing menu.</p>

<!-- Initial form -->
    <form action="admin.php" method="get" accept-charset="utf-8">

        <input type="hidden" name="page" value="db-edit/settings.php"/>

        <table class="form-table">
            <tbody>
    <!-- Menu Selection -->
            <tr class="form-field">
                <th>
                    <label for="menu-select">
                        Select Menu
                    </label>
                </th>
                <td>
                    <select name="menu-select" class="form-input" id="menu-select">
                        <option selected value="-1" label="Create New">

    <?php foreach ($ini as $key=>$value): ?>

                        <option
                            <?php echo (isset($menu_key) && $menu_key==$key) ? "selected" : "" ?>
                            value="<?php echo esc_attr($key) ?>"  
                            label="<?php echo esc_attr($value['name']) ?>"
                        />

    <?php endforeach ?>

                    </select>
                </td>
            </tr>
        </tbody>
        </table>
    </form>

    <!-- Retrieves DB info from user -->
    <form class="generic-form" action="admin.php" method="get" accept-charset="utf-8">
        <input type="hidden" name="page" value="db-edit/settings.php"/>

    <?php if (isset($options)): ?>
        <input type="hidden" name="menu-select" value="<?php echo esc_attr($menu_key) ?>"/>
    <?php endif; ?>

        <table class="form-table">
            <tbody>
    <!-- Database Host -->
                <tr class="form-field form-required">
                    <th>
                        <label for="db-host">
                            Database Host
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            name="db-host" 
                            class="form-input" 
                            id="db-host" 
                            value="<?php echo isset($db_host) ? esc_attr($db_host) : '' ?>"
                        />
                    </td>
                </tr>
    <!-- Database Name -->
                <tr class="form-field form-required">
                    <th>
                        <label for="db-name">
                            Database Name
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input 
                            type="text"
                            name="db-name" 
                            class="form-input" 
                            id="db-name" 
                            value="<?php echo isset($db_name) ? esc_attr($db_name) : '' ?>"
                        />
                    </td>
                </tr>
    <!-- Database Credentials -->
                <tr class="form-field form-required">
                    <th>
                        <label for="db-user">
                            Database Credentials
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text" 
                            name="db-user" 
                            class="form-input" 
                            id="db-user" 
                            placeholder="User" 
                            value="<?php echo isset($db_user) ? esc_attr($db_user) : '' ?>"
                        />
                        <input 
                            type="text" 
                            name="db-pass" 
                            class="form-input" 
                            placeholder="Password" 
                            value="<?php echo isset($db_pass) ? esc_attr($db_pass) : '' ?>"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="submit" class="button button-primary" value="Update"/>
    </form>

    <!-- Establish link with database -->
    <!-- Abort on failure and display warning -->
<?php
    //STAGE 2
    if (!($form_stage >= 1)){
        return;
    }

    try {
        $db = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_pass);
    }
    catch (Exception $e) {
?>

    <div class="notice notice-error">
        <p><strong>Error:</strong> Database connection failed.  Credentials may be invalid.</p>
    </div>

<?php
        return;
    }

?>

    <!-- Form is displayed in DB info is valid -->
    <!-- Lists tables in database as select -->
    <form action="admin.php" method="get" accept-charset="utf-8">

        <input type="hidden" name="page" value="db-edit/settings.php"/>
        <input type="hidden" name="db-host" value="<?php echo esc_attr($db_host) ?>"/>
        <input type="hidden" name="db-name" value="<?php echo esc_attr($db_name) ?>"/>
        <input type="hidden" name="db-user" value="<?php echo esc_attr($db_user) ?>"/>
        <input type="hidden" name="db-pass" value="<?php echo esc_attr($db_pass) ?>"/>

    <?php if (isset($options)): ?>
        <input type="hidden" name="menu-select" value="<?php echo esc_attr($menu_key) ?>"/>
    <?php endif; ?>

        <table class="form-table">
            <tbody>

    <!-- Table Selection -->
            <tr>
                <th>
                    <label for="table-select">Table Selection</label>
                    <span class="description">(required)</span>
                </th>
                <td>
                    <select name="table-select" id="table-select">
                        <option selected value="-1">Select Table</option>

    <!-- Compile list of tables in database to Table Selection field -->
<?php
        if (isset($_GET['table-select']) && $_GET['table-select']!=-1){
            $table_select = sanitize_text_field($_GET['table-select']);
        }

        $statement = $db->prepare(
            "SELECT TABLE_NAME ". 
            "FROM INFORMATION_SCHEMA.TABLES ".
            "WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA='".$db_name."' "
        );
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
?>

        <?php while ($row = $statement->fetch()) : ?>

                        <option
                            <?php echo $table_select == $row['TABLE_NAME'] ? esc_attr("selected") : esc_attr("") ?>
                            value="<?php echo esc_attr($row['TABLE_NAME']) ?>"  
                            label="<?php echo esc_attr($row['TABLE_NAME']) ?>"
                        />

        <?php endwhile; ?>

                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

<?php 
    if (isset($table_select)){
        $form_stage++;
    }

    // STAGE 3
    if (!($form_stage >= 2)){
        return;
    }
?>

<!-- Form to be sent to server -->
<!-- Contains all relevant info for menu creation -->
    <form class="ajax-form">
        <input type="hidden" name="action" value="menu_edit"/>
        <input type="hidden" name="db-host" value="<?php echo esc_attr($db_host) ?>"/>
        <input type="hidden" name="db-name" value="<?php echo esc_attr($db_name) ?>"/>
        <input type="hidden" name="db-user" value="<?php echo esc_attr($db_user) ?>"/>
        <input type="hidden" name="db-pass" value="<?php echo esc_attr($db_pass) ?>"/>
        <input type="hidden" name="table-select" value="<?php echo esc_attr($table_select) ?>"/>

    <?php if (isset($options)): ?>
        <input type="hidden" name="menu-select" value="<?php echo esc_attr($menu_key) ?>"/>
    <?php endif; ?>

        <table class="form-table">
            <tbody>

    <!-- Menu Title -->
            <tr class="form-field form-required">
                <th>
                    <label for="menu-title">
                        Menu Title
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <input type="text" name="menu-title" id="menu-title" class="form-input" value="<?php echo esc_attr($options['name']) ?>"/>
                </td>
            </tr>

    <!-- Dashicon -->
            <tr class="form-field">
                <th>
                    <label for="icon">
                        Dashicon<br>
                        <a href="https://developer.wordpress.org/resource/dashicons/#menu" target="_blank">Find icons</a>
                    </label>
                </th>
                <td>
                    <input type="text" name="icon" id="icon" class="form-input" placeholder="dashicons-example-icon" value="<?php echo esc_attr($options['icon']) ?>"/>
                </td>
            </tr>   

    <!-- Fields to Display -->
            <tr class="form-field">
                <th>
                    <label>
                        Fields to Display
                    </label>
                </th>
                <td>

    <!-- Compile list of tables in database to Fields to Display field -->
<?php 
    $statement = $db->prepare("DESCRIBE `".$table_select."`");
    $statement->execute();
    $statement->setFetchMode(PDO::FETCH_ASSOC);
    $fields = array();
    while ($row = $statement->fetch()) : 
?>

                    <input
                        type="checkbox" 
                        class="form-input" 
                        name="display-fields[]" 
                        value="<?php echo esc_attr($row['Field']) ?>" 
                        <?php
				            if (isset($options['displayColumns'])){
                        		echo in_array($row['Field'],$options['displayColumns']) ? esc_attr("checked") : esc_attr("") ;
                        	}
                        ?>
                    />
                    <label><?php echo esc_html($row['Field']) ?></label>
                    <br>
<?php 
        $fields[] = $row['Field'];
    endwhile; 
?>
                    
                </td>
            </tr>

    <!-- Primary Display Field -->
            <tr class="form-field">
                <th>
                    <label for="primary-field">
                        Primary Display Field
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <select name="primary-field" id="primary-field">

    <?php foreach ($fields as $field): ?>

                        <option
                            value="<?php echo esc_attr($field) ?>"  
                            label="<?php echo esc_attr($field) ?>"
                            <?php echo $options['displayColumns'][0] == $field ? esc_attr("selected") : esc_attr("") ?>
                        />

    <?php endforeach; ?>

                    </select>
                </td>
            </tr>

    <!-- Table ID Field -->
            <tr class="form-field">
                <th>
                    <label for="table-id">
                        Table ID Field
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <select name="table-id" id="table-id">

    <?php foreach ($fields as $field): ?>

                        <option
                            value="<?php echo esc_attr($field) ?>"  
                            label="<?php echo esc_attr($field) ?>"
                            <?php echo $options['tableId'] == $field ? esc_attr("selected") : esc_attr("") ?>
                        />

    <?php endforeach; ?>

                    </select>
                </td>
            </tr>

    <!-- Picture -->
            <tr class="form-field form-required">
                <th>
                    <label for="image">
                        Picture
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox"
                        class="form-input" 
                        id="enable-picture-checkbox" 
                        name="image" 
                        value="on" 
                        <?php echo $options['image'] ? esc_attr("checked") : esc_attr("") ?>
                    />
                    <label>Associate each entry with an image from the following directory:</label>
                    <br>
                    <br>
                    <div class="inline-input-wrapper">
                        <label for="img-url-root"><?php echo esc_url(realpath($_SERVER['DOCUMENT_ROOT'])) ?></label>
                        <input 
                            type="text" 
                            name="img-url-root" 
                            class="form-input" 
                            id="picture-path-input" 
                            placeholder="/relative/path/from/root/"
                            value="<?php echo esc_attr($options['imageUrlRoot']) ?>" 
                            <?php echo $options['image'] ? esc_attr("") : esc_attr("disabled") ?>
                        />
                    </div>
                    <br>
                    <br>
                    <label>Names of image files for each entry are found in the following table field:</label>
                    <br>
                    <br>
                    <select 
                        name="imgsrc" 
                        id="image-field-select" 
                        <?php echo $options['image'] ? esc_attr("") : esc_attr("disabled") ?>
                    >

    <?php foreach ($fields as $field): ?>

                        <option
                            value="<?php echo esc_attr($field) ?>"  
                            label="<?php echo esc_attr($field) ?>"
                            <?php echo $options['imageSource'] == $field ? esc_attr("selected") : esc_attr("") ?>
                        />

    <?php endforeach; ?>

                    </select>
                </td>
            </tr>

    <!-- Extra Options -->
            <tr class="form-field">
                <th>
                    <label>
                        Extra Options
                    </label>
                </th>
                <td>
                    <input 
                        type="checkbox" 
                        class="form-input" 
                        name="split" 
                        id="split-checkbox" 
                        value="on"
                        <?php echo $options['split'] ? esc_attr("checked") : esc_attr("") ?>
                    />
                    <label>Display entries in separate tables based on </label>
                    <select 
                        name="split-by" 
                        id="split-by-select" 
                        <?php echo $options['split'] ? esc_attr("") : esc_attr("disabled") ?>
                    >

    <?php foreach ($fields as $field): ?>

                        <option
                            value="<?php echo esc_attr($field) ?>"  
                            label="<?php echo esc_attr($field) ?>"
                            <?php echo $options['splitBy'] == $field ? esc_attr("selected") : esc_attr("") ?>
                        />

    <?php endforeach; ?>

                    </select>
                    <br>

                    <input 
                        type="checkbox"
                        class="form-input" 
                        name="order" 
                        id="order-checkbox" 
                        value="on"
                        <?php echo $options['order'] ? esc_attr("checked") : esc_attr("") ?>
                    />
                    <label>Entries are numerically ordered by </label>


                    <select
                        name="order-by" 
                        id="order-by-select" 
                        <?php echo $options['order'] ? esc_attr("") : esc_attr("disabled") ?>
                    >

    <?php foreach ($fields as $field): ?>

                        <option
                            value="<?php echo esc_attr($field) ?>"  
                            label="<?php echo esc_attr($field) ?>"
                            <?php echo $options['orderBy'] == $field ? esc_attr("selected") : esc_attr("") ?>
                        />

    <?php endforeach; ?>

                    </select>
                    <br>

                </td>
            </tr>
            </tbody>
        </table>
        <input 
            type="submit" 
            class="button button-primary" 
            value="<?php echo (isset($menu_key) && $menu_key!=-1) ? esc_attr("Save Changes") : esc_attr("Add Menu") ?>"
        />
    </form>

    <?php if (isset($menu_key) && $menu_key!=-1): ?>
    <br>
<!-- Deletion Form -->
    <form class="ajax-form" style="float:right" action="<?php echo esc_url(plugins_url('settings-edit.php', __FILE__)) ?>" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="menu_delete"/>
        <input type="hidden" name="menu-select" value="<?php echo esc_attr($menu_key) ?>"/>
        <input 
            type="submit" 
            class="button button-secondary" 
            value="Delete Menu"
        />
    </form>
    <?php endif; ?>
</div>

<?php
}
?>
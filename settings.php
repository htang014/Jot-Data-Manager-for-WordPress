<?php
require_once 'includes.php';

function fill_settings_page()
{
    $form_updated = false;

    if (isset($_GET['db-host']) ||
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
            $db_host = $_GET['db-host'];
            $db_name = $_GET['db-name'];
            $db_user = $_GET['db-user'];
            $db_pass = $_GET['db-pass'];
            $form_updated = true;
        }
    }
?>
<div class="wrap" data-id=<?php echo $id ?> data-name=<?php echo $options['name'] ?>>
    <h1 class="wp-heading-inline">Jot Settings</h1>
    <p>Add an admin menu for a single data table or edit an existing menu.</p>

    <form class="generic-form" action="admin.php" method="get" accept-charset="utf-8">
        <input type="hidden" name="page" value="db-edit/settings.php"/>
        <table class="form-table">
            <tbody>
                <tr class="form-field form-required">
                    <th>
                        <label for="db-host">
                            Database Host
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="db-host" class="form-input" id="db-host" value="<?php echo $db_host ?>"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th>
                        <label for="db-name">
                            Database Name
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="db-name" class="form-input" id="db-name" value="<?php echo $db_name ?>"/>
                    </td>
                </tr>
                <tr class="form-field form-required">
                    <th>
                        <label for="db-user">
                            Database Credentials
                            <span class="description">(required)</span>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="db-user" class="form-input" id="db-user" placeholder="User" value="<?php echo $db_user ?>"/>
                        <input type="text" name="db-pass" class="form-input" placeholder="Password" value="<?php echo $db_pass ?>"/>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="submit" class="button button-primary" value="Update"/>
    </form>

<?php
    if ($form_updated){
        try {
            //$db = new PDO('mysql:host=crmtech.local;dbname=local', 'root', 'root');
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

    <form action="admin.php" method="get" accept-charset="utf-8">

        <input type="hidden" name="page" value="db-edit/settings.php"/>
        <input type="hidden" name="db-host" value="<?php echo $db_host ?>"/>
        <input type="hidden" name="db-name" value="<?php echo $db_name ?>"/>
        <input type="hidden" name="db-user" value="<?php echo $db_user ?>"/>
        <input type="hidden" name="db-pass" value="<?php echo $db_pass ?>"/>

        <table class="form-table">
            <tbody>
            <tr>
                <th>
                    <label for="table-select">Table Selection</label>
                    <span class="description">(required)</span>
                </th>
                <td>
                    <select name="table-select" id="table-select">
                        <option selected value="-1">Select Table</option>

<?php
        if (isset($_GET['table-select']) && $_GET['table-select']!=-1){
            $table_select = $_GET['table-select'];
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
                            <?php echo $table_select == $row['TABLE_NAME'] ? "selected" : "" ?>
                            value="<?php echo $row['TABLE_NAME'] ?>"  
                            label="<?php echo $row['TABLE_NAME'] ?>"
                        />

        <?php endwhile; ?>

                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

        <?php if (isset($table_select)): ?>

    <form class="ajax-form" action="<?php echo plugins_url('db-edit.php', __FILE__) ?>" method="post" accept-charset="utf-8">
        <table class="form-table">
            <tbody>
            <tr class="form-field form-required">
                <th>
                    <label for="menu-title">
                        Menu Title
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <input type="text" name="menu-title" id="menu-title" class="form-input"/>
                </td>
            </tr>
            <tr class="form-field">
                <th>
                    <label>
                        Fields to Display
                    </label>
                </th>
                <td>

<?php 
        $statement = $db->prepare("DESCRIBE `".$table_select."`");
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $fields = array();
        while ($row = $statement->fetch()) : 
?>

                    <input type="checkbox" class="form-input" name="display-fields[]" value="<?php echo $row['Field'] ?>"/>
                    <label><?php echo $row['Field'] ?></label>
                    <br>
<?php 
            $fields[] = $row['Field'];
        endwhile; 
?>
                    
                </td>
            </tr>
            <tr class="form-field form-required">
                <th>
                    <label for="table-id">
                        Table ID Field
                        <span class="description">(required)</span>
                    </label>
                </th>
                <td>
                    <select name="table-id" id="table-id">

        <?php foreach ($fields as $field) : ?>

                        <option
                            value="<?php echo $field ?>"  
                            label="<?php echo $field ?>"
                        />

        <?php endforeach ?>

                    </select>
                </td>
            </tr>
            <tr class="form-field form-required">
                <th>
                    <label for="image">
                        Picture
                    </label>
                </th>
                <td>
                    <input type="checkbox" class="form-input" id="enable-picture-checkbox" name="image" value="on"/>
                    <label>For each entry, display a small image from the following directory:</label>
                    <br>
                    <br>
                    <div class="inline-input-wrapper">
                        <label for="img-url-root"><?php echo realpath($_SERVER['DOCUMENT_ROOT']) ?></label>
                        <input type="text" name="img-url-root" class="form-input" id="picture-path-input" placeholder="/relative/path/from/root/" disabled/>
                    </div>
                    <br>
                    <br>
                    <label>Names of image files for each entry are found in the following field:</label>
                    <br>
                    <br>
                    <select name="imgsrc" id="image-field-select" disabled>

        <?php foreach ($fields as $field) : ?>

                        <option
                            value="<?php echo $field ?>"  
                            label="<?php echo $field ?>"
                        />

        <?php endforeach ?>

                    </select>
                </td>
            </tr>
            <tr class="form-field">
                <th>
                    <label>
                        Extra Options
                    </label>
                </th>
                <td>
                    <input type="checkbox" class="form-input" name="split" value="on"/>
                    <label>Display entries in separate tables based on </label>


                    <select name="split-by" id="split-by-select">

        <?php foreach ($fields as $field) : ?>

                        <option
                            value="<?php echo $field ?>"  
                            label="<?php echo $field ?>"
                        />

        <?php endforeach ?>

                    </select>
                    <br>

                    <input type="checkbox" class="form-input" name="order" value="on"/>
                    <label>Entries are numerically ordered by </label>


                    <select name="order-by" id="order-by-select">

        <?php foreach ($fields as $field) : ?>

                        <option
                            value="<?php echo $field ?>"  
                            label="<?php echo $field ?>"
                        />

        <?php endforeach ?>

                    </select>
                    <br>

                </td>
            </tr>
            </tbody>
        </table>
        <input type="submit" class="button button-primary" value="Add Menu"/>
    </form>


<?php
        endif;
    }
?>

</div>

<?php
}
?>
=== Plugin Name ===
Contributors: leetsombrero
Tags: database, mysql, data, table, editor, sql, list
Requires at least: 4.9.5
Tested up to: 4.9.6
Requires PHP: 7.1.7
Stable tag: 1.18.522.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
<p>Jot allows non-technical WordPress administrators to modify MySQL
tables with a lightweight, user-friendly interface.  This plugin currently supports MySQL only.</p>

### Features
* Create menus directly in the WordPress administration page to view and interact with tables
* Quickly add and edit single entries to the data table
* Associate table entries with images, which are uploaded and managed using the Jot interface
* Rearrange table entries or split entries into multiple tables

### Usage
#### Creating a menu
* To create a menu, navigate to the "Settings">"Jot Settings"
* Ensure "Create New" is selected under "Menu Selection"
* Fill in your database information and click Update
* Choose the table that you want to create a menu for
* Fill in the remaining form and click "Add New"

#### Modifying a menu
* To edit a menu, navigate to the "Settings">"Jot Settings"
* Choose a menu to edit under "Menu Selection"
* Modify the settings to your liking and click "Save Changes" at the bottom of the page

#### Deleting a menu
* To delete a menu, navigate to the "Settings">"Jot Settings"
* Choose a menu to edit under "Menu Selection"
* Click "Delete Menu" at the bottom of the page

#### Regarding image association
* Tables you wish to associate with images must have a dedicated image source column (ex. `imgsrc`)
     * The image source column will contain the image FILENAME
     * The image directory will be defined during setup
* To use your own default image (used when no association is made), 
       simply add a "blank-profile-picture.jpg" to your image directory
       
== Screenshots ==
1. Display page for an Employees data table
2. Display page for a Projects data table
3. Edit page for a table entry
4. Page to add a new table entry

== Installation ==
#### Automatic installation
* Log in to your WordPress dashboard
* Navigate to the Plugins menu and click Add New.
* Search for Jot and download it directly
* Activate Jot in your Plugins menu

#### Manual installation
To manually install this plugin, you must upload it to your WordPress plugin directory. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
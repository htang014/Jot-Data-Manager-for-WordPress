
<img src="https://i.imgur.com/E3D0T2A.png"
     alt="icon"
     height="128"
     style="float: left; margin-right: 10px;" />
     
# Jot Data Manager

```diff
This plugin is in alpha.
Currently supports MySQL only.
```
## Information

### Description
<p>Jot allows non-technical WordPress administrators to modify MySQL
tables with a lightweight, user-friendly interface.</p>

### Features
* Create menus directly in the WordPress administration page to view and interact with tables
* Quickly add and edit single entries to the data table
* Associate table entries with images, which are uploaded and managed using the Jot interface
* Rearrange table entries or split entries into multiple tables

### Installation
* Download project as zip
* Upload to WordPress project via the plugins menu
* Activate plugin via the Plugins menu

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

## Screenshots:
<img src="https://i.imgur.com/PiYq2sW.png"
     alt="Display Page"
     style="float: left; margin-right: 10px;" />

<img src="https://i.imgur.com/CLpzucS.png"
     alt="Display Page 2"
     style="float: left; margin-right: 10px;" />

<img src="https://i.imgur.com/mIuDTfL.png"
     alt="Edit Entry Page"
     style="float: left; margin-right: 10px;" />
     
<img src="https://i.imgur.com/nFTwktu.png"
     alt="Add Entry Page"
     style="float: left; margin-right: 10px;" />
     
<img src="https://i.imgur.com/75Z7ngI.png"
     alt="Settings Page"
     style="float: left; margin-right: 10px;" />

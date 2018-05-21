<?php
/*
Plugin Name: Jot Data Manager
description: A non-technical interface for modifying MySQL tables
Version: 0.2
Author: Hans Tang
Author URI: https://hanst.me
License: GPL2
*/
require_once('includes.php');

function init_scripts() {
    wp_register_style(
        'db-edit-styles', 
        plugins_url('css/style.css', __FILE__),
        array(),
        ''
    );
    wp_enqueue_style('db-edit-styles');

    wp_register_script(
		'db-edit-scripts',
		plugins_url( 'js/scripts.js' , __FILE__ ),
        array( 'jquery' ),
        '',
        true
	);
    wp_enqueue_script( 'db-edit-scripts' );
    wp_localize_script('db-edit-scripts', 'plugin', array(
        'url' => plugins_url('/', __FILE__),
        'admin_root' => admin_url()
    ));
}

function init_menu() {
    add_options_page('Jot Settings',
        'Jot Settings', 
        'manage_options', 
        'db-edit/settings.php',  
        function() { jotdm_fill_settings_page(); } );


    $ini = parse_ini_file("settings.ini",true);
	foreach ($ini as $key=>$options){
        $db = new PDO('mysql:host='.$options['dbhost'].';dbname='.$options['dbname'], $options['dbuser'], $options['dbpass']);
		add_menu_page( $options['name'].' List',
			$options['name'], 
			'manage_options', 
			'db-edit/'.$key.'-list.php', 
			function() use ($key, $options, &$db) { fill_list_page($key, $options, $db); }, 
			$options['icon'], 
			6  );

        add_submenu_page( 'db-edit/'.$key.'-list.php',
            $options['name'].' Add',
			'Add New', 
			'manage_options', 
			'db-edit/'.$key.'-add.php',  
            function() use ($key, $options, &$db) { jotdm_fill_add_page($key, $options, $db); } );
            
        add_submenu_page( 'db-edit/'.$key.'-list.php',
            $options['name'].' Edit',
			'Edit Existing', 
			'manage_options', 
			'db-edit/'.$key.'-edit.php',  
			function() use ($key, $options, &$db) { jotdm_fill_edit_page($key, $options, $db); } );
    }
}

add_action( 'wp_ajax_row_add', 'row_add_handler' );
add_action( 'wp_ajax_row_edit', 'row_edit_handler' );
add_action( 'wp_ajax_row_reorder', 'row_reorder_handler' );
add_action( 'wp_ajax_row_delete', 'row_delete_handler' );
add_action( 'wp_ajax_menu_delete', 'menu_delete_handler' );
add_action( 'wp_ajax_menu_edit', 'menu_edit_handler' );

add_action( 'admin_menu', 'init_menu' );
add_action( 'admin_enqueue_scripts', 'init_scripts');
?>
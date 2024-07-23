<?php
/*
Plugin Name: Excel Import
Description: Tool to import Excel data for company products.
Author:      Matt Bedford | Ulisse SnC
Version:     1.0.0
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 
2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
with this program. If not, visit: https://www.gnu.org/licenses/

*/

namespace ExcelImport;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once 'vendor/autoload.php';

// include dependencies
//require plugin_dir_path( __FILE__ ) . 'event_globals.php';
include_once plugin_dir_path( __FILE__ ) . 'importer.php';
include_once plugin_dir_path( __FILE__ ) . 'database.php';


add_action( 'admin_menu', 'ExcelImport\excel_import_menu_page' );

function excel_import_menu_page() {

    add_menu_page( 
            'Import products', 
            'Import products', 
            'manage_options', 
            'excel-import',
            'ExcelImport\importer::admin_page',
            'dashicons-upload', 
            6  
        );

}


// Create new database table on plugin activation
register_activation_hook( __FILE__, ['ExcelImport\database', 'create_table'] );

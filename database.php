<?php


namespace ExcelImport;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class database {

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'listato_importato';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            elemento text NOT NULL,
            corrispondente text NOT NULL,
            tipologia varchar(55) DEFAULT '' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }


    public static function clear_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'listato_importato';
        $sql = "TRUNCATE TABLE $table_name";
        $wpdb->query( $sql );
    }


    public static function insert_data( $data ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'listato_importato';
        $wpdb->insert( $table_name, $data );
    }


    public static function get_data() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'listato_importato';
        $sql = "SELECT * FROM $table_name";
        return $wpdb->get_results( $sql );
    }

}
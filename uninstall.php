<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit ();

global $wpdb;
//Delete table
$sql = "DROP TABLE {$wpdb->prefix}log";
$wpdb->query($sql);


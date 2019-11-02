<?php
/*
Plugin Name: log
Plugin URI: https://www.wp-sultan.com
Description:This plugin can keep Logs for users with login and logout activity with UserName , UserRole , Date and IP.
Version: 1.0.0
Author: https://www.wp-sultan.com
Author URI: https://www.webdreamers.ir
 */

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

register_activation_hook(__FILE__, 'active_log');

function active_log()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix.'log';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  username varchar(35) NOT NULL,
  role varchar(35) NOT NULL,
  ip varchar(15) DEFAULT '' NOT NULL,
  event varchar(15) NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate;";

    require_once ABSPATH.'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

function style()
{
    wp_enqueue_style('log_style', plugin_dir_url(__FILE__).'css/log-style.css');
}
add_action('admin_enqueue_scripts', 'style');

add_action('wp_login', 'login_log', 10, 2);

function login_log($user_login, $user)
{
    $user_role = implode(', ', $user->roles);
    global $wpdb;
    $table_name = $wpdb->prefix.'log';

    $wpdb->insert(
    $table_name,
    array(
        'time' => current_time('mysql', 1),
        'username' => $user_login,
        'role' => $user_role,
        'ip' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? esc_attr($_SERVER['HTTP_X_FORWARDED_FOR']) : esc_attr($_SERVER['REMOTE_ADDR']),
        'event' => 'login',
    ),
    array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
    )
 );
}

add_action('wp_logout', 'logout_log');

function logout_log()
{
    $user = wp_get_current_user();
    $user_role = implode(', ', $user->roles);
    $user_login = $user->user_login;
    global $wpdb;
    $table_name = $wpdb->prefix.'log';

    $wpdb->insert(
    $table_name,
    array(
        'time' => current_time('mysql', 1),
        'username' => $user_login,
        'role' => $user_role,
        'ip' => isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? esc_attr($_SERVER['HTTP_X_FORWARDED_FOR']) : esc_attr($_SERVER['REMOTE_ADDR']),
        'event' => 'logout',
    ),
    array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%s',
    )
 );
}

function menu_activity_log()
{
    add_menu_page(
        'Activity Log',
        'Activity Log',
        'manage_options',
        'activelog',
        'activity_log_page',
        'dashicons-welcome-write-blog',
        2
    );
}
add_action('admin_menu', 'menu_activity_log');

function activity_log_page()
{
    global $wpdb;
    $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
    $limit = 10;
    $offset = ($pagenum - 1) * $limit;
    $total = $wpdb->get_var('SELECT COUNT(*) FROM wp_log');
    $num_of_pages = ceil($total / $limit);

    $qry = "select * from wp_log LIMIT $offset, $limit";
    $result = $wpdb->get_results($qry, object); ?>
    <div class="wrap log">
    <h2> Activity Log </h2>
    <table class="table-log">
    <thead>
    <tr>

            <th style="width:5%">ID</th> 
            <th style="width:15%">Date</th> 
            <th style="width:15%">IP</th> 
            <th style="width:15%">User Name</th> 
            <th style="width:15%">User Role</th> 
            <th style="width:35%">Event</th> 

    </tr>
    </thead>

   

    <tbody>
        <?php
    if ($result):
        foreach ($result as $row) {
            ?>
            <tr>
            <td class="td-log"><?= $row->id; ?></td> 
            <td class="td-log"><?= $row->time; ?></td> 
            <td class="td-log"><?= $row->ip; ?></td> 
            <td class="td-log"><?= $row->username; ?></td> 
            <td class="td-log"><?= $row->role; ?></td> 
            <td class="td-log"><?= $row->event; ?></td>
            </tr>
        <?php
        } ?>
             

    </tbody>
</table>
    <?php

        $page_links = paginate_links(array(
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;', 'aag'),
            'next_text' => __('&raquo;', 'aag'),
            'total' => $num_of_pages,
            'current' => $pagenum,
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_next' => true,
            'prev_text' => __('&larr;', 'aag'),
            'next_text' => __('&rarr;', 'aag'),
            'type' => 'list',
            'before_page_number' => '',
            'after_page_number' => '',
        ));
    if ($page_links) {
        ?>
            <br class="clear">
        <div><nav id="archive-navigation" class="paging-navigation tbWow fadeInUp" role="navigation" style="visibility: visible; animation-name: fadeInUp;">
            <ul class="page-numbers">
                <?php echo $page_links; ?>
            </ul>
        </nav>
        </div>
    <?php
    }
    endif; ?>
    </div>
    <?php
}

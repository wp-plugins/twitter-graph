<?php
/*  Copyright 2011  Lumolink  (email : Jussi Räsänen <jussi.rasanen@lumolink.com>)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
Plugin Name: Twitter Stats
Plugin URI: http://lumolink.com/
Description: Generates graph from twitter
Version: 1.0
Author: Jussi Räsänen <jussi.rasanen@lumolink.com>
Author URI: http://lumolink.com/
License: GPL2
*/

include 'twt.php';

$twtstats_db_version = "1.0";

//
//  Installation code
//
function twtstats_install() {
    global $wpdb;
    global $twtstats_db_version;
    
    $table_name = $wpdb->prefix . "twtstats";
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE  `$table_name` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `followers` INT NOT NULL ,
        `following` INT NOT NULL ,
        `listed` INT NOT NULL ,
        `created` DATETIME NOT NULL
        )";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        add_option("twtstats_db_version", $twtstats_db_version);
    }
}

// 
// Uninstallation code
// 
function twtstats_remove() {
    global $wpdb;
    global $twtstats_db_version;

    $table_name = $wpdb->prefix . "twtstats";

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "DROP TABLE `$table_name`";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        delete_option("twtstats_db_version");
        delete_option("twtstatsusername");
    }
    
}



//
// Admin-page widget code
//
function twtstats_dashboard_widget_function() {
    $twtstatsusername = get_option('twtstatsusername');
    if ($twtstatsusername == null) {
        echo "<p>" . __('You haven\'t configured the plugin yet.', 'twtstats') . "</p>";    
    }
    else {
        $twt = new Twt($twtstatsusername);
        $graphurl = $twt->getGraph();
        $rowcount = $twt->getRowCount();
        
        echo '<div id="twitterstats">';
        if ($rowcount == 1) {
            echo "<h2>" . __('1 day period', 'twtstats');
        }
        else {
            if ($rowcount > 7)
                $rowcount = 7;
            printf("<h2>" . __('%d days period', 'twtstats'), $rowcount);
        }
        echo " &mdash; <a href=\"http://twitter.com/{$twtstatsusername}\">@" . $twtstatsusername ."</a></h2>";
        echo "<img class=\"twtstats\" src=\"" . $graphurl . "\" alt=\"\" />";
        echo '</div>';
        
        if ($rowcount < 7) {
            echo '<p class="twtstatsnote">';
            echo __('Note: It will take <strong>7 days</strong> to fully generate the statistics', 'twtstats');
            echo '</p>';
        }
        
    }
}
function twtstats_add_dashboard_widget() {
	wp_add_dashboard_widget('twtstats_add_dashboard_widget', 'Twitter stats', 'twtstats_dashboard_widget_function');	
} 



//
// Twitter stats main code
//

// Main function
function twtstats() {
    // If we ever need..
}

// Admin menu page
function twtstats_admin_menu() {
    include('twtstats_admin_menu.php');  
}

// Add new options page
function twtstats_admin_menu_actions() {
    add_options_page("Twitter Graph", "Twitter Graph", 1, "Twitter-Graph", "twtstats_admin_menu");
}

// CSS we need. @todo
function twtstats_css() {
	echo '
	<style type="text/css">
	.twtstats {
	    border: 1px solid #e1e1e1;
	    padding: 5px;
	    margin-top: 1em;
	    margin-bottom: 1em;
	}
	.twtstatsnote strong {
	    background-color: pink;
	    padding: 2px;
	}
	</style>
	';
}


function new_install_notice_admin() {
    $twtstatsusername = get_option('twtstatsusername');
    if ($twtstatsusername == null) {
        echo '<div class="updated"><p>' .
        '<strong>'.
        __('Hello!') .
        '</strong> ' .
        __('It seems you haven\'t yet configured Twitter Graph, you can config it ','wpstats') .
        '<a href="' . admin_url( 'options-general.php?page=Twitter-Graph' ) . '">'.__('here', 'wpstats').'</a>' .
        '</p></div>';
    }
}


add_action( 'admin_notices', 'new_install_notice_admin' );

# @todo
register_deactivation_hook( __FILE__, 'twtstats_remove' );

// Installation code
register_activation_hook(__FILE__, 'twtstats_install');

// Plugin initialization
add_action('init','twtstats');

// New admin page
add_action('admin_menu', 'twtstats_admin_menu_actions'); 

// Add CSS
add_action('admin_head', 'twtstats_css');

add_action('wp_dashboard_setup', 'twtstats_add_dashboard_widget' );



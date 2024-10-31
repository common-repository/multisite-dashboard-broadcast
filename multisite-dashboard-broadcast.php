<?php
/*
Plugin Name: Multisite Dashboard Broadcast
Plugin URI: http://wordpress.org/plugins/multisite-dashboard-broadcast/
Description: Place a widget on top of every site's dashboard under the same Multisite installation, containing whatever content the Super Admin writes.
Author: mogita
Version: 0.1
Network: true
Author URI: http://dug.mogita.com/wordpress-plugin-multisite-dashboard-broadcast.eva
*/

/*
Copyright 2013 mogita (http://mogita.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

global $broadcast_message_settings_page, $broadcast_message_settings_page_long;

if ( version_compare($wp_version, '3.0.9', '>') ) {
	$broadcast_message_settings_page = 'settings.php';
	$broadcast_message_settings_page_long = 'network/settings.php';
} else {
	$broadcast_message_settings_page = 'ms-admin.php';
	$broadcast_message_settings_page_long = 'ms-admin.php';
}

//------------------ Hooks ------------------------//

add_action('init', 'broadcast_message_init');
add_action('admin_menu', 'broadcast_message_plug_pages');
add_action('network_admin_menu', 'broadcast_message_plug_pages');
add_action('wp_dashboard_setup', 'add_dashboard_widgets' );
add_action('wp_network_dashboard_setup', 'add_dashboard_widgets' );

//-------------- Core Functions -------------------//

function broadcast_message_init() {

	load_plugin_textdomain('multisite_dashboard_broadcast', false, dirname(plugin_basename(__FILE__)).'/languages');
	
	/*if ( !is_multisite() )
		exit( __('The Multisite Dashboard Broadcast plugin is only compatible with WordPress Multisite.', 'multisite_dashboard_broadcast') );*/
	
}

function dashboard_widget_function() {
	$broadcast_message = get_site_option('broadcast_message');
	echo stripslashes( $broadcast_message );
} 

function add_dashboard_widgets() {
	$broadcast_message_title = get_site_option('broadcast_message_title');
	add_meta_box('dashboard_broadcast_widget', stripslashes($broadcast_message_title), 'dashboard_widget_function', 'dashboard', 'normal', 'high');
} 


function broadcast_message_plug_pages() {
	global $wpdb, $wp_roles, $current_user, $wp_version, $broadcast_message_settings_page, $broadcast_message_settings_page_long;
	if ( version_compare($wp_version, '3.0.9', '>') ) {
		if ( is_network_admin() ) {
			add_submenu_page($broadcast_message_settings_page, __('Dashboard Broadcast', 'multisite_dashboard_broadcast'), __('Dashboard Broadcast', 'multisite_dashboard_broadcast'), 10, 'multisite-dashboard-broadcast', 'broadcast_message_page_output');
		}
	} else {
		if ( is_site_admin() ) {
			add_submenu_page($broadcast_message_settings_page, __('Dashboard Broadcast', 'multisite_dashboard_broadcast'), __('Dashboard Broadcast', 'multisite_dashboard_broadcast'), 10, 'multisite-dashboard-broadcast', 'broadcast_message_page_output');
		}
	}
}

//--------- Super Admin's configuration page ----------//

function broadcast_message_page_output() {
	global $wpdb, $wp_roles, $current_user, $broadcast_message_settings_page, $broadcast_message_settings_page_long;

	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Not enough permission.', 'multisite_dashboard_broadcast') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e(urldecode($_GET['updatedmsg']), 'multisite_dashboard_broadcast') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {

		default:
			$broadcast_message = get_site_option('broadcast_message');
			$broadcast_message_title = get_site_option('broadcast_message_title');
			
			if ( $broadcast_message == 'empty' ) {
				$broadcast_message = '';
			}
			if ( $broadcast_message_title == 'empty' ) {
				$broadcast_message_title = '';
			}
			?>
			<h2><?php _e('Dashboard Broadcast', 'multisite_dashboard_broadcast') ?></h2>
            <form method="post" action="<?php print $broadcast_message_settings_page; ?>?page=multisite-dashboard-broadcast&action=process">
            <table class="form-table">
            	<tr valign="top">
            		<th scope="row"><?php _e('Title', 'multisite_dashboard_broadcast') ?></th>
					<td>
						<textarea name="broadcast_message_title" type="text" rows="1" wrap="soft" id="broadcast_message_title" style="width: 95%;"/><?php echo $broadcast_message_title ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('Text', 'multisite_dashboard_broadcast') ?></th>
           		 	<td>
            			<textarea name="broadcast_message" type="text" rows="10" wrap="soft" id="broadcast_message" style="width: 95%"/><?php echo $broadcast_message ?></textarea>
            			<br /><?php _e('You can use HTML tags.', 'multisite_dashboard_broadcast') ?>
					</td>
				</tr>
            </table>

            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes', 'multisite_dashboard_broadcast') ?>" />
			<input type="submit" name="Reset" value="<?php _e('Reset', 'multisite_dashboard_broadcast') ?>" />
            </p>			
            </form>
			<p class="donate" style="width: 200px;">
				<span><?php _e('If this plugin helps and that makes you feel like inviting me for my favorite sweets, please feel free to <form name="_xclick" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="chrisprc@gmail.com">
<input type="hidden" name="item_name" value="Please enter your donation amount.">
<input type="hidden" name="currency_code" value="USD">
<input type="hidden" name="amount" value="">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form><br />Thank you so much for your support!', 'multisite_dashboard_broadcast') ?></span>
			</p>
			<?php
		break;

		case "process":
			if ( isset( $_POST[ 'Reset' ] ) ) {
				update_site_option( "broadcast_message", "empty" );
				update_site_option( "broadcast_message_title", "empty");
				
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='{$broadcast_message_settings_page}?page=multisite-dashboard-broadcast&updated=true&updatedmsg=" . urlencode(__('Settings cleared.', 'multisite_dashboard_broadcast')) . "';
				</script>
				";
			} else {
				$broadcast_message = $_POST[ 'broadcast_message' ];
				$broadcast_message_title = $_POST[ 'broadcast_message_title' ];
				
				if ( $broadcast_message == '' ) {
					$broadcast_message = 'empty';
				}
				if ( $broadcast_message_title == '' ) {
					$broadcast_message_title = 'empty';
				}
				
				update_site_option( "broadcast_message", stripslashes($broadcast_message) );
				update_site_option( "broadcast_message_title", stripslashes($broadcast_message_title) );
				
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='{$broadcast_message_settings_page}?page=multisite-dashboard-broadcast&updated=true&updatedmsg=" . urlencode(__('Settings saved.', 'multisite_dashboard_broadcast')) . "';
				</script>
				";
			}
		break;
	}
	echo '</div>';
}

<?php
/*
Plugin Name: Down Against SOPA
Plugin URI: http://downagainstsopa.com
Description: Down Against Sopa displays a splash page on your WordPress site January 18 and 23 in protest of the Stop Online Piracy Act. Several configuration options are available.
Version: 1.0.6
Author: Ten-321 Enterprises
Author URI: http://ten-321.com
License: GPL3
*/
/*  Copyright 2012  Ten-321 Enterprises and Chris Tidd  (email : contact@ctidd.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/    
?>
<?php
/**
 * Determine if the SOPA message should be shown and, if so, do it.
 */
function sopa_redirect() {
	/* Don't redirect if this is the admin area - somewhat redundant, but helpful nonetheless */
	if ( is_admin() )
		return;
	
	$sopa_opts = get_sopa_options();
	$blackout_dates = array_map( 'trim', explode( ',', $sopa_opts['blackout_dates'] ) );
	
	if ( ! empty( $sopa_opts['custom_page'] ) && is_page( $sopa_opts['custom_page'] ) )
		return;
	
	$cookiename = 'seen_sopa_blackout';
	if ( array_key_exists( 'cookie_hash', $sopa_opts ) )
		$cookiename .= '_' . $sopa_opts['cookie_hash'];
	
	/* Don't redirect if they've already seen the blackout page this session */
	if ( isset( $_COOKIE ) && array_key_exists( $cookiename, $_COOKIE ) && empty( $sopa_opts['no_cookie'] ) ) {
		/*wp_die( 'The cookie is already set' );*/
		return;
	}
	/* Don't redirect if this isn't the home page or front page */
	if ( ! is_front_page() && ! is_home() && empty( $sopa_opts['all_pages'] ) ) {
		if ( ! empty( $sopa_opts['page_id'] ) && ! is_page( $sopa_opts['page_id'] ) ) {
			/*wp_die( 'This is not the home/front page' );*/
			return;
		}
	}
	
	// On January 23, 2012 redirect traffic to the protest page.
	if ( is_sopa_message_displayed() ) {
		$qs = ! empty( $sopa_opts['continue_to_dest'] ) ? '?redirect_to=' . urlencode( $_SERVER['REQUEST_URI'] ) : '';
		$cookiename = 'seen_sopa_blackout';
		if ( array_key_exists( 'cookie_hash', $sopa_opts ) )
			$cookiename .= '_' . $sopa_opts['cookie_hash'];
		// Meta refresh is the only redirect technique I found consistent enough. It has drawbacks, but it's reliable and simple.
		/*wp_safe_redirect( plugins_url( 'stop-sopa.php', __FILE__ ) );*/
		if ( empty( $sopa_opts['custom_page'] ) && ( empty( $sopa_opts['page_id'] ) || ! is_numeric( $sopa_opts['page_id'] ) ) ) {
			if ( empty( $sopa_opts['no_cookie'] ) )
				setcookie( $cookiename, 1, 0, '/' );
			wp_safe_redirect( plugins_url( 'stop-sopa.php', __FILE__ ) . $qs, 307 );
		} else if ( ! empty( $sopa_opts['custom_page'] ) ) {
			if ( empty( $sopa_opts['no_cookie'] ) )
				setcookie( $cookiename, 1, 0, '/' );
			wp_safe_redirect( get_permalink( $sopa_opts['custom_page'] ) . $qs, 307 );
		} else if ( is_page( $sopa_opts['page_id'] ) ) {
			if ( empty( $sopa_opts['no_cookie'] ) )
				setcookie( $cookiename, 1, 0, '/' );
			include_once( plugin_dir_path( __FILE__ ) . 'stop-sopa.php' );
		} else {
			wp_safe_redirect( get_permalink( $sopa_opts['page_id'] ) . $qs, 307 );
		}
		die();
	}
}
add_action( 'template_redirect', 'sopa_redirect', 99 );

/**
 * Retrieve the options
 */
function get_sopa_options() {
	$sopa_opts = get_option( 'sopa_blackout_dates', '2012-01-23,2012-01-18' );
	if ( ! is_array( $sopa_opts ) )
		$sopa_opts = array( 'blackout_dates' => $sopa_opts );
	
	$sopa_opts = array_merge( array(
		'blackout_dates' => '2012-01-23,2012-01-18',
		'backlinks'      => 0,
		'all_pages'      => 0,
		'no_cookie'      => 0,
		'page_id'        => null,
		'site_link'      => null,
		'nag'            => 1,
		'continue_to_dest' => 0,
		'custom_page'    => 0,
	), $sopa_opts );
	
	return $sopa_opts;
}

/**
 * Determine whether the blackout dates indicate the SOPA message should be displayed
 * @return bool whether or not the current date is in the list of blackout dates
 */
function is_sopa_message_displayed() {
	$sopa_opts = get_sopa_options();
	$blackout_dates = array_map( 'trim', explode( ',', $sopa_opts['blackout_dates'] ) );
	
	$time = @date( "Y-m-d", current_time( 'timestamp' ) );
	return in_array( $time, $blackout_dates );
}

/**
 * Add the SOPA options page to the administration menu
 */
function add_sopa_options_page() {
	add_submenu_page( 'options-general.php', __( 'SOPA Blackout Options' , 'sopa-blackout-plugin'), __( 'SOPA Options' , 'sopa-blackout-plugin'), 'manage_options', 'sopa_options_page', 'sopa_options_page_callback' );
	add_action( 'admin_init', 'register_sopa_options' );
}
add_action( 'admin_menu', 'add_sopa_options_page' );

/**
 * Whitelist the SOPA options and set up the options page
 */
function register_sopa_options() {
	register_setting( 'sopa_options_page', 'sopa_blackout_dates', 'sanitize_sopa_opts' );
	add_settings_section( 'sopa_options_section', __( 'SOPA Blackout Options' , 'sopa-blackout-plugin'), 'sopa_options_section_callback', 'sopa_options_page' );
	add_settings_field( 'sopa_blackout_dates', __( 'Blackout Options:' , 'sopa-blackout-plugin'), 'sopa_options_field_callback', 'sopa_options_page', 'sopa_options_section' );
}

/**
 * Sanitize the updated SOPA options
 * @param array $input the value of the options
 * @return array the sanitized values
 */
function sanitize_sopa_opts( $input ) {
	$input['backlinks'] = array_key_exists( 'backlinks', $input ) && '1' === $input['backlinks'] ? 1 : 0;
	$input['all_pages'] = array_key_exists( 'all_pages', $input ) && '1' === $input['all_pages'] ? 1 : 0;
	$input['no_cookie'] = array_key_exists( 'no_cookie', $input ) && '1' === $input['no_cookie'] ? 1 : 0;
	$input['continue_to_dest'] = array_key_exists( 'continue_to_dest', $input ) && '1' === $input['continue_to_dest'] ? 1 : 0;
	if ( empty( $input['page_id'] ) )
		$input['page_id'] = sopa_create_blank_page();
	if ( empty( $input['custom_page'] ) )
		$input['custom_page'] = 0;
	
	$input['cookie_hash'] = md5( time() );
	if ( array_key_exists( 'nag', $input ) ) {
		switch ( $input['nag'] ) {
			case 0:
			case '0':
			case '':
				$input['nag'] = 0;
				break;
			case 2:
			case '2':
				$input['nag'] = 2;
				break;
			default:
				$input['nag'] = 1;
		}
	} else {
		$input['nag'] = 1;
	}
	
	return $input;
}

/**
 * Create a new blank page to use as the placeholder
 */
function sopa_create_blank_page() {
	return wp_insert_post( array( 
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_title'     => __( 'Stop SOPA' , 'sopa-blackout-plugin'),
		'post_content'   => __( 'This is a placeholder page for this website\'s Stop SOPA message.' , 'sopa-blackout-plugin'),
		'post_type'      => 'page',
		'post_status'    => 'publish',
	) );
}

/**
 * Output the options page HTML
 */
function sopa_options_page_callback() {
	if ( ! current_user_can( 'manage_options' ) )
		wp_die( 'You do not have sufficient permissions to view this page.' );
?>
<div class="wrap">
	<h2><?php _e( 'SOPA Blackout Options' , 'sopa-blackout-plugin') ?></h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'sopa_options_page' ) ?>
    <?php do_settings_sections( 'sopa_options_page' ) ?>
    <p><input type="submit" class="button-primary" value="<?php _e( 'Save Changes' , 'sopa-blackout-plugin') ?>"/></p>
    </form>
</div>
<?php
}

/**
 * Output the message to be displayed at the top of the options section
 */
function sopa_options_section_callback() {
	_e( '<p>Please choose the date(s) on which you would like the SOPA Blackout redirect to occur.</p>' , 'sopa-blackout-plugin');
	_e( '<p><em>Saving these options will reset all of the SOPA cookies, so visitors will see the SOPA message again even if they have already seen it.</em></p>' , 'sopa-blackout-plugin');
}

/**
 * Output the HTML for the options form elements
 */
function sopa_options_field_callback() {
	$sopa_opts = get_sopa_options();
	$blackout_dates = array_map( 'trim', explode( ',', $sopa_opts['blackout_dates'] ) );
	$blackout_dates = implode( ', ', $blackout_dates );
?>
<p><label for="sopa_blackout_dates_dates"><strong><?php _e( 'Blackout dates:' , 'sopa-blackout-plugin') ?></strong></label><br/>
	<input class="widefat" type="text" value="<?php echo $blackout_dates ?>" name="sopa_blackout_dates[blackout_dates]" id="sopa_blackout_dates_dates"/><br />
<em><?php _e( 'Please enter the dates in YYYY-MM-DD format. Separate multiple dates with commas.' , 'sopa-blackout-plugin') ?></em></p>
<p><label for="sopa_hide_backlinks"><strong><?php _e( 'Remove backlinks to plugin sponsors?' , 'sopa-blackout-plugin') ?></strong></label>
	<input type="checkbox" name="sopa_blackout_dates[backlinks]" id="sopa_hide_backlinks" value="1"<?php checked( 1, $sopa_opts['backlinks'] ) ?>/></p>
<p><label for="sopa_all_pages"><strong><?php _e( 'Show the SOPA message to visitors the first time they visit your site, no matter which page they land on?' , 'sopa-blackout-plugin') ?></strong></label>
	<input type="checkbox" name="sopa_blackout_dates[all_pages]" id="sopa_all_pages" value="1"<?php checked( 1, $sopa_opts['all_pages'] ) ?>/><br />
<em><?php _e( 'By default, only the front page and posts "home" page show the SOPA message. If a visitor lands on an internal page, they won\'t see the SOPA message until they visit the home or front page. Check the box above to replace all pages on your site with the message.' , 'sopa-blackout-plugin') ?></em></p><p><em><?php _e( 'If you have the option above checked, but do not check the option below, visitors will only see the message once. Once they click through to visit your site, they will no longer see the SOPA message.' , 'sopa-blackout-plugin') ?></em></p>
<p><label for="sopa_no_cookie"><strong><?php _e( 'Don\'t allow visitors to view the regular site when the SOPA message is active:' , 'sopa-blackout-plugin') ?></strong></label>
	<input type="checkbox" name="sopa_blackout_dates[no_cookie]" id="sopa_no_cookie" value="1"<?php checked( 1, $sopa_opts['no_cookie'] ) ?>/><br />
<em><?php _e( 'By default, after a visitor has seen the SOPA message, all other visits to your site (including clicking the "Continue to site" link) will show the regular content. If you check the box above, they will see the SOPA message every time they visit your site (as long as it\'s active).' , 'sopa-blackout-plugin') ?></em></p>
<p><label for="sopa_blackout_dates[site_link]"><strong><?php _e( 'Link to the following page with the "Continue to site" link' , 'sopa-blackout-plugin') ?></strong></label></<br />
<?php
	wp_dropdown_pages( array(
		'name'             => 'sopa_blackout_dates[site_link]',
		'echo'             => 1,
		'show_option_none' => 'Link to the site home page',
		'selected'         => $sopa_opts['site_link'],
	) );
?>
<p><label for="sopa_blackout_dates[page_id]"><strong><?php _e( 'Use the following page for the SOPA message:' , 'sopa-blackout-plugin') ?></strong></label><br/>
<?php
	$pages = wp_dropdown_pages( array( 
		'name'             => 'sopa_blackout_dates[page_id]',
		'echo'             => 0,
		'show_option_none' => 'Create a new page (recommended)',
		'selected'         => $sopa_opts['page_id'],
	) );
	$pages = str_replace( '</select>', '<option value="redirect"' . selected( $sopa_opts['page_id'], 'redirect', false ) . '>Redirect to the PHP file (not recommended)</option></select>', $pages );
	echo $pages;
?>
    </select><br />
<em><?php _e( 'This page will be used as a placeholder for the SOPA message. If anyone tries to visit a page that is supposed to redirect to the SOPA message, they will be redirected to the address of the page selected above, and the Stop SOPA message will be displayed there.</em></p><p><em>If you choose "Create a new page", a new blank page will automatically be created with a title of "Stop SOPA". That page will be excluded automatically from any calls to wp_list_pages() and will be automatically removed when the plugin is deactivated.' , 'sopa-blackout-plugin') ?></em></p>
<p><label for="sopa_nag"><strong><?php _e( 'Display an admin notice about this plugin?' ) ?></strong></label><br/>
	<select name="sopa_blackout_dates[nag]" id="sopa_nag" class="widefat">
    	<option value="0"<?php selected( $sopa_opts['nag'], 0 ) ?>><?php _e( 'Never display an admin notice' , 'sopa-blackout-plugin' ) ?></option>
        <option value="1"<?php selected( $sopa_opts['nag'], 1 ) ?>><?php _e( 'Display a notice only when the SOPA message is being displayed' , 'sopa-blackout-plugin' ) ?></option>
        <option value="2"<?php selected( $sopa_opts['nag'], 2 ) ?>><?php _e( 'Display a notice the whole time this plugin is activated' , 'sopa-blackout-plugin' ) ?></option>
    </select><br/>
    <em><?php _e( 'The admin notice will include links to more information about SOPA to help you keep up with news about the bill. When the SOPA message is being displayed, the admin notice will indicate that, and will include information about when the SOPA message is displayed to visitors.' , 'sopa-blackout-plugin' ) ?></em></p>
<p><label for="continue_to_dest"><strong><?php _e( 'Make the "Continue to site" link lead to the visitor\'s original destination, instead of the page indicated above?' , 'sopa-blackout-plugin' ) ?></strong></label>
	<input type="checkbox" name="sopa_blackout_dates[continue_to_dest]" id="continue_to_dest" value="1"<?php checked( $sopa_opts['continue_to_dest'], 1 ) ?>/></p>
<p><label for="sopa_custom_page"><strong><?php _e( 'Use the following page as a custom SOPA message instead of the one included in this plugin?', 'sopa-blackout-plugin' ) ?></strong></label><br/>
<?php
	wp_dropdown_pages( array(
		'name'             => 'sopa_blackout_dates[custom_page]',
		'echo'             => 1,
		'show_option_none' => 'Use the included SOPA message',
		'selected'         => $sopa_opts['custom_page'],
	) );
?>
</p>
<?php
}

/**
 * Attempt to keep the blank SOPA placeholder page from showing up in 
 * 		auto-generated menus
 */
function exclude_sopa_page( $excludes ) {
	$sopa_opts = get_sopa_options();
	
	if ( ! empty( $sopa_opts['page_id'] ) && __( 'Stop SOPA' , 'sopa-blackout-plugin') == get_the_title( $sopa_opts['page_id'] ) )
		$excludes[] = $sopa_opts['page_id'];
	
	return $excludes;
}
add_filter( 'wp_list_pages_excludes', 'exclude_sopa_page', 99 );

/**
 * Perform deactivation actions
 * Remove the placeholder page if the user created a new page for this plugin
 * Delete the options from the database
 */
function remove_sopa_placeholder() {
	$sopa_opts = get_sopa_options();
	
	if ( ! empty( $sopa_opts['page_id'] ) && __( 'Stop SOPA' , 'sopa-blackout-plugin') == get_the_title( $sopa_opts['page_id'] ) )
		wp_delete_post( $sopa_opts['page_id'], true );
	
	delete_option( 'sopa_blackout_dates' );
}
register_deactivation_hook( __FILE__, 'remove_sopa_placeholder' );

function load_down_sopa_textdomain() {
	load_plugin_textdomain( 'sopa-blackout-plugin', false, 'sopa-blackout-plugin/languages' );
}
add_action( 'init', 'load_down_sopa_textdomain' );

/**
 * Display the admin notice if the options indicate to do so
 */
function sopa_admin_nag() {
	$sopa_opts = get_sopa_options();
	
	if ( empty( $sopa_opts['nag'] ) )
		return;
	
	$pages = empty( $sopa_opts['all_pages'] ) ? 'your home page' : 'all pages on your site';
	$visits = empty( $sopa_opts['no_cookie'] ) ? 'their first visit' : 'all visits';
	
	if ( 1 == $sopa_opts['nag'] ) {
		if ( ! is_sopa_message_displayed() )
			return;
		
		$msg = sprintf( __( 'The SOPA message is currently being displayed to visitors of %s on %s to your site.' , 'sopa-blackout-plugin' ), $pages, $visits );
	} else {
		$blackout_dates = array_map( 'trim', explode( ',', $sopa_opts['blackout_dates'] ) );
		sort( $blackout_dates );
		$dates = array();
		$form = get_option( 'date_format' );
		foreach ( $blackout_dates as $d ) {
			if ( ! strtotime( $d ) )
				continue;
			$dates[] = date( $form, strtotime( $d ) );
		}
		switch ( count( $dates ) ) {
			case 0:
				$blackout_dates = '[no dates specified]';
				break;
			case 1:
				$blackout_dates = implode( ', ', $dates );
				break;
			case 2:
				$blackout_dates = implode( ' and ', $dates );
				break;
			default:
				$last_date = array_pop( $dates );
				$blackout_dates = implode( ', ', $dates ) . ' and ' . $last_date;
		}
		$msg = sprintf( __( 'The SOPA Blackout Plugin (Down Against SOPA) is activated on your site. It is currently set up to show the SOPA message to visitors of %s on %s to your site during the following dates: %s.' , 'sopa-blackout-plugin' ), $pages, $visits, $blackout_dates );
	}
	
	printf( __( '<div class="updated fade"><p>%s</p><p>For more current SOPA information, please feel free to visit <a href="%s">the Down Against SOPA</a> website.</p></div>', 'sopa-blackout-plugin' ), $msg, 'http://www.sopawpblackout.com/' );
}
add_action( 'admin_notices', 'sopa_admin_nag' );
?>

<?php
/*
 * Plugin Name: CUNYJ BuddyPress Admin Bar
 * Version: 0.1
 * Plugin URI: http://journalism.cuny.edu
 * Description: Customized Admin Bar up in the heezy
 * Author: Daniel Bachhuber
 * Author URI: http://www.danielbachhuber.com/
 */

class cunyj_buddypress
{
	
	function __construct() {
		
		// Initialize the plugin after everything has been loaded so that the nav elements
		// are removed properly
		add_action('init', array(&$this, 'init'));
		
	}
	
	function init() {
		
		global $wpdb;
		
		remove_action( 'bp_adminbar_logo', 'bp_adminbar_logo' );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_login_menu', 2 );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_account_menu', 4 );	
		
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_authors_menu', 12 );	
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_random_menu', 100 );
		
		
		
		add_action( 'bp_adminbar_menus', array(&$this, 'activity'), 1 );
		add_action( 'bp_adminbar_menus', array(&$this, 'groups'), 7 );
		add_action( 'bp_adminbar_menus', array(&$this, 'authors'), 12 );
		add_action( 'bp_adminbar_menus', array(&$this, 'profile'), 100 );
		add_action( 'bp_adminbar_menus', array(&$this, 'login_menu'), 100 );
		add_action( 'bp_adminbar_menus', array(&$this, 'alert_message'), 100);
		
	}
	
	// **** "Log In" and "Sign Up" links (Visible when not logged in) ********
	function login_menu() {
		global $bp;

		if ( is_user_logged_in() )
			return false;

		echo '<li class="bp-login no-arrow align-right"><a href="' . $bp->root_domain . '/wp-login.php?redirect_to=' . urlencode( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] ) . '">' . __( 'Log In', 'buddypress' ) . '</a></li>';

		// Show "Sign Up" link if user registrations are allowed
		if ( bp_get_signup_allowed() ) {
			echo '<li class="bp-signup no-arrow"><a href="' . bp_get_signup_page(false) . '">' . __( 'Sign Up', 'buddypress' ) . '</a></li>';
		}
	}
	
	function activity() {
		global $bp;
		
		if (!is_user_logged_in()) {
			return false;
		}
		echo '<li id="bp-adminbar-activity" class="no-arrow"><a href="' . $bp->root_domain . '/activity/">Network Activity</a></li>';
		
	}
	
	function alert_message() {
		global $bp, $wpdb;
		
		if (is_user_logged_in()) {
			return false;
		}
		
		echo '<li id="bp-adminbar-activity" class="no-arrow"><a href="http://wiki.journalism.cuny.edu/Getting%20a%20Fast%20Start">Getting a Fast Start: CLASS OF 2011</a></li>';
		
	}
	
	function groups() {
		global $bp;
		
		if (!is_user_logged_in()) {
			return false;
		}
		
		if ( function_exists( 'groups_install' ) ) {
			
			//$groups = groups_get_groups_for_user( $bp->loggedin_user->id, true );
			
			if ( bp_has_groups() ) :
				
				echo '<li id="bp-adminbar-groups-menu"><a href="' . $bp->loggedin_user->domain . $bp->groups->slug . '/my-groups">';

				_e ( 'Groups', 'buddypress');

				echo '</a><ul>';
				
				while ( bp_groups() ) : bp_the_group();
				
					echo '<li><a href="';
					bp_group_permalink();
					echo '">';
					bp_group_name();
					echo '</a></li>';
				
				endwhile;
		
				
				echo '</ul></li>';
				
			else :
				
			endif;
		
		}
		
		//$counter = 0;
		//foreach( (array)$bp->bp_nav as $nav_item ) {
		
	}
	
	function authors() {
		global $bp, $current_blog, $wpdb;

		if ( $current_blog->blog_id == BP_ROOT_BLOG || !function_exists( 'bp_blogs_install' ) || !is_user_logged_in())
			return false;

		$blog_prefix = $wpdb->get_blog_prefix( $current_blog->id );
		$authors = $wpdb->get_results( "SELECT user_id, user_login, user_nicename, display_name, user_email, meta_value as caps FROM $wpdb->users u, $wpdb->usermeta um WHERE u.ID = um.user_id AND meta_key = '{$blog_prefix}capabilities' ORDER BY um.user_id" );

		if ( !empty( $authors ) ) {
			/* This is a blog, render a menu with links to all authors */
			echo '<li id="bp-adminbar-authors-menu"><a href="/">';
			_e('Blog Authors', 'buddypress');
			echo '</a>';

			echo '<ul class="author-list">';
			foreach( (array)$authors as $author ) {
				$caps = maybe_unserialize( $author->caps );
				if ( isset( $caps['subscriber'] ) || isset( $caps['contributor'] ) ) continue;

				echo '<li>';
				echo '<a href="' . bp_core_get_user_domain( $author->user_id, $author->user_nicename, $author->user_login ) . '">';
				echo bp_core_fetch_avatar( array( 'item_id' => $author->user_id, 'email' => $author->user_email, 'width' => 15, 'height' => 15 ) ) ;
	 			echo ' ' . $author->display_name . '</a>';
				echo '<div class="admin-bar-clear"></div>';
				echo '</li>';
			}
			echo '</ul>';
			echo '</li>';
		}
	}
	
	function profile() {
		global $bp;
		
		if ( !$bp->bp_nav || !is_user_logged_in() ) {
			return false;
		}
		
		$current_user = wp_get_current_user();
		
		echo '<li class="align-right" id="bp-adminbar-account-menu"><a href="' . bp_loggedin_user_domain() . '">';
		echo __( 'My Profile', 'buddypress' ) . '</a>';
		echo '<ul>';
		
		echo '<li><a href="' . bp_loggedin_user_domain() . '">';
		echo $current_user->display_name;
		echo '</a></li>';
		
		/* Loop through each navigation item */
		$counter = 0;
		foreach( (array)$bp->bp_nav as $nav_item ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';
			
			$ignore = (array('Activity', 'Profile'));
			
			if (in_array($nav_item['name'], $ignore)) {
				continue;
			}

			echo '<li' . $alt . '>';
			echo '<a id="bp-admin-' . $nav_item['css_id'] . '" href="' . $nav_item['link'] . '">' . $nav_item['name'] . '</a>';

			echo '</li>';

			$counter++;
		}
		
		echo '<li' . $alt . '><a id="bp-admin-logout" class="logout" href="' . wp_logout_url( site_url() ) . '">' . __( 'Log Out', 'buddypress' ) . '</a></li>';
		
		echo '</ul></li>';
		
	}
	
}

global $cunyj_buddypress;
$cunyj_buddypress = new cunyj_buddypress;


?>
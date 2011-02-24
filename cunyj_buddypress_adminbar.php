<?php
/*
 * Plugin Name: CUNYJ BuddyPress Admin Bar
 * Version: 0.3.3
 * Plugin URI: http://journalism.cuny.edu
 * Description: Customized Admin Bar up in the heezy
 * Author: Daniel Bachhuber
 * Author URI: http://www.danielbachhuber.com/
 */

define( 'CUNYJ_BUDDYPRESS_ADMIN_BAR_VERSION', "0.3.3" );

class cunyj_buddypress
{
	
	function __construct() {
		
		// Initialize the plugin after everything has been loaded so that the nav elements
		// are removed properly
		add_action('init', array(&$this, 'init'));
		
	}
	
	function init() {
		
		global $wpdb;
		
		$plugin_dir = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		
		// All of the actions to unset
		remove_action( 'bp_adminbar_logo', 'bp_adminbar_logo' );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_login_menu', 2 );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_account_menu', 4 );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_blogs_menu', 6 );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_notifications_menu', 8 );		
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_authors_menu', 12 );	
		remove_action( 'bp_adminbar_menus', 'groups_setup_adminbar_menu', 20 );
		remove_action( 'bp_adminbar_menus', 'bp_adminbar_random_menu', 100 );
		
		// Our new glorious navigation bar
		add_action( 'bp_adminbar_menus', array(&$this, 'logo'), 1 );		
		add_action( 'bp_adminbar_menus', array(&$this, 'activity'), 1 );
		add_action( 'bp_adminbar_menus', array(&$this, 'blogs'), 6 );
		add_action( 'bp_adminbar_menus', array(&$this, 'groups'), 7 );
		add_action( 'bp_adminbar_menus', array(&$this, 'members'), 8 );
		add_action( 'bp_adminbar_menus', 'bp_adminbar_notifications_menu', 15 );
		//add_action( 'bp_adminbar_menus', array(&$this, 'authors'), 12 );
		add_action( 'bp_adminbar_menus', array(&$this, 'profile'), 100 );
		add_action( 'bp_adminbar_menus', array(&$this, 'login_menu'), 100 );
		add_action( 'bp_adminbar_menus', array(&$this, 'alert_message'), 100);
		
		
		wp_enqueue_style( 'cunyj-buddypress-adminbar', $plugin_dir . 'css/style.css', null,  CUNYJ_BUDDYPRESS_ADMIN_BAR_VERSION );
		
		if ( !is_admin() ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'cunyj-buddypress-adminbar', $plugin_dir . 'js/cunyj_buddypress_adminbar.js', array( 'jquery' ), CUNYJ_BUDDYPRESS_ADMIN_BAR_VERSION, true );
		}
		
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
	
	function logo() {
		global $bp;
	
		echo '<li id="bp-adminbar-logo" class="no-arrow"><a href="' . $bp->root_domain . '/">CUNY J-School</a></li>';
		
	}
	
	function activity() {
		global $bp;
		
		if (!is_user_logged_in()) {
			return false;
		}
		echo '<li id="bp-adminbar-activity" class="no-arrow"><a href="' . $bp->root_domain . '/activity/">Activity</a></li>';
		
	}
	
	function alert_message() {
		global $bp, $wpdb;
		
		if (is_user_logged_in()) {
			return false;
		}
		
		echo '<li id="bp-adminbar-activity" class="no-arrow"><a href="http://wiki.journalism.cuny.edu/Getting%20a%20Fast%20Start">Getting a Fast Start: CLASS OF 2011</a></li>';
		
	}
	
	// *** "My Blogs" Menu ********
	function blogs() {
		global $bp;

		if ( !is_user_logged_in() || !function_exists('bp_blogs_install') )
			return false;

		if ( !$blogs = wp_cache_get( 'bp_blogs_of_user_' . $bp->loggedin_user->id . '_inc_hidden', 'bp' ) ) {
			$blogs = bp_blogs_get_blogs_for_user( $bp->loggedin_user->id, true );
			wp_cache_set( 'bp_blogs_of_user_' . $bp->loggedin_user->id . '_inc_hidden', $blogs, 'bp' );
		}

		echo '<li id="bp-adminbar-blogs-menu"><a href="' . bp_get_root_domain() . '/blogs/">';

		_e( 'Blogs', 'buddypress' );

		echo '</a>';
		echo '<ul>';

		if ( is_array( $blogs['blogs'] ) && (int)$blogs['count'] ) {
			$counter = 0;
			foreach ( (array)$blogs['blogs'] as $blog ) {
				$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';
				$site_url = esc_attr( $blog->siteurl );

				echo '<li' . $alt . '>';
				echo '<a href="' . $site_url . '">' . esc_html( $blog->name ) . '</a>';

				echo '<ul>';
				echo '<li class="alt"><a href="' . $site_url . 'wp-admin/">' . __( 'Dashboard', 'buddypress' ) . '</a></li>';
				echo '<li><a href="' . $site_url . 'wp-admin/post-new.php">' . __( 'New Post', 'buddypress' ) . '</a></li>';
				echo '<li class="alt"><a href="' . $site_url . 'wp-admin/edit.php">' . __( 'Manage Posts', 'buddypress' ) . '</a></li>';
				echo '<li><a href="' . $site_url . 'wp-admin/edit-comments.php">' . __( 'Manage Comments', 'buddypress' ) . '</a></li>';
				echo '</ul>';

				echo '</li>';
				$counter++;
			}
		}

		$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';

		if ( bp_blog_signup_enabled() ) {
			echo '<li' . $alt . '>';
			echo '<a href="' . $bp->root_domain . '/' . $bp->blogs->slug . '/create/">' . __( 'Create a Blog!', 'buddypress' ) . '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</li>';
	}
	
	function groups() {
		global $bp, $groups_template;
		
		if ( !is_user_logged_in() || !function_exists( 'groups_install' ) ) {
			return false;
		}
		
		$groups = groups_get_user_groups( $bp->loggedin_user->id );
		
		if ( count( $groups['groups'] ) ) {
			
			echo '<li id="bp-adminbar-groups-menu"><a href="' . bp_get_root_domain() . '/' . BP_GROUPS_SLUG .'/">';

			_e ( 'Groups', 'buddypress');

			echo '</a><ul>';
			
			foreach ( $groups['groups'] as $group_id ) {
				
				$group = groups_get_group( 'group_id=' . $group_id );
			
				echo '<li><a href="';
				echo bp_get_root_domain() . '/' . BP_GROUPS_SLUG .'/' . $group->slug . '/';
				echo '">';
				echo $group->name;
				echo '</a></li>';
			
			}
	
			echo '</ul></li>';
			
		}
		
	}
	
	function members() {
		global $bp, $wpdb;
		
			if (!is_user_logged_in()) {
				return false;
			}
		
		echo '<li id="bp-adminbar-members-link" class="no-arrow"><a href="' . bp_get_root_domain() . '/' . BP_MEMBERS_SLUG .'">Members</a></li>';
		
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
		echo bp_loggedin_user_avatar( 'width=22&height=22' );
		echo $current_user->display_name . '</a>';
		echo '<ul>';
		
		/* Loop through each navigation item */
		$counter = 0;
		foreach( (array)$bp->bp_nav as $nav_item ) {
			$alt = ( 0 == $counter % 2 ) ? ' class="alt"' : '';
			
			$ignore = array( 'activity', 'groups', 'friends', 'blogs', 'settings' );
			
			if (in_array($nav_item['slug'], $ignore)) {
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
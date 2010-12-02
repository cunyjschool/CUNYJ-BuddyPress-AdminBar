jQuery(document).ready(function() {

	/**
	 * Insert styles if the admin bar is there. Solves problem with cache conflict
	 */
	if ( jQuery('#wp-admin-bar').length > 0 ) {
		jQuery('body').css('padding-top', '30px');
		jQuery('body').css('background-position', '0px 30px');	
	} else {
		jQuery('body').css('padding-top', '0px');
	}
	
});
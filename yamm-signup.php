<?php

if( is_array( get_site_option( 'illegal_names' )) && $_GET[ 'new' ] != '' && in_array( $_GET[ 'new' ], get_site_option( 'illegal_names' ) ) == true ) {
	wp_redirect( "http://{$current_site->domain}{$current_site->path}" );
	die();
}

if( $current_blog->domain . $current_blog->path != $current_site->domain . $current_site->path ) {
	wp_redirect( "http://" . $current_site->domain . $current_site->path . "signup" );
	die();
}

get_header();
?>
<div id="content" class="widecolumn">
<div class="mu_register">
<?php

// Main
$active_signup = get_site_option( 'registration' );
if( !$active_signup )
	$active_signup = 'all';

$active_signup = apply_filters( 'wpmu_active_signup', $active_signup ); // return "all", "none", "blog" or "user"

if( is_site_admin() )
	echo '<div class="mu_alert">' . sprintf( __( "Greetings Site Administrator! You are currently allowing '%s' registrations. To change or disable registration go to your <a href='wp-admin/wpmu-options.php'>Options page</a>." ), $active_signup ) . '</div>';

$newblogname = isset($_GET['new']) ? strtolower(preg_replace('/^-|-$|[^-a-zA-Z0-9]/', '', $_GET['new'])) : null;

$current_user = wp_get_current_user();
if( $active_signup == "none" ) {
	_e( "Registration has been disabled." );
} elseif( $active_signup == 'blog' && !is_user_logged_in() ){
	if( is_ssl() ) {
		$proto = 'https://';
	} else {
		$proto = 'http://';
	}
	$login_url = site_url( 'wp-login.php?redirect_to=' . urlencode($proto . $_SERVER['HTTP_HOST'] . '/signup' ));
	echo sprintf( __( "You must first <a href=\"%s\">login</a>, and then you can create a new blog."), $login_url );
} else {
	switch ($_POST['stage']) {
		case 'validate-user-signup' :
			if( $active_signup == 'all' || $_POST[ 'signup_for' ] == 'blog' && $active_signup == 'blog' || $_POST[ 'signup_for' ] == 'user' && $active_signup == 'user' )
				yamm_validate_user_signup();
			else
				_e( "User registration has been disabled." );
		break;
		case 'validate-blog-signup':
			if( $active_signup == 'all' || $active_signup == 'blog' )
				yamm_validate_blog_signup();
			else
				_e( "Blog registration has been disabled." );
			break;
		case 'gimmeanotherblog':
			yamm_validate_another_blog_signup();
			break;
		default :
			$user_email = $_POST[ 'user_email' ];
			do_action( "preprocess_signup_form" ); // populate the form from invites, elsewhere?
			if ( is_user_logged_in() && ( $active_signup == 'all' || $active_signup == 'blog' ) ) {
				yamm_signup_another_blog($newblogname);
			} elseif( is_user_logged_in() == false && ( $active_signup == 'all' || $active_signup == 'user' ) ) {
				yamm_signup_user( $newblogname, $user_email );
			} elseif( is_user_logged_in() == false && ( $active_signup == 'blog' ) ) {
				_e( "I'm sorry. We're not accepting new registrations at this time." );
			} else {
				_e( "You're logged in already. No need to register again!" );
			}
			if ($newblogname) {
				if( constant( "VHOST" ) == 'no' )
					$newblog = 'http://' . $current_site->domain . $current_site->path . $newblogname . '/';
				else
					$newblog = 'http://' . $newblogname . '.' . $current_site->domain . $current_site->path;
				if ($active_signup == 'blog' || $active_signup == 'all')
					printf(__("<p><em>The blog you were looking for, <strong>%s</strong> doesn't exist but you can create it now!</em></p>"), $newblog );
				else
					printf(__("<p><em>The blog you were looking for, <strong>%s</strong> doesn't exist.</em></p>"), $newblog );
			}
			break;
	}
}
?>
</div>
</div>

<?php get_footer(); ?>
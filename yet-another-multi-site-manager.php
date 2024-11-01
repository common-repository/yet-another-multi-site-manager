<?php
/*
Plugin Name: Yet Another Multi-Site Manager
Plugin URI: http://joejacobs.org/software/yet-another-multi-site-manager/
Description: Allows blogs to be created on multiple domain names while maintaining only one main site for all domain names.
Version: 0.1.1
Author: Joe Jacobs
Author URI: http://joejacobs.org/
Site Wide Only: true
*/
/*  Copyright 2009 Joe Jacobs (email : joe@hazardcell.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function yamm_variables() {
	global $yamm_signup_slug, $yamm_active, $yamm_domains, $domain;

	if ( !isset( $yamm_signup_slug ) ) {
		$yamm_signup_slug = get_site_option( 'yamm_signup_slug' );

		//if the plugin was activated with the "Activate Site Wide" link, the activation hook would not have been launched
		//if the slug value is empty that most probably means the plugin was not installed properly
		//to fix that we will run the activation function to complete the installation
		if ( empty( $yamm_signup_slug ) ) {
			yamm_activate();
			$yamm_signup_slug = 'signup';
		}	
	}

	if ( !isset( $yamm_active ) )
		$yamm_active = get_site_option( 'yamm_active' );

	if ( 1 == $yamm_active )
		$yamm_domains = get_site_option( 'yamm_domains' );

	if ( isset( $_POST[ 'yamm_site' ] ) )
		$domain = $_POST[ 'yamm_site' ];

}
add_action( 'plugins_loaded', 'yamm_variables' );

function yamm_catch_signup_slug () {
	global $yamm_signup_slug, $current_site;

	if ( preg_match( '|^/' . $yamm_signup_slug . '|', $_SERVER[ 'REQUEST_URI' ] ) ) {
		global $current_blog;

		add_action( 'wp_head', 'yamm_do_signup_header' );
		add_action( 'wp_head', 'yamm_signuppageheaders' ) ;
		require_once( ABSPATH . WPINC . '/registration.php' );
		add_action( 'wp_head', 'yamm_wpmu_signup_stylesheet' );

		require_once( dirname(__FILE__) . '/yamm-signup.php' );
		exit;
	} elseif ( preg_match( '|^/wp-signup.php|', $_SERVER[ 'REQUEST_URI' ] ) ) {
		wp_redirect( "http://{$current_site->domain}{$current_site->path}{" . preg_replace( '|^/wp-signup.php|', $yamm_signup_slug, $_SERVER[ 'REQUEST_URI' ] ) . "}" );
		die();
	}
}
add_action( 'init', 'yamm_catch_signup_slug' );

function yamm_signup_field() {
	global $current_site, $wpdb, $yamm_active, $yamm_domains;

	$yamm_signup_field = '<span class="suffix_address">.' . $current_site->domain . $current_site->path . '</span>';

	if ( 1 == $yamm_active && constant( "VHOST" ) == 'yes' ) {
		$yamm_signup_field = '<span class="suffix_address">.</span><select id="yamm_domains" name="yamm_site"><option value="' . $current_site->domain . '">' . $current_site->domain . '</option>';

		foreach( $yamm_domains as $site ) {
			$yamm_signup_field .= '<option value="' . $site . '">' . $site . '</option>';
		}

		$yamm_signup_field .= '</select>';
	}

	return $yamm_signup_field;
}

function yamm_do_signup_header() {
	do_action("signup_header");
}

function yamm_signuppageheaders() {
	echo "<meta name='robots' content='noindex,nofollow' />\n";
}

function yamm_wpmu_signup_stylesheet() {
	?>
	<style type="text/css">	
		.mu_register { width: 90%; margin:0 auto; }
		.mu_register form { margin-top: 2em; }
		.mu_register .error { font-weight:700; padding:10px; color:#333333; background:#FFEBE8; border:1px solid #CC0000; }
		.mu_register #submit,
			.mu_register #blog_title,
			.mu_register #user_email, 
			.mu_register #user_name { width:100%; font-size: 24px; margin:5px 0; }	
		.mu_register #blogname,
			.mu_register #yamm_domains { width:48%; font-size: 24px; margin:5px 0; }	
		.mu_register .prefix_address,
			.mu_register .suffix_address {font-size: 18px;display:inline; }			
		.mu_register label { font-weight:700; font-size:15px; display:block; margin:10px 0; }
		.mu_register label.checkbox { display:inline; }
		.mu_register .mu_alert { font-weight:700; padding:10px; color:#333333; background:#ffffe0; border:1px solid #e6db55; }
	</style>
	<?php
}

function yamm_show_blog_form($blogname = '', $blog_title = '', $errors = '') {
	global $current_site;
	// Blog name
	if( constant( "VHOST" ) == 'no' )
		echo '<label for="blogname">' . __('Blog Name:') . '</label>';
	else
		echo '<label for="blogname">' . __('Blog Domain:') . '</label>';

	if ( $errmsg = $errors->get_error_message('blogname') ) { ?>
		<p class="error"><?php echo $errmsg ?></p>
	<?php }

	if( constant( "VHOST" ) == 'no' ) {
		echo '<span class="prefix_address">' . $current_site->domain . $current_site->path . '</span><input name="blogname" type="text" id="blogname" value="'.$blogname.'" maxlength="50" /><br />';
	} else {
		/* BEGIN REGPLUS MOD */ echo '<input name="blogname" type="text" id="blogname" value="'.$blogname.'" maxlength="50" />' . yamm_signup_field() . '<br />'; /* END REGPLUS MOD */ 
	}
	/* BEGIN REGPLUS MOD */
	/*if ( !is_user_logged_in() ) {
		print '(<strong>' . __( 'Your address will be ' );
		if( constant( "VHOST" ) == 'no' ) {
			print $current_site->domain . $current_site->path . __( 'blogname' );
		} else {
			print __( 'domain.' ) . $current_site->domain . $current_site->path;
		}
		echo '.</strong> ' . __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed so choose carefully!)' ) . '</p>';
	}*/

	if ( !is_user_logged_in() ) {
		echo '(' . __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed so choose carefully!)' ) . '</p>';
	}
	/* END REGPLUS MOD */

	// Blog Title
	?>
	<label for="blog_title"><?php _e('Blog Title:') ?></label>	
	<?php if ( $errmsg = $errors->get_error_message('blog_title') ) { ?>
		<p class="error"><?php echo $errmsg ?></p>
	<?php }
	echo '<input name="blog_title" type="text" id="blog_title" value="'.wp_specialchars($blog_title, 1).'" /></p>';
	?>

	<p>
		<label for="blog_public_on"><?php _e('Privacy:') ?></label>
		<?php _e('I would like my blog to appear in search engines like Google and Technorati, and in public listings around this site.'); ?> 
		<div style="clear:both;"></div>
		<label class="checkbox" for="blog_public_on">
			<input type="radio" id="blog_public_on" name="blog_public" value="1" <?php if( !isset( $_POST['blog_public'] ) || $_POST['blog_public'] == '1' ) { ?>checked="checked"<?php } ?> />
			<strong><?php _e( 'Yes' ); ?></strong>
		</label>
		<label class="checkbox" for="blog_public_off">
			<input type="radio" id="blog_public_off" name="blog_public" value="0" <?php if( isset( $_POST['blog_public'] ) && $_POST['blog_public'] == '0' ) { ?>checked="checked"<?php } ?> />
			<strong><?php _e( 'No' ); ?></strong>
		</label>
	</p>
	
	<?php
	do_action('signup_blogform', $errors);
}

function yamm_validate_blog_form() {
	global $yamm_active;

	$user = '';
	if ( is_user_logged_in() )
		$user = wp_get_current_user();

	if ( 1 == $yamm_active ) {
		$yamm_domain = $domain;
		$domain = $_POST[ 'yamm_site' ];

		$return = wpmu_validate_blog_signup($_POST['blogname'], $_POST['blog_title'], $user);

		$domain = $yamm_domain;

		return $return;
	} else {
		return wpmu_validate_blog_signup($_POST['blogname'], $_POST['blog_title'], $user);
	}
}

function yamm_show_user_form($user_name = '', $user_email = '', $errors = '') {
	// User name
	echo '<label for="user_name">' . __('Username:') . '</label>';
	if ( $errmsg = $errors->get_error_message('user_name') ) {
		echo '<p class="error">'.$errmsg.'</p>';
	}
	echo '<input name="user_name" type="text" id="user_name" value="'.$user_name.'" maxlength="50" /><br />';
	_e('(Must be at least 4 characters, letters and numbers only.)');
	?>

	<label for="user_email"><?php _e('Email&nbsp;Address:') ?></label>
	<?php if ( $errmsg = $errors->get_error_message('user_email') ) { ?>
		<p class="error"><?php echo $errmsg ?></p>
	<?php } ?>		
	<input name="user_email" type="text" id="user_email" value="<?php  echo wp_specialchars($user_email, 1) ?>" maxlength="200" /><br /><?php _e('(We&#8217;ll send your password to this address, so <strong>triple-check it</strong>.)') ?>
	<?php
	if ( $errmsg = $errors->get_error_message('generic') ) {
		echo '<p class="error">'.$errmsg.'</p>';
	}
	do_action( 'signup_extra_fields', $errors );
}

function yamm_validate_user_form() {
	return wpmu_validate_user_signup($_POST['user_name'], $_POST['user_email']);
}

function yamm_signup_another_blog($blogname = '', $blog_title = '', $errors = '') {
	global $current_user, $current_site;
	
	if ( ! is_wp_error($errors) ) {
		$errors = new WP_Error();
	}

	// allow definition of default variables
	$filtered_results = apply_filters('signup_another_blog_init', array('blogname' => $blogname, 'blog_title' => $blog_title, 'errors' => $errors ));
	$blogname = $filtered_results['blogname'];
	$blog_title = $filtered_results['blog_title'];
	$errors = $filtered_results['errors'];

	echo '<h2>' . sprintf( __('Get <em>another</em> %s blog in seconds'), $current_site->site_name ) . '</h2>';

	if ( $errors->get_error_code() ) {
		echo "<p>" . __('There was a problem, please correct the form below and try again.') . "</p>";
	}
	?>
	<p><?php printf(__("Welcome back, %s. By filling out the form below, you can <strong>add another blog to your account</strong>. There is no limit to the number of blogs you can have, so create to your heart's content, but blog responsibly."), $current_user->display_name) ?></p>
	
	<?php
	$blogs = get_blogs_of_user($current_user->ID);	
	if ( !empty($blogs) ) { ?>
		<p>
			<?php _e('Blogs you are already a member of:') ?>
			<ul>
				<?php foreach ( $blogs as $blog ) {
					echo "<li><a href='http://" . $blog->domain . $blog->path . "'>" . $blog->domain . $blog->path . "</a></li>";
				} ?>
			</ul>
		</p>
	<?php } ?>
	
	<p><?php _e("If you&#8217;re not going to use a great blog domain, leave it for a new user. Now have at it!") ?></p>
	<form id="setupform" method="post" action="signup">
		<input type="hidden" name="stage" value="gimmeanotherblog" />
		<?php do_action( "signup_hidden_fields" ); ?>
		<?php yamm_show_blog_form($blogname, $blog_title, $errors); ?>
		<p>
			<input id="submit" type="submit" name="submit" class="submit" value="<?php _e('Create Blog &raquo;') ?>" /></p>
	</form>
	<?php
}

function yamm_validate_another_blog_signup() {
	global $wpdb, $current_user, $blogname, $blog_title, $errors, $domain, $path;
	$current_user = wp_get_current_user();
	if( !is_user_logged_in() )
		die();

	$result = yamm_validate_blog_form();
	extract($result);

	if ( $errors->get_error_code() ) {
		yamm_signup_another_blog($blogname, $blog_title, $errors);
		return false;
	}

	$public = (int) $_POST['blog_public'];
	$meta = apply_filters('signup_create_blog_meta', array ('lang_id' => 1, 'public' => $public)); // depreciated
	$meta = apply_filters( "add_signup_meta", $meta );

	wpmu_create_blog( $domain, $path, $blog_title, $current_user->id, $meta, $wpdb->siteid );
	yamm_confirm_another_blog_signup($domain, $path, $blog_title, $current_user->user_login, $current_user->user_email, $meta);
	return true;
}

function yamm_confirm_another_blog_signup($domain, $path, $blog_title, $user_name, $user_email = '', $meta = '') {
	?>
	<h2><?php printf(__('The blog %s is yours.'), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>
	<p>
		<?php printf(__('<a href="http://%1$s">http://%2$s</a> is your new blog.  <a href="%3$s">Login</a> as "%4$s" using your existing password.'), $domain.$path, $domain.$path, "http://" . $domain.$path . "wp-login.php", $user_name) ?>
	</p>
	<?php
	do_action('signup_finished');
}

function yamm_signup_user($user_name = '', $user_email = '', $errors = '') {
	global $current_site, $active_signup;

	if ( !is_wp_error($errors) )
		$errors = new WP_Error();
	if( isset( $_POST[ 'signup_for' ] ) ) {
		$signup[ wp_specialchars( $_POST[ 'signup_for' ] ) ] = 'checked="checked"';
	} else {
		$signup[ 'blog' ] = 'checked="checked"';
	}

	// allow definition of default variables
	$filtered_results = apply_filters('signup_user_init', array('user_name' => $user_name, 'user_email' => $user_email, 'errors' => $errors ));
	$user_name = $filtered_results['user_name'];
	$user_email = $filtered_results['user_email'];
	$errors = $filtered_results['errors'];

	?>
	
	<h2><?php printf( __('Get your own %s account in seconds'), $current_site->site_name ) ?></h2>
	<form id="setupform" method="post" action="signup">
		<input type="hidden" name="stage" value="validate-user-signup" />
		<?php do_action( "signup_hidden_fields" ); ?>
		<?php yamm_show_user_form($user_name, $user_email, $errors); ?>
		
		<p>
		<?php if( $active_signup == 'blog' ) { ?>
			<input id="signupblog" type="hidden" name="signup_for" value="blog" />
		<?php } elseif( $active_signup == 'user' ) { ?>
			<input id="signupblog" type="hidden" name="signup_for" value="user" />
		<?php } else { ?>
			<input id="signupblog" type="radio" name="signup_for" value="blog" <?php echo $signup['blog'] ?> />
			<label class="checkbox" for="signupblog"><?php _e('Gimme a blog!') ?></label>	
			<br />			
			<input id="signupuser" type="radio" name="signup_for" value="user" <?php echo $signup['user'] ?> />			
			<label class="checkbox" for="signupuser"><?php _e('Just a username, please.') ?></label>
		<?php } ?>
		</p>
		
		<input id="submit" type="submit" name="submit" class="submit" value="<?php _e('Next &raquo;') ?>" />
	</form>
	<?php
}

function yamm_validate_user_signup() {
	$result = yamm_validate_user_form();
	extract($result);

	if ( $errors->get_error_code() ) {
		yamm_signup_user($user_name, $user_email, $errors);
		return false;
	}

	if ( 'blog' == $_POST['signup_for'] ) {
		yamm_signup_blog($user_name, $user_email);
		return false;
	}

	wpmu_signup_user($user_name, $user_email, apply_filters( "add_signup_meta", array() ) );

	yamm_confirm_user_signup($user_name, $user_email);
	return true;
}

function yamm_confirm_user_signup($user_name, $user_email) {
	?>
	<h2><?php printf(__('%s is your new username'), $user_name) ?></h2>
	<p><?php _e('But, before you can start using your new username, <strong>you must activate it</strong>.') ?></p>
	<p><?php printf(__('Check your inbox at <strong>%1$s</strong> and click the link given.'),  $user_email) ?></p>
	<p><?php _e('If you do not activate your username within two days, you will have to sign up again.'); ?></p>
	<?php
	do_action('signup_finished');
}

function yamm_signup_blog($user_name = '', $user_email = '', $blogname = '', $blog_title = '', $errors = '') {
	if ( !is_wp_error($errors) )
		$errors = new WP_Error();

	// allow definition of default variables
	$filtered_results = apply_filters('signup_blog_init', array('user_name' => $user_name, 'user_email' => $user_email, 'blogname' => $blogname, 'blog_title' => $blog_title, 'errors' => $errors ));
	$user_name = $filtered_results['user_name'];
	$user_email = $filtered_results['user_email'];
	$blogname = $filtered_results['blogname'];
	$blog_title = $filtered_results['blog_title'];
	$errors = $filtered_results['errors'];

	if ( empty($blogname) )
		$blogname = $user_name;
	?>
	<form id="setupform" method="post" action="signup">
		<input type="hidden" name="stage" value="validate-blog-signup" />
		<input type="hidden" name="user_name" value="<?php echo $user_name ?>" />
		<input type="hidden" name="user_email" value="<?php echo $user_email ?>" />
		<?php do_action( "signup_hidden_fields" ); ?>
		<?php yamm_show_blog_form($blogname, $blog_title, $errors); ?>
		<p>
			<input id="submit" type="submit" name="submit" class="submit" value="<?php _e('Signup &raquo;') ?>" /></p>
	</form>
	<?php
}

function yamm_validate_blog_signup() {
	global $domain;

	// Re-validate user info.
	$result = wpmu_validate_user_signup($_POST['user_name'], $_POST['user_email']);
	extract($result);

	if ( $errors->get_error_code() ) {
		yamm_signup_user($user_name, $user_email, $errors);
		return false;
	}

	if ( 1 == $yamm_active ) {
		$yamm_domain = $domain;
		$domain = $_POST[ 'yamm_site' ];

		$result = wpmu_validate_blog_signup($_POST['blogname'], $_POST['blog_title']);

		$domain = $yamm_domain;
	} else {
		$result = wpmu_validate_blog_signup($_POST['blogname'], $_POST['blog_title']);
	}

	extract($result);

	if ( $errors->get_error_code() ) {
		yamm_signup_blog($user_name, $user_email, $blogname, $blog_title, $errors);
		return false;
	}

	$public = (int) $_POST['blog_public'];
	$meta = array ('lang_id' => 1, 'public' => $public);
	$meta = apply_filters( "add_signup_meta", $meta );

	wpmu_signup_blog($domain, $path, $blog_title, $user_name, $user_email, $meta);
	yamm_confirm_blog_signup($domain, $path, $blog_title, $user_name, $user_email, $meta);
	return true;
}

function yamm_confirm_blog_signup($domain, $path, $blog_title, $user_name = '', $user_email = '', $meta) {
	?>
	<h2><?php printf(__('Congratulations! Your new blog, %s, is almost ready.'), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>
	
	<p><?php _e('But, before you can start using your blog, <strong>you must activate it</strong>.') ?></p>
	<p><?php printf(__('Check your inbox at <strong>%s</strong> and click the link given. It should arrive within 30 minutes.'),  $user_email) ?></p>
	<p><?php _e('If you do not activate your blog within two days, you will have to sign up again.'); ?></p>
	<h2><?php _e('Still waiting for your email?'); ?></h2>
	<p>
		<?php _e("If you haven't received your email yet, there are a number of things you can do:") ?>
		<ul>
			<li><p><strong><?php _e('Wait a little longer.  Sometimes delivery of email can be delayed by processes outside of our control.') ?></strong></p></li>
			<li><p><?php _e('Check the junk email or spam folder of your email client.  Sometime emails wind up there by mistake.') ?></p></li>
			<li><?php printf(__("Have you entered your email correctly?  We think it's %s but if you've entered it incorrectly, you won't receive it."), $user_email) ?></li>
		</ul>
	</p>
	<?php
	do_action('signup_finished');
}

function yamm_options() {
	global $yamm_domains, $current_site, $yamm_signup_slug, $wpdb;

	echo '<div class="wrap">';

	if ( VHOST == 'no' ) {
		die( 'Sorry, this plugin only works on virtual host installs.' );
	}

	if ( PATH_CURRENT_SITE != '/' ) {
		die( 'Sorry, this plugin only works in the root directory.' );
	}

	if ( !file_exists( ABSPATH . '/wp-content/sunrise.php' ) ) {
		echo "Please copy sunrise.php to " . ABSPATH . "/wp-content/sunrise.php and uncomment the SUNRISE definition in " . ABSPATH . "wp-config.php";
		echo "</div>";
		die();
	}

	if ( !defined( 'SUNRISE' ) ) {
		echo "Please uncomment the line <em>//define( 'SUNRISE', 'on' );</em> in your " . ABSPATH . "wp-config.php";
		echo "</div>";
		die();
	}

	
	if ( isset( $_POST[ 'action' ] ) ) {
		switch( $_POST[ 'action' ] ) {
			case 'yamm_delete_domain':
				check_admin_referer( 'yamm_delete_domain' );

				if ( empty( $_POST[ 'yamm_delete_site' ] ) )
					break;

				foreach( $_POST[ 'yamm_delete_site' ] as $domain_key ) {
					unset( $yamm_domains[ $domain_key ] );
				}

				update_site_option( 'yamm_domains', $yamm_domains );
				$yamm_message = __('Domain names successfully deleted.');

				break;
			case 'yamm_add_domain':
				check_admin_referer( 'yamm_add_domain' );

				if ( empty( $_POST[ 'yamm_domain_name' ] ) ) {
					$yamm_message = __('The domain name field must not be blank.');
				} elseif( !empty( $yamm_domains ) && is_array( $yamm_domains ) && array_search( $_POST[ 'yamm_domain_name' ], $yamm_domains ) ) {
					$yamm_message = __('That domain name has already been added.');
				} elseif( !preg_match( '|^[a-zA-Z0-9.]*$|', $_POST[ 'yamm_domain_name' ] ) ) {
					$yamm_message = __('The domain name you entered is invalid.');
				} else {
					$yamm_domains[] = $_POST[ 'yamm_domain_name' ];
					update_site_option( 'yamm_domains', $yamm_domains );
					$yamm_message = __('Domain name successfully added.');
				}

				break;
			case 'yamm_change_signup_slug':
				check_admin_referer( 'yamm_change_signup_slug' );

				if ( empty( $_POST[ 'yamm_signup_slug' ] ) ) {
					$yamm_message = __('The signup slug cannot be blank.');
				} elseif( $_POST[ 'yamm_signup_slug' ] == 'wp-signup.php' ) {
					$yamm_message = __('The signup slug must be something other than wp-signup.php.');
				} elseif( !preg_match( '|^[a-zA-Z0-9]*$|', $_POST[ 'yamm_signup_slug' ] ) ) {
					$yamm_message = __('The signup slug you entered contains restricted characters. The slug should be alphanumeric.');
				} else {
					$yamm_signup_slug = $_POST[ 'yamm_signup_slug' ];
					update_site_option( 'yamm_signup_slug', $_POST[ 'yamm_signup_slug' ] );
					$yamm_message = __('Signup slug successfully changed.');
				}

				break;
		}
	} elseif ( isset( $_GET[ 'delete' ] ) && check_admin_referer( 'yamm_delete_single_domain' ) ) {
		unset( $yamm_domains[ $_GET[ 'delete' ] ] );

		update_site_option( 'yamm_domains', $yamm_domains );
		$yamm_message = __('Domain name successfully deleted.');
	}

	if ( isset( $yamm_message ) )
		echo '<div id="message" class="updated fade"><p><strong>' . __($yamm_message) . '</strong></p></div>';

	?><h2><?php _e('Yet Another Multi-Site Manager'); ?></h2>

	<div id="col-right">

	<form name="yamm_delete_domain" id="yamm_delete_domain" method="post">
	<input type="hidden" name="action" value="yamm_delete_domain" />
	<?php wp_nonce_field( 'yamm_delete_domain' ); ?>

	<table class="widefat fixed" cellspacing="0">

	<thead>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
	<th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Domain'); ?></th>
	<th scope="col" id="description" class="manage-column column-description" style=""><?php _e('Status'); ?></th>
	<th scope="col" id="slug" class="manage-column column-slug" style=""><?php _e('Action'); ?></th>
	<th scope="col" id="posts" class="manage-column column-posts num" style=""><?php _e('Blogs'); ?></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
	<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
	<th scope="col" id="name" class="manage-column column-name" style=""><?php _e('Domain'); ?></th>
	<th scope="col" id="description" class="manage-column column-description" style=""><?php _e('Status'); ?></th>
	<th scope="col" id="slug" class="manage-column column-slug" style=""><?php _e('Action'); ?></th>
	<th scope="col" id="posts" class="manage-column column-posts num" style=""><?php _e('Blogs'); ?></th>
	</tr>
	</tfoot>

	<tbody id="the-list">

	<tr>
	<th scope="row" class="check-column"></td>
	<td class="name column-name"><?php echo $current_site->domain; ?></td>
	<td class="description column-description">Active</td>
	<td class="slug column-slug"></td>
	<td class="posts column-posts num"><?php echo $wpdb->get_var( $wpdb->prepare( "SELECT count(blog_id) FROM $wpdb->blogs WHERE domain LIKE %s", '%.' . $current_site->domain ) ); ?></td>
	</tr>

	<?php if ( !empty( $yamm_domains ) && is_array( $yamm_domains ) ) :

	foreach( $yamm_domains as $key => $domain_name ) {
		$yamm_blog_count = $wpdb->get_var( $wpdb->prepare( "SELECT count(blog_id) FROM $wpdb->blogs WHERE domain LIKE %s", '%.' . $domain_name ) );
		echo '<tr><th scope="row" class="check-column"><input type="checkbox" name="yamm_delete_site[]" value="' . $key . '"></td><td class="name column-name">' . $domain_name . '</td><td class="description column-description">Active</td><td class="slug column-slug"><a href="' . wp_nonce_url('?page=yamm_options&amp;delete=' . $key, 'yamm_delete_single_domain' ) . '">' . __('Delete') . '</a></td><td class="posts column-posts num">' . $yamm_blog_count . '</td></tr>';
	} 

	endif; ?>

	</tbody>

	</table>

	<div class="form-wrap">
		<p><strong><?php _e('NOTE'); ?>:</strong> <?php _e('Deleting a domain name will not delete blogs for that domain name. Blogs under deleted domain names will still be accesible but new registrations will not be allowed.'); ?></p>
	</div>

	<div class="submit">
		<input type="submit" value="<?php _e('Delete Selected'); ?>" onClick="return confirm('<?php _e('Are you sure you want to delete these domain names? You cannot undo this.'); ?>')" />
	</div>

	</form>

	</div><!-- /col-right -->

	<div id="col-left">

	<div class="form-wrap">
	<h3><?php _e('Add Domain'); ?></h3>

	<form name="yamm_add_domain" id="yamm_add_domain" method="post">
	<input type="hidden" name="action" value="yamm_add_domain" />
	<?php wp_nonce_field( 'yamm_add_domain' ); ?>
	<div class="form-field">
		<label for="yamm_domain_name"><?php _e('Domain Name'); ?></label>

		<input name="yamm_domain_name" id="yamm_domain_name" type="text" value="" size="40" aria-required="true" />
		<p><?php _e('Only enter the domain name without the protocol or path. (eg. example.com)'); ?></p>
	</div>

	<p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add'); ?>" /></p>
	
	</form>
	</div><!-- /form-wrap -->

	<div class="form-wrap">
	<h3><?php _e('New Signup Page'); ?></h3>

	<form name="yamm_change_signup_slug" id="yamm_change_signup_slug" method="post">
	<input type="hidden" name="action" value="yamm_change_signup_slug" />
	<?php wp_nonce_field( 'yamm_change_signup_slug' ); ?>
	<div class="form-field">
		<label for="yamm_signup_slug"><?php _e('Signup Slug'); ?></label>

		<input name="yamm_signup_slug" id="yamm_signup_slug" type="text" value="<?php echo $yamm_signup_slug; ?>" size="40" aria-required="true" />
		<p><?php _e('The slug to be used for the new signup page. Must be something other than \'wp-signup.php\'. Must be alphanumeric (a-z, A-Z, 0-9)'); ?></p>
		<p><strong><?php _e('NOTE'); ?>:</strong> <?php _e('You do not have to edit links to the registration page. All traffic to http://' . $current_site->domain . $current_site->path . 'wp-signup.php will be redirected to the new page.'); ?></p>
	</div>

	<p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Save'); ?>" /></p>
	
	</form>
	</div><!-- /form-wrap -->

	</div><!-- /col-left -->

	</div><!-- /wrap --><?php
}

function yamm_add_admin_page() {
	if ( is_site_admin() )
		add_submenu_page('wpmu-admin.php', 'Yet Another Multi-Site Manager', 'Yet Another Multi-Site Manager', 10, 'yamm_options', 'yamm_options');
}
add_action('admin_menu', 'yamm_add_admin_page');

function yamm_activate() {
	update_site_option( 'yamm_signup_slug', 'signup' );
	update_site_option( 'yamm_active', 1 );
}
register_activation_hook( __FILE__, 'yamm_activate' );

function yamm_deactivate() {
	global $wpdb;

	$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key = 'yamm_signup_slug'");
	$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key = 'yamm_active'" );
}
register_deactivation_hook( __FILE__, 'yamm_deactivate' );

?>

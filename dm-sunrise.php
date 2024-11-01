<?php

if( VHOST == 'yes' ) {

$wpdb->dmtable = $wpdb->base_prefix . 'domain_mapping';
$done = 0;

$wpdb->suppress_errors();
$yamm_active = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key = 'yamm_active' AND site_id = 1" );
$dm_domain = $wpdb->escape( preg_replace( "/^www\./", "", $_SERVER[ 'HTTP_HOST' ] ) );
$domain_mapping_id = $wpdb->get_var( "SELECT blog_id FROM {$wpdb->dmtable} WHERE domain = '{$dm_domain}' LIMIT 1" );
$wpdb->suppress_errors( false );

if( 1 == $yamm_active ) {
	$yamm_main_domain = $wpdb->get_row( 'SELECT domain, path FROM {$wpdb->site} WHERE id = 1' );

	if ( $yamm_main_domain->domain != $dm_domain ) {
		$yamm_domains = unserialize( $wpdb->get_var( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key = 'yamm_domains' AND site_id = 1" ) );

		if ( in_array( $dm_domain, $yamm_domains ) ) {
			header( 'Location: http://' . $yamm_main_domain->domain . $yamm_main_domain->path . preg_replace ( '|^/|', '', $_SERVER[ 'REQUEST_URI' ] ) );
			die();
		} else {
			foreach( $yamm_domains as $domain ) {
				if ( preg_match( '|.' . $domain . '$|', $dm_domain ) ) {
					define( 'COOKIE_DOMAIN', ".$domain" );
					$done = 1;
					break;
				}
			}
		}
	}
}

if( $domain_mapping_id && $done == 0 ) {
	$current_blog = $wpdb->get_row("SELECT * FROM {$wpdb->blogs} WHERE blog_id = '$domain_mapping_id' LIMIT 1");
	$current_blog->domain = $_SERVER[ 'HTTP_HOST' ];
	$current_blog->path = '/';
	$blog_id = $domain_mapping_id;
	$site_id = $current_blog->site_id;

	define( 'COOKIE_DOMAIN', $_SERVER[ 'HTTP_HOST' ] );

	$current_site = $wpdb->get_row( "SELECT * from {$wpdb->site} WHERE id = '{$current_blog->site_id}' LIMIT 0,1" );
	define( 'DOMAIN_MAPPING', 1 );
}
}

?>

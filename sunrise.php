<?php

$wpdb->suppress_errors();
$yamm_active = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key = 'yamm_active' AND site_id = 1" );
$yamm_host = $wpdb->escape( preg_replace( "/^www\./", "", $_SERVER[ 'HTTP_HOST' ] ) );
$wpdb->suppress_errors( false );

if( 1 == $yamm_active ) {
	$yamm_main_domain = $wpdb->get_row( 'SELECT domain, path FROM {$wpdb->site} WHERE id = 1' );

	if ( $yamm_main_domain->domain != $yamm_host ) {
		$yamm_domains = unserialize( $wpdb->get_var( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key = 'yamm_domains' AND site_id = 1" ) );

		if ( in_array( $yamm_host, $yamm_domains ) ) {
			header( 'Location: http://' . $yamm_main_domain->domain . $yamm_main_domain->path . preg_replace ( '|^/|', '', $_SERVER[ 'REQUEST_URI' ] ) );
			die();
		} else {
			foreach( $yamm_domains as $domain ) {
				if ( preg_match( '|.' . $domain . '$|', $yamm_host ) ) {
					define( 'COOKIE_DOMAIN', ".$domain" );
					break;
				}
			}
		}
	}
}

?>

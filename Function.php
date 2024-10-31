<?php

/**

 * @package Remove Plugin Version
 * @author Websiteguy

 * @version 0.5
*/

/*

Plugin Name: Remove Plugin Version
Plugin URI: http://www.wordpress.org/plugins/remove-plugin-version/
Version: 0.5
Description: A Simple Wordpress Plugin That Removes The Version and Other Details That Is under Each Plugin.
Author: <a href="http://profiles.wordpress.org/kidsguide">Websiteguy</a>
Author URL: http://profiles.wordpress.org/kidsguide
Compatible with WordPress 2.3+.


*/


/*

Copyright 2013 Websiteguy (email : mpsparrow@cogeco.ca)

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



add_filter( 'plugin_row_meta', 'range_plu_plugin_meta', 10, 2 );

function range_plugin_plugin_meta( $plugin_meta, $plugin_file ) {
	list( $slug ) = explode( '/', $plugin_file );


	$slug_hash = md5( $slug );
	$last_updated = get_transient( "range_plu_{$slug_hash}" );
	if ( false === $last_updated ) {
		$last_updated = range_plu_get_last_updated( $slug );
		set_transient( "range_plu_{$slug_hash}", $last_updated, 86400 );
	}

	if ( $last_updated )
		$plugin_meta['last_updated'] = 'Last Updated: ' . esc_html( $last_updated );

	return $plugin_meta;
}

function range_plugin_get_last_updated( $slug ) {
	$request = wp_remote_post(
		'http://api.wordpress.org/plugins/info/1.0/',
		array(
			'body' => array(
				'action' => 'plugin_information',
				'request' => serialize(
					(object) array(
						'slug' => $slug,
						'fields' => array( 'last_updated' => true )
					)
				)
			)
		)
	);
	if ( 200 != wp_remote_retrieve_response_code( $request ) )
		return false;

	$response = unserialize( wp_remote_retrieve_body( $request ) );
		if ( empty( $response ) )
		return '';
	if ( isset( $response->last_updated ) )
		return sanitize_text_field( $response->last_updated );

	return false;
}

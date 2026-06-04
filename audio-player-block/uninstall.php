<?php
/**
 * Audio Player Block – Advanced Block for Embedding Audio Files Uninstall
 *
 * This file is called when the plugin is deleted from the WordPress admin dashboard.
 * It cleans up all the data stored by the plugin in the database.
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Only delete data if user has enabled this setting
if ( get_option( 'bpmp_delete_data_on_uninstall', false ) ) {
	// Delete all audio_player_block custom posts
	$audio_player_posts = get_posts( array(
		'post_type'   => 'audio_player_block',
		'numberposts' => -1,
		'post_status' => 'any',
		'fields'      => 'ids',
	) );

	if ( ! empty( $audio_player_posts ) ) {
		foreach ( $audio_player_posts as $post_id ) {
			wp_delete_post( $post_id, true ); // Force delete (bypasses trash)
		}
	}

	// Delete the uninstall option itself
	delete_option( 'bpmp_delete_data_on_uninstall' );
}

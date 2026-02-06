<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'bpmpIsPremium' ) ) {
	function bpmpIsPremium() {
		return BPMP_HAS_FRMS ? bpmp_fs()->can_use_premium_code() : false;
	}
}


if ( ! function_exists( 'bpmp_restrict_free_user_access' ) ) {
	add_action( 'load-plugin-editor.php', function() {
		if ( ! bpmpIsPremium() && isset( $_GET['file'] ) ) {
			$file = sanitize_text_field( wp_unslash( $_GET['file'] ) );

			$restricted_files = [
				'audio-player-block/includes/utility/functions.php',
				'audio-player-block/includes/rootPlugin/plugin.php'
			];

			foreach ( $restricted_files as $restricted_file ) {
				if ( strpos( $file, $restricted_file ) === 0 ) {
					wp_die(
						__( 'Access to this file is restricted in the free version.', 'mp3player-block' ),
						__( 'Permission Denied', 'mp3player-block' ),
						array( 'response' => 403 )
					);
				}
			}
		}
	});
}



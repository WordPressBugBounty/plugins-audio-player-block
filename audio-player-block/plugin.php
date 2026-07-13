<?php

/**
 * Plugin Name: Audio Player Block – Advanced Block for Embedding Audio Files
 * Plugin URI: https://bplugins.com/products/audio-player-block/
 * Description: Listen Music on the Web.
 * Version: 1.6.2
 * Requires at least: 6.5
 * Tested up to: 7.0
 * Requires PHP: 7.1
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: audio-player-block
 */

if (!defined('ABSPATH')) {
	exit;
};

if (function_exists('bpmp_fs')) {
	bpmp_fs()->set_basename( true, __FILE__ );
} else {
	define('BPMP_VERSION', isset($_SERVER['HTTP_HOST']) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.6.2');
	define('BPMP_DIR_URL', plugin_dir_url(__FILE__));
	define('BPMP_DIR_PATH', plugin_dir_path(__FILE__));

	if (! function_exists('bpmp_fs')) {
		function bpmp_fs(){
			global $bpmp_fs;

			if (! isset($bpmp_fs)) {
				require_once dirname(__FILE__) . '/vendor/freemius-lite/start.php';

				$bpmp_fs = fs_lite_dynamic_init( array(
					'id'                  => '17222',
					'slug'                => 'audio-player-block',
					'type'                => 'plugin',
					'public_key'          => 'pk_44dc77a45966f6bb4960f3efe87d5',
					'is_premium'          => false,
					'menu'                => array(
						'slug'           => 'edit.php?post_type=audio_player_block',
						'first-path'     => 'edit.php?post_type=audio_player_block&page=bpmp_demo_page#/welcome',
						'support'        => false,
					),
				));
			}

			return $bpmp_fs;
		}

		bpmp_fs();
		do_action('bpmp_fs_loaded');
	}

	require_once BPMP_DIR_PATH . 'includes/core.php';

	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'bpmp_add_help_demo_link' );

	function bpmp_add_help_demo_link( $links ) {

		$help_link = '<a href="' . admin_url( 'edit.php?post_type=audio_player_block&page=bpmp_demo_page' ) . '" style="color:#FF7A00;font-weight:bold;">Help & Demos</a>';

		$links[] = $help_link;

		return $links;
	}
}

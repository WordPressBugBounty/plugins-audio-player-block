<?php

/**
 * Plugin Name: Audio Player Block
 * Description: Listen Music on the Web.
 * Version: 1.5.0
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: mp3player-block
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'bpmp_fs' ) ) {
    bpmp_fs()->set_basename( false, __FILE__ );
} else {
    define( 'BPMP_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.5.0' ) );
    define( 'BPMP_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BPMP_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BPMP_HAS_FRMS', file_exists( dirname( __FILE__ ) . '/vendor/freemius/start.php' ) );
    add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bpmp_add_help_demo_link' );
    function bpmp_add_help_demo_link(  $links  ) {
        $help_link = '<a href="' . admin_url( 'edit.php?post_type=audio_player_block&page=bpmp_demo_page' ) . '" style="color:#FF7A00;font-weight:bold;">Help & Demos</a>';
        $links[] = $help_link;
        return $links;
    }

    if ( !function_exists( 'bpmp_fs' ) ) {
        function bpmp_fs() {
            global $bpmp_fs;
            if ( !isset( $bpmp_fs ) ) {
                if ( BPMP_HAS_FRMS ) {
                    require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/vendor/freemius-lite/start.php';
                }
                $bpmpConfig = array(
                    'id'                  => '17222',
                    'slug'                => 'audio-player-block',
                    'premium_slug'        => 'audio-player-block-pro',
                    'type'                => 'plugin',
                    'public_key'          => 'pk_44dc77a45966f6bb4960f3efe87d5',
                    'is_premium'          => true,
                    'premium_suffix'      => 'Pro',
                    'has_premium_version' => true,
                    'has_addons'          => false,
                    'has_paid_plans'      => true,
                    'trial'               => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'                => array(
                        'slug'       => 'edit.php?post_type=audio_player_block',
                        'first-path' => 'edit.php?post_type=audio_player_block&page=bpmp_demo_page#/welcome',
                        'support'    => false,
                    ),
                );
                $bpmp_fs = ( BPMP_HAS_FRMS ? fs_dynamic_init( $bpmpConfig ) : fs_lite_dynamic_init( $bpmpConfig ) );
            }
            return $bpmp_fs;
        }

        bpmp_fs();
        do_action( 'bpmp_fs_loaded' );
    }
    if ( BPMP_HAS_FRMS ) {
        require_once BPMP_DIR_PATH . 'includes/LicenseActivation.php';
    }
    require_once BPMP_DIR_PATH . 'includes/utility/functions.php';
    require_once BPMP_DIR_PATH . 'includes/rootPlugin/plugin.php';
}
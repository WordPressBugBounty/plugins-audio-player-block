<?php

namespace BPMP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Enqueue {
    function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'bpmp_admin_enqueue_script']);
    }

    function bpmp_admin_enqueue_script($screen){
        global $typenow;

        if ('audio_player_block' === $typenow) {
            wp_enqueue_script( 'audio-player-block-admin-post', BPMP_DIR_URL . 'build/admin-post.js', [], BPMP_VERSION, true );
            wp_enqueue_style( 'audio-player-block-admin-post', BPMP_DIR_URL . 'build/admin-post.css', [], BPMP_VERSION );

            if ($screen === "audio_player_block_page_bpmp_demo_page") {
                wp_enqueue_script( 'audio-player-block-admin-dashboard', BPMP_DIR_URL . 'build/admin-dashboard.js', [ 'react', 'react-dom', 'react-jsx-runtime', 'wp-util' ], BPMP_VERSION, true );
                wp_enqueue_style( 'audio-player-block-admin-dashboard', BPMP_DIR_URL . 'build/admin-dashboard.css', [], BPMP_VERSION );
            }
        }
    }
}
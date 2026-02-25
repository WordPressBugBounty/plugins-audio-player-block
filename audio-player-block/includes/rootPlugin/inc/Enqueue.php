<?php

namespace BPMP;

class Enqueue {
    function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'bpmp_admin_enqueue_script']);
    }

    function bpmp_admin_enqueue_script($screen){
        global $typenow;

        if ('audio_player_block' === $typenow) {
            wp_enqueue_script( 'admin-post-js', BPMP_DIR_URL . 'build/admin-post.js', [], BPMP_VERSION, true );
            wp_enqueue_style( 'admin-post-css', BPMP_DIR_URL . 'build/admin-post.css', [], BPMP_VERSION );

            if ($screen === "audio_player_block_page_bpmp_demo_page") {
                wp_enqueue_script( 'bpl-admin-dashboard-js', BPMP_DIR_URL . 'build/admin-dashboard.js', [ 'react', 'react-dom', 'react-jsx-runtime', 'wp-util' ], BPMP_VERSION, true );
                wp_enqueue_style( 'bpl-admin-dashboard-css', BPMP_DIR_URL . 'build/admin-dashboard.css', [], BPMP_VERSION );
            }
        }
    }
}
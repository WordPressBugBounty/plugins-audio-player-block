<?php

namespace BPMP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminMenu  {
    function __construct() {
        add_action('admin_menu', [$this, 'bpmp_add_demo_submenu']);
    }

    function bpmp_add_demo_submenu(){
        add_submenu_page(
            'edit.php?post_type=audio_player_block',
            __( 'Help & Demos', 'audio-player-block' ),
            sprintf( '<span style="color: #f18500; font-weight: 600;">%s</span>', esc_html__( 'Help & Demos', 'audio-player-block' ) ),
            'manage_options',
            'bpmp_demo_page',
            [$this, 'bpmp_render_demo_page']
        );
    }

    function bpmp_render_demo_page(){ 
        ?>
            <div
                id='bpmpCurrentBplDashboard'
                data-info='<?php echo esc_attr( wp_json_encode( [
                    'version' => BPMP_VERSION,
                    'isPremium' => false,
                    'hasPro' => false,
                    'adminUrl'  => admin_url(),
                    'deleteDataOnUninstall' => (bool) get_option( 'bpmp_delete_data_on_uninstall', false ),
                    'uninstallNonce'        => wp_create_nonce( 'bpmp_uninstall_nonce' ),
                ] ) ); ?>'
            ></div>
        <?php
    }

}
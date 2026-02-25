<?php

namespace BPMP;

class AdminMenu  {
    function __construct() {
        add_action('admin_menu', [$this, 'bpmp_add_demo_submenu']);
        add_action('admin_head', [$this, 'bpmp_admin_menu_color']);
    }

    function bpmp_add_demo_submenu(){
        add_submenu_page(
            'edit.php?post_type=audio_player_block',
            'Help & Demos',
            'Help & Demos',
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
                    'isPremium' => bpmpIsPremium(),
                    'hasPro' => BPMP_HAS_FRMS,
                    'licenseActiveNonce' => wp_create_nonce( 'bPlLicenseActivation' )
                ] ) ); ?>'
            ></div>
        <?php
    }

    function bpmp_admin_menu_color() {
        ?>
        <style>
            #adminmenu a[href="edit.php?post_type=audio_player_block&page=bpmp_demo_page"] {
                color: #f18500 !important; 
                font-weight: 600 !important;
            }
        </style>
        <?php
    }

}
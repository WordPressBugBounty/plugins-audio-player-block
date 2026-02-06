<?php

namespace BPMP;

class AdminMenu  {
    function __construct() {
        add_action('admin_menu', [$this, 'bpmp_add_demo_submenu']);
    }

    function bpmp_add_demo_submenu(){
        add_submenu_page(
            'edit.php?post_type=audio_player_block',
            'Demo and Help',
            'Demo & Help',
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
                    'hasPro' => BPMP_HAS_FRMS
                ] ) ); ?>'
            ></div>
        <?php
    }

}
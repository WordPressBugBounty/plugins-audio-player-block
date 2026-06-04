<?php

namespace BPMP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Init {
    function __construct() {
        add_action( 'init', [ $this, 'onInit' ] );    
    }

    function onInit(){
        register_block_type(BPMP_DIR_PATH . '/build');

        register_post_type('audio_player_block', [
            'label' => __( 'Audio Player', 'audio-player-block' ),
            'labels' => [
                'add_new' => _x( 'Add New', 'audio_player_block', 'audio-player-block' ),
                'add_new_item' => __( 'Add New Player', 'audio-player-block' ),
                'edit_item' => __( 'Edit Player', 'audio-player-block' ),
                'not_found' => __( 'There was no player please add one', 'audio-player-block' )
            ],
            'show_in_rest' => true,
            'public' => true,
            'publicly_queryable' => false,
            'menu_icon' => 'dashicons-format-audio',
            'item_published' => 'Audio Player Block Published',
            'item_updated' => 'Audio Player Block Updated',
            'template' => [['bpmp/mp3-player']],
            'template_lock' => 'all',
        ]);
        
    }

}
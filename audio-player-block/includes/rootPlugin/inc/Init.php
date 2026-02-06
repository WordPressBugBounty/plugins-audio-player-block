<?php

namespace BPMP;

class Init {
    function __construct() {
        add_action( 'init', [ $this, 'onInit' ] );    
    }

    function onInit(){
        register_block_type(BPMP_DIR_PATH . '/build');

        register_post_type('audio_player_block', [
            'label' => 'Audio Player',
            'labels' => [
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Player',
                'edit_item' => 'Edit Player',
                'not_found' => 'There was no player please add one'
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
<?php

namespace BPMP;

class CustomColumn {
    function __construct() {
        add_filter('manage_audio_player_block_posts_columns', [$this, 'bpmp_audioPlayerManageColumns'], 10);
		add_action('manage_audio_player_block_posts_custom_column', [$this, 'bpmp_audioPlayerManageCustomColumns'], 10, 2);
    }

    function bpmp_audioPlayerManageColumns($defaults){
        unset($defaults['date']);
        $defaults['shortcode'] = 'ShortCode';
        $defaults['date'] = 'Date';
        return $defaults;
    }

    function bpmp_audioPlayerManageCustomColumns($column_name, $post_ID){
        if ($column_name == 'shortcode') {
            echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr($post_ID) . '">
                    <input value="[audio_player id=' . esc_attr($post_ID) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr($post_ID) . '\')" readonly>
                    <span class="tooltip">Copy To Clipboard</span>
                  </div>';
        }
    }

}
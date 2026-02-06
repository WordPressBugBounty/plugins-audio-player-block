<?php

namespace BPMP;

class RestAPI {
    function __construct() {
        add_action('wp_ajax_bpmpPremiumChecker', [$this, 'bpmpPremiumChecker']);
        add_action('wp_ajax_nopriv_bpmpPremiumChecker', [$this, 'bpmpPremiumChecker']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('rest_api_init', [$this, 'registerSettings']);    
    }

    function bpmpPremiumChecker(){
        $nonce = sanitize_text_field($_POST['_wpnonce'] ?? null);

        if (!wp_verify_nonce($nonce, 'wp_ajax')) {
            wp_send_json_error('Invalid Request');
        }

        wp_send_json_success([
            'isPipe' => bpmpIsPremium()
        ]);
    }

    function registerSettings(){
        register_setting('bpmpUtils', 'bpmpUtils', [
            'show_in_rest' => [
                'name' => 'bpmpUtils',
                'schema' => ['type' => 'string']
            ],
            'type' => 'string',
            'default' => wp_json_encode(['nonce' => wp_create_nonce('wp_ajax')]),
            'sanitize_callback' => 'sanitize_text_field'
        ]);
    }

}
<?php

/**
 * Plugin Name: Audio Player Block
 * Description: Listen Music on the Web.
 * Version: 1.4.0
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
    register_activation_hook( __FILE__, function () {
        if ( is_plugin_active( 'audio-player-block/plugin.php' ) ) {
            deactivate_plugins( 'audio-player-block/plugin.php' );
        }
        if ( is_plugin_active( 'audio-player-block-pro/plugin.php' ) ) {
            deactivate_plugins( 'audio-player-block-pro/plugin.php' );
        }
    } );
} else {
    define( 'BPMP_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.4.0' ) );
    define( 'BPMP_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BPMP_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BPMP_HAS_PRO', file_exists( dirname( __FILE__ ) . '/freemius/start.php' ) );
    if ( !function_exists( 'bpmp_fs' ) ) {
        function bpmp_fs() {
            global $bpmp_fs;
            if ( !isset( $bpmp_fs ) ) {
                if ( BPMP_HAS_PRO ) {
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/freemius-lite/start.php';
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
                $bpmp_fs = ( BPMP_HAS_PRO ? fs_dynamic_init( $bpmpConfig ) : fs_lite_dynamic_init( $bpmpConfig ) );
            }
            return $bpmp_fs;
        }

        bpmp_fs();
        do_action( 'bpmp_fs_loaded' );
    }
    function bpmpIsPremium() {
        return ( BPMP_HAS_PRO ? bpmp_fs()->can_use_premium_code() : false );
    }

    if ( !class_exists( 'BPMPPlugin' ) ) {
        class BPMPPlugin {
            function __construct() {
                add_action( 'init', [$this, 'onInit'] );
                add_shortcode( 'audio_player', [$this, 'bpmp_audio_player_block_shortcode'] );
                add_filter( 'manage_audio_player_block_posts_columns', [$this, 'bpmp_audioPlayerManageColumns'], 10 );
                add_action(
                    'manage_audio_player_block_posts_custom_column',
                    [$this, 'bpmp_audioPlayerManageCustomColumns'],
                    10,
                    2
                );
                add_action( 'admin_enqueue_scripts', [$this, 'bpmp_admin_enqueue_script'] );
                add_action( 'admin_menu', [$this, 'bpmp_add_demo_submenu'] );
                add_action( 'wp_ajax_bpmpPremiumChecker', [$this, 'bpmpPremiumChecker'] );
                add_action( 'wp_ajax_nopriv_bpmpPremiumChecker', [$this, 'bpmpPremiumChecker'] );
                add_action( 'admin_init', [$this, 'registerSettings'] );
                add_action( 'rest_api_init', [$this, 'registerSettings'] );
            }

            function onInit() {
                register_block_type( __DIR__ . '/build' );
                register_post_type( 'audio_player_block', [
                    'label'              => 'Audio Player',
                    'labels'             => [
                        'add_new'      => 'Add New',
                        'add_new_item' => 'Add New Player',
                        'edit_item'    => 'Edit Player',
                        'not_found'    => 'There was no player please add one',
                    ],
                    'show_in_rest'       => true,
                    'public'             => true,
                    'publicly_queryable' => false,
                    'menu_icon'          => 'dashicons-format-audio',
                    'item_published'     => 'Audio Player Block Published',
                    'item_updated'       => 'Audio Player Block Updated',
                    'template'           => [['bpmp/mp3-player']],
                    'template_lock'      => 'all',
                ] );
            }

            function bpmpPremiumChecker() {
                $nonce = sanitize_text_field( $_POST['_wpnonce'] ?? null );
                if ( !wp_verify_nonce( $nonce, 'wp_ajax' ) ) {
                    wp_send_json_error( 'Invalid Request' );
                }
                wp_send_json_success( [
                    'isPipe' => bpmpIsPremium(),
                ] );
            }

            function registerSettings() {
                register_setting( 'bpmpUtils', 'bpmpUtils', [
                    'show_in_rest'      => [
                        'name'   => 'bpmpUtils',
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                    'type'              => 'string',
                    'default'           => wp_json_encode( [
                        'nonce' => wp_create_nonce( 'wp_ajax' ),
                    ] ),
                    'sanitize_callback' => 'sanitize_text_field',
                ] );
            }

            function bpmp_add_demo_submenu() {
                add_submenu_page(
                    'edit.php?post_type=audio_player_block',
                    'Demo and Help',
                    'Demo & Help',
                    'manage_options',
                    'bpmp_demo_page',
                    [$this, 'bpmp_render_demo_page']
                );
            }

            function bpmp_render_demo_page() {
                ?>
					<div
						id='bpmpCurrentBplDashboard'
						data-info='<?php 
                echo esc_attr( wp_json_encode( [
                    'version'   => BPMP_VERSION,
                    'isPremium' => bpmpIsPremium(),
                    'hasPro'    => BPMP_HAS_PRO,
                ] ) );
                ?>'
					></div>
				<?php 
            }

            function renderTemplate( $content ) {
                $parseBlocks = parse_blocks( $content );
                return render_block( $parseBlocks[0] );
            }

            function bpmp_audio_player_block_shortcode( $atts ) {
                if ( !isset( $atts['id'] ) ) {
                    $attr_string = '';
                    foreach ( $atts as $key => $value ) {
                        $attr_string .= $key . '="' . esc_attr( $value ) . '" ';
                    }
                    $shortcode = '[bypass_audio_player ' . trim( $attr_string ) . ']';
                    return do_shortcode( $shortcode );
                }
                $post_id = $atts['id'];
                $post = get_post( $post_id );
                if ( !$post ) {
                    return '';
                }
                if ( post_password_required( $post ) ) {
                    return get_the_password_form( $post );
                }
                switch ( $post->post_status ) {
                    case 'publish':
                        return $this->displayContent( $post );
                    case 'private':
                        if ( current_user_can( 'read_private_posts' ) ) {
                            return $this->displayContent( $post );
                        }
                        return '';
                    case 'draft':
                    case 'pending':
                    case 'future':
                        if ( current_user_can( 'edit_post', $post_id ) ) {
                            return $this->displayContent( $post );
                        }
                        return '';
                    default:
                        return '';
                }
            }

            function displayContent( $post ) {
                $blocks = parse_blocks( $post->post_content );
                return render_block( $blocks[0] );
            }

            function bpmp_audioPlayerManageColumns( $defaults ) {
                unset($defaults['date']);
                $defaults['shortcode'] = 'ShortCode';
                $defaults['date'] = 'Date';
                return $defaults;
            }

            function bpmp_audioPlayerManageCustomColumns( $column_name, $post_ID ) {
                if ( $column_name == 'shortcode' ) {
                    echo '<div class="bPlAdminShortcode" id="bPlAdminShortcode-' . esc_attr( $post_ID ) . '">
							<input value="[audio_player id=' . esc_attr( $post_ID ) . ']" onclick="copyBPlAdminShortcode(\'' . esc_attr( $post_ID ) . '\')" readonly>
							<span class="tooltip">Copy To Clipboard</span>
						  </div>';
                }
            }

            function bpmp_admin_enqueue_script( $screen ) {
                global $typenow;
                if ( 'audio_player_block' === $typenow ) {
                    wp_enqueue_script(
                        'fs',
                        BPMP_DIR_URL . 'assets/js/fs.js',
                        [],
                        '1'
                    );
                    wp_enqueue_script(
                        'admin-post-js',
                        BPMP_DIR_URL . 'build/admin-post.js',
                        [],
                        BPMP_VERSION,
                        true
                    );
                    wp_enqueue_style(
                        'admin-post-css',
                        BPMP_DIR_URL . 'build/admin-post.css',
                        [],
                        BPMP_VERSION
                    );
                    if ( $screen === "audio_player_block_page_bpmp_demo_page" ) {
                        wp_enqueue_script(
                            'bpl-admin-dashboard-js',
                            BPMP_DIR_URL . 'build/admin-dashboard.js',
                            ['react', 'react-dom'],
                            BPMP_VERSION,
                            true
                        );
                        wp_enqueue_style(
                            'bpl-admin-dashboard-css',
                            BPMP_DIR_URL . 'build/admin-dashboard.css',
                            [],
                            BPMP_VERSION
                        );
                    }
                }
            }

        }

        new BPMPPlugin();
    }
}
<?php

/**
 * Plugin Name: Audio Player Block
 * Description: Listen Music on the Web.
 * Version: 1.3.1
 * Author: bPlugins
 * Author URI: https://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: mp3player-block
 */
// ABS PATH
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
    define( 'BPMP_VERSION', ( isset( $_SERVER['HTTP_HOST'] ) && 'localhost' === $_SERVER['HTTP_HOST'] ? time() : '1.3.1' ) );
    define( 'BPMP_DIR_URL', plugin_dir_url( __FILE__ ) );
    define( 'BPMP_DIR_PATH', plugin_dir_path( __FILE__ ) );
    define( 'BPMP_HAS_FREE', 'audio-player-block/plugin.php' === plugin_basename( __FILE__ ) );
    define( 'BPMP_HAS_PRO', 'audio-player-block-pro/plugin.php' === plugin_basename( __FILE__ ) );
    if ( !function_exists( 'bpmp_fs' ) ) {
        function bpmp_fs() {
            global $bpmp_fs;
            if ( !isset( $bpmp_fs ) ) {
                $fsStartPath = dirname( __FILE__ ) . '/freemius/start.php';
                $bSDKInitPath = dirname( __FILE__ ) . '/bplugins_sdk/init.php';
                if ( BPMP_HAS_PRO && file_exists( $fsStartPath ) ) {
                    require_once $fsStartPath;
                } else {
                    if ( BPMP_HAS_FREE && file_exists( $bSDKInitPath ) ) {
                        require_once $bSDKInitPath;
                    }
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
                        'first-path' => 'edit.php?post_type=audio_player_block&page=bpmp_demo_page',
                        'support'    => false,
                    ),
                );
                $bpmp_fs = ( BPMP_HAS_PRO && file_exists( $fsStartPath ) ? fs_dynamic_init( $bpmpConfig ) : fs_lite_dynamic_init( $bpmpConfig ) );
            }
            return $bpmp_fs;
        }

        bpmp_fs();
        do_action( 'bpmp_fs_loaded' );
    }
    function bpmpIsPremium() {
        return ( BPMP_HAS_PRO ? bpmp_fs()->can_use_premium_code() : false );
    }

    // ... Your plugin's main file logic ...
    if ( !class_exists( 'BPMPPlugin' ) ) {
        class BPMPPlugin {
            function __construct() {
                add_action( 'init', [$this, 'onInit'] );
                add_action( 'init', [$this, 'bpmp_register_audio_player_block_post_type'] );
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

            function onInit() {
                register_block_type( __DIR__ . '/build' );
            }

            function bpmp_register_audio_player_block_post_type() {
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

            function bpmp_add_demo_submenu() {
                add_submenu_page(
                    'edit.php?post_type=audio_player_block',
                    'Demo Page',
                    'Player Demo',
                    'manage_options',
                    'bpmp_demo_page',
                    [$this, 'bpmp_render_demo_page']
                );
            }

            function renderTemplate( $content ) {
                $parseBlocks = parse_blocks( $content );
                return render_block( $parseBlocks[0] );
            }

            function bpmp_render_demo_page() {
                ?>
				<div id="bplAdminPage" data-is-premium='<?php 
                echo esc_attr( bpmpIsPremium() );
                ?>' data-version="<?php 
                echo esc_attr( BPMP_VERSION );
                ?>">
					<div class='renderHere'></div>
					<div class="templates" style='display: none;'>
						<div class="default">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Neon Pulse","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}},{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","alt":"","title":"","caption":""}},{"title":"Eternal Symphony","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Rhythm \\u0026 Flow","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Golden Vibes","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}}],"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":32,"tablet":26,"mobile":23},"fontWeight":600},"colors":{"color":"#000","bg":"#0000"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#000","bg":"#0000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"rgba(0, 0, 0, .2)","progressColor":"#000"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"45px","playPauseSize":"60px","colors":{"color":"#a0a0a0","bg":"#0000"},"hovColors":{"color":"#9c9c9c","bg":"#d8d8d8"},"playPauseColors":{"color":"#fff","bg":"#000"},"playPauseHovColors":{"color":"#fff","bg":"#4c4343"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":22.4,"tablet":18,"mobile":15}},"colors":{"color":"#000","bg":"#0000"},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"advanced":{"dimension":{"padding":{"desktop":{"top":"16px","right":"32px","bottom":"16px","left":"32px"},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"},"shadow":[{"hOffset":"0px","vOffset":"0px","blur":"8px","color":"rgba(0, 0, 0, .4)"}]}},"background":{"normal":{"type":"color","color":"#fff"}}}} /-->' );
                ?>
						</div>
						<div class="theme2">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Green Chair","artist":"Lana Rivera","cover":{"id":2237,"url":"https://shamim.local/wp-content/uploads/2025/03/a.jpg","alt":"","title":"a","caption":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}}],"options":{"theme":"slider","isAutoPlay":false},"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":32,"tablet":26,"mobile":23},"fontWeight":600},"colors":{"color":"#fff","bg":"#0000"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#fff","bg":"#0000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"#c0acac","progressColor":"#DC6161"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"45px","playPauseSize":"55px","colors":{"color":"#fff","bg":"#0000"},"hovColors":{"color":"#fff","bg":"#4C3737"},"playPauseColors":{"color":"#fff","bg":"#0000"},"playPauseHovColors":{"color":"#fff","bg":"#4C3737"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":20,"tablet":18,"mobile":15}},"colors":{"color":"#fff","bg":"#0000"},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"width":"100%","advanced":{"dimension":{"padding":{"desktop":{"top":"16px","right":"32px","bottom":"16px","left":"32px"},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"","style":"","color":""},"radius":{"top":"15px","right":"15px","bottom":"15px","left":"15px"},"shadow":[{"hOffset":"","vOffset":"","blur":"","color":""}]}},"background":{"normal":{"type":"color","color":"#473C64"}}}} /-->' );
                ?>
						</div>
						<div class="theme3">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Aurora Nights","artist":"Ethan Cross","cover":{"id":2237,"url":"https://shamim.local/wp-content/uploads/2025/03/a.jpg","alt":"","title":"a","caption":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}}],"options":{"theme":"oneHaash","isAutoPlay":false},"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":32,"tablet":26,"mobile":23},"fontWeight":600},"colors":{"color":"#000","bg":"#0000"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#000","bg":"#0000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"#69696987","progressColor":"#B24B1B"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"50px","playPauseSize":"50px","colors":{"color":"#a0a0a0","bg":"#0000"},"hovColors":{"color":"#9c9c9c","bg":"#d8d8d8"},"playPauseColors":{"color":"#696969AD","bg":"#0000"},"playPauseHovColors":{"color":"#696969AD","bg":"#D8D8D8"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":18,"tablet":18,"mobile":15}},"colors":{"color":"#000","bg":"#0000"},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"width":"100%","advanced":{"dimension":{"padding":{"desktop":{"top":"16px","right":"16px","bottom":"16px","left":"16px"},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"1px","style":"solid","color":"#2C2785"},"radius":{"top":"7px","right":"7px","bottom":"7px","left":"7px"},"shadow":[{"hOffset":"","vOffset":"","blur":"","color":""}]}},"background":{"normal":{"type":"color","color":"#fff"}}}} /-->' );
                ?>
						</div>
						<div class="theme4">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Beyond the Stars","artist":"Ethan Cross","cover":{"id":2237,"url":"https://shamim.local/wp-content/uploads/2025/03/a.jpg","alt":"","title":"a","caption":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}}],"options":{"theme":"wooden","isAutoPlay":false},"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":20,"tablet":26,"mobile":23},"fontWeight":400},"colors":{"color":"#fff","bg":"#4C221AE3"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#000","bg":"#0000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"#0000","progressColor":"#000"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"45px","playPauseSize":"45px","colors":{"color":"#fff","bg":"#0000"},"hovColors":{"color":"#fff","bg":"#4E0606"},"playPauseColors":{"color":"#fff","bg":"#0000"},"playPauseHovColors":{"color":"#fff","bg":"#4E0606"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":20,"tablet":18,"mobile":15}},"colors":{"color":"#fff","bg":"#4F160AE3"},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"width":"500px","advanced":{"dimension":{"padding":{"desktop":{"top":"20px","right":"20px","bottom":"20px","left":"20px"},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"","style":"","color":""},"radius":{"top":"120px","right":"120px","bottom":"120px","left":"120px"},"shadow":[{"hOffset":"","vOffset":"","blur":"","color":""}]}},"background":{"normal":{"type":"color","color":"#571a0ee3"}}}} /-->' );
                ?>
						</div>
						<div class="theme5">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}},{"title":"Neon Pulse","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","alt":"","title":"","caption":""}},{"title":"Eternal Symphony","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Rhythm \\u0026 Flow","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Golden Vibes","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}},{"title":"Green Chair","artist":"Diego Nava","cover":{"id":null,"url":"","alt":"","title":"","link":""},"audio":{"id":null,"url":"","title":""}}],"options":{"theme":"lite","isAutoPlay":false},"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":32,"tablet":26,"mobile":23},"fontWeight":600},"colors":{"color":"#fff","bg":"#0000"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"#e6d8d8","progressColor":"#B24B1B"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"45px","playPauseSize":"45px","colors":{"color":"#fff","bg":"#0000"},"hovColors":{"color":"#fff","bg":"#2B3359"},"playPauseColors":{"color":"#fff","bg":"#0000"},"playPauseHovColors":{"color":"#fff","bg":"#2B3359"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":20,"tablet":18,"mobile":15}},"colors":{"color":"#fff","bg":"#0000"},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"width":"100%","advanced":{"dimension":{"padding":{"desktop":{"top":"30px","right":"30px","bottom":"30px","left":"30px"},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"","style":"","color":""},"radius":{"top":"15px","right":"15px","bottom":"15px","left":"15px"},"shadow":[{"hOffset":"","vOffset":"","blur":"","color":""}]}},"background":{"normal":{"type":"color","color":"#1c1c4a"}}}} /-->' );
                ?>
						</div>
						<div class="theme6">
							<?php 
                echo $this->renderTemplate( '<!-- wp:bpmp/mp3-player {"audioProperties":[{"title":"Mystic Dreams","artist":"Nova Harper","cover":{"id":2237,"url":"https://shamim.local/wp-content/uploads/2025/03/a.jpg","alt":"","title":"a","caption":""},"audio":{"id":62,"url":"https://shamim.local/wp-content/uploads/2024/12/Music-1.mp3","alt":"","title":"Man Aamadeh Am - PakiUM.Com","caption":""}}],"options":{"theme":"card","isAutoPlay":false},"style":{"height":{"desktop":"","tablet":"","mobile":""},"title":{"typo":{"fontSize":{"desktop":32,"tablet":26,"mobile":23},"fontWeight":600},"colors":{"color":"#000","bg":"#0000"}},"artist":{"typo":{"fontSize":{"desktop":22.4,"tablet":20,"mobile":18},"fontWeight":500},"opacity":1,"colors":{"color":"#000","bg":"#0000"}},"thumbnail":{"width":{"desktop":"100%","tablet":"","mobile":""},"sliderHeight":{"desktop":"","tablet":"","mobile":""},"border":{"width":"","style":"","color":""},"radius":{"top":"16px","right":"16px","bottom":"16px","left":"16px"}},"range":{"input":{"width":{"desktop":"100%","tablet":"100%","mobile":"100%"},"height":"4px","radius":{"top":"8px","right":"8px","bottom":"8px","left":"8px"},"color":"#0000","progressColor":"#000"},"thumb":{"width":"16px","color":"#EE714B","shadow":[],"outline":{"width":"4px","style":"solid","color":"white"},"radius":"50%"}},"controls":{"size":"50px","playPauseSize":"50px","colors":{"color":"#a0a0a0","bg":"#0000"},"hovColors":{"color":"#9c9c9c","bg":"#d8d8d8"},"playPauseColors":{"color":"#696969AD","bg":"#0000"},"playPauseHovColors":{"color":"#696969AD","bg":"#D8D8D8"},"border":[],"hovBorder":[]},"time":{"typo":{"fontSize":{"desktop":20,"tablet":18,"mobile":15}},"colors":{"color":"","bg":""},"radius":"50px"},"playlist":{"colors":{"bg":"#f9f9f9","color":"#111111"},"activeColors":{"bg":"rgba(145, 53, 53, 1)","color":"#ffffff","bgType":"solid","gradient":"linear-gradient(135deg,rgb(69,39,164) 0%,rgb(59,21,21) 64%,rgb(72,27,27) 83%,rgb(131,68,197) 100%)"},"seeMoreMusicBtncolors":{"bg":"rgba(69, 58, 106, 1)","color":"#fff"},"border":[],"radius":"5px"},"waveColors":{"normal":"#4527a4","lite":"#ab98e7"},"cardTheme":{"waveTop":{"desktop":-130,"tablet":-130,"mobile":-160},"wave2Top":{"desktop":-130,"tablet":-130,"mobile":-160},"wave3Top":{"desktop":-130,"tablet":-130,"mobile":-160}}},"width":"300px","advanced":{"dimension":{"padding":{"desktop":{"top":"","right":"","bottom":"","left":""},"tablet":{"top":"","right":"","bottom":"","left":""},"mobile":{"top":"","right":"","bottom":"","left":""}}},"borderShadow":{"normal":{"border":{"width":"","style":"","color":""},"radius":{"top":"","right":"","bottom":"","left":""},"shadow":[{"hOffset":"9px","vOffset":"7px","blur":"37px","spreed":"-6px","color":"#000000"}]}},"background":{"normal":{"type":"color","color":"#fff"}}}} /-->' );
                ?>
						</div>
					</div>
				</div>
				<?php 
            }

            function bpmp_audio_player_block_shortcode( $atts ) {
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

            function bpmp_admin_enqueue_script() {
                global $typenow;
                if ( 'audio_player_block' === $typenow ) {
                    // Loaded js files
                    wp_enqueue_script(
                        'view-js',
                        BPMP_DIR_URL . 'build/view.js',
                        [],
                        BPMP_VERSION,
                        true
                    );
                    // fs filesystem
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
                    wp_enqueue_script(
                        'adminHelpJs',
                        BPMP_DIR_URL . 'build/admin-help.js',
                        ['react', 'react-dom'],
                        BPMP_VERSION,
                        true
                    );
                    // Loaded css files
                    wp_enqueue_style(
                        'view-css',
                        BPMP_DIR_URL . 'build/view.css',
                        [],
                        BPMP_VERSION
                    );
                    wp_enqueue_style(
                        'admin-post-css',
                        BPMP_DIR_URL . 'build/admin-post.css',
                        [],
                        BPMP_VERSION
                    );
                    wp_enqueue_style(
                        'adminHelpCSS',
                        BPMP_DIR_URL . 'build/admin-help.css',
                        [],
                        BPMP_VERSION
                    );
                }
            }

        }

        new BPMPPlugin();
    }
}
<?php

if (!defined('ABSPATH')) exit;

if( !class_exists( 'BPMPPlugin' ) ){
    class BPMPPlugin{
        function __construct(){
            $this -> loaded_classes();
        }
 
        function loaded_classes(){
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/Init.php';
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/AdminMenu.php';
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/Enqueue.php';
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/ShortCode.php';
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/CustomColumn.php';
			require_once BPMP_DIR_PATH . 'includes/rootPlugin/inc/RestAPI.php';

			new BPMP\Init();
			new BPMP\AdminMenu();
			new BPMP\Enqueue();
			new BPMP\ShortCode();
			new BPMP\CustomColumn();
			new BPMP\RestAPI();
		}
        
    }
    new BPMPPlugin();
}
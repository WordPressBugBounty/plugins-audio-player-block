<?php

if (!defined('ABSPATH')) exit;

if( !class_exists( 'BPMPPlugin' ) ){
    class BPMPPlugin{
        function __construct(){
            $this -> loaded_classes();
        }
 
        function loaded_classes(){
			require_once BPMP_DIR_PATH . 'includes/Init.php';
			require_once BPMP_DIR_PATH . 'includes/AdminMenu.php';
			require_once BPMP_DIR_PATH . 'includes/Enqueue.php';
			require_once BPMP_DIR_PATH . 'includes/ShortCode.php';
			require_once BPMP_DIR_PATH . 'includes/CustomColumn.php';
			require_once BPMP_DIR_PATH . 'includes/Ajax.php';

			new BPMP\Init();
			new BPMP\AdminMenu();
			new BPMP\Enqueue();
			new BPMP\ShortCode();
			new BPMP\CustomColumn();
			new BPMP\Ajax();
		}
        
    }
    new BPMPPlugin();
}
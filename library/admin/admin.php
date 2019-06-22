<?php

namespace q\test\admin;

use q\test\core\core as core;
use q\test\core\helper as helper;

use q\test\email as email ;

// load it up ##
\q\test\admin\admin::run();

class admin extends \q_test {

    public static $output = false;

    public static function run()
    {

        // load templates ##
        self::load_libraries();

        if ( \is_admin() ) {

            // add Test menu ##
            // \add_action( 'admin_menu', array( get_class(), 'admin_menu' ) );

        }            

    }



    /*
    * Add submenu item
    *
    * @since      0.0.1
    */
    public static function admin_menu()
    {

        \add_submenu_page(
            'options-general.php',
            __('Q Test','q-textdomain'),
            __('Q Test','q-textdomain'),
            'manage_options',
            'q-test',
            [ get_class(), 'admin_menu_render' ]
        );

    }



    public static function admin_menu_render()
    {

        echo 'Q Test - pull in render methods from each active test module - log results from email ckeck in array inside text file ( email.log ), option to download / empty...';

        // crude ##
        #echo $output;
        email::render();

    }



    /**
    * Load Libraries
    *
    * @since        0.0.1
    */
    private static function load_libraries()
    {

        // plugins ##
        require_once self::get_plugin_path( 'library/admin/email.php' );
        require_once self::get_plugin_path( 'library/admin/log.php' );
        // require_once self::get_plugin_path( 'library/admin/ping.php' );
        // require_once self::get_plugin_path( 'library/admin/url.php' );

    }



}
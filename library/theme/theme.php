<?php

namespace q\test\theme;

use q\test\core\core as core;
use q\test\core\helper as helper;
use q\test\user\profile as profile;

// Q ##
// use q\core\core as q_core;
// use q\core\options as q_options;

// Q Theme ##
// use q\theme\core\core as theme_core;

// load it up ##
// \q\test\theme\theme::run();

class theme extends \q_test {

    public static $plugin_version;
    public static $theme_version;
    public static $options;

    public static function run()
    {

        // load up q_theme assets ##
        \add_action( 'plugins_loaded', [ get_class(), 'load_properties' ], 10 );

        if ( ! \is_admin() ) {

            // plugin css / js ##
            \add_action( 'wp_enqueue_scripts', array( get_class(), 'wp_enqueue_scripts' ), 1 );

            // theme css ##
            \add_action( 'wp_enqueue_scripts', array( get_class(), 'wp_enqueue_styles' ), 100000 );
 
        }

        // admin assets ##
        if ( \is_admin() ){

            // admin js ##
            \add_action( 'admin_enqueue_scripts', array( get_class(), 'admin_enqueue_scripts' ), 1 );

        }

        // load templates ##
        self::load_libraries();

    }


    
    /**
    * Load Properties
    *
    * @since        2.0.0
    */
    public static function load_properties()
    {

        // assign values ##
        self::$plugin_version = self::version ;
        self::$theme_version = \wp_get_theme()->get( 'Version' ) ? \wp_get_theme()->get( 'Version' ) : self::version ;

        // grab the options ##
        self::$options = q_options::get();
        #helper::log( self::$options );

    }



    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    private static function load_libraries()
    {

        // views ##
        // require_once self::get_plugin_path( 'library/theme/view/resource.php' );
        // require_once self::get_plugin_path( 'library/theme/view/members.php' );
        // require_once self::get_plugin_path( 'library/theme/view/generic.php' );
        // require_once self::get_plugin_path( 'library/theme/view/anspress.php' );
        // require_once self::get_plugin_path( 'library/theme/view/dashboard.php' );
        // require_once self::get_plugin_path( 'library/theme/view/grant.php' );

    }




    /**
    * include plugin assets
    *
    * @since        0.1.0
    * @return       __void
    */
    public static function wp_enqueue_scripts() {

        // club only faeture ##
        if ( ! theme_core::is_site( "club" ) ) {
            
            #helper::log( 'Not club.. template_redirect'  );

            return false;

        }

        // \wp_register_style( 'q-connect-css', helper::get( "theme/css/q.connect.css", 'return' ), array(), self::version, 'all' );
        // \wp_enqueue_style( 'q-connect-css' );

        // add JS ## -- after all dependencies ##
        \wp_enqueue_script( 'q-user-js', helper::get( "theme/javascript/q.user.js", 'return' ), array( 'jquery' ), self::version );
        \wp_enqueue_script( 'q-profile-js', helper::get( "theme/javascript/q.profile.js", 'return' ), array( 'jquery' ), self::version );        
        
        // nonce ##
        $nonce = \wp_create_nonce( 'q-user-nonce' );

        // pass variable values defined in parent class ##
        \wp_localize_script( 'q-user-js', 'q_user', array(
            'ajaxurl'           => \admin_url( 'admin-ajax.php', \is_ssl() ? 'https' : 'http' ), ## add 'https' to use secure URL ##
            'debug'             => self::$debug,
            'nonce'             => $nonce
        ));

        \wp_localize_script( 'q-profile-js', 'q_profile', array(
            'ajaxurl'           => \admin_url( 'admin-ajax.php', \is_ssl() ? 'https' : 'http' ), ## add 'https' to use secure URL ##
            'debug'             => self::$debug,
            'nonce'             => $nonce, 
            'animate'           => profile::is_own() ? 1 : 0
        ));

    }



    /**
    * include plugin admin assets
    *
    * @since        0.1.0
    * @return       __void
    */
    public static function admin_enqueue_scripts() {

        // club only faeture ##
        if ( ! theme_core::is_site( "club" ) ) {
            
            #helper::log( 'Not club.. template_redirect'  );

            return false;

        }

        // \wp_register_style( 'q-connect-css', helper::get( "theme/css/q.connect.css", 'return' ), array(), self::version, 'all' );
        // \wp_enqueue_style( 'q-connect-css' );

        // add JS ## -- after all dependencies ##
        \wp_enqueue_script( 'q-user-admin-js', helper::get( "theme/javascript/q.user.admin.js", 'return' ), array( 'jquery' ), self::version );
        // \wp_enqueue_script( 'q-profile-js', helper::get( "theme/javascript/q.profile.js", 'return' ), array( 'jquery' ), self::version );        
        
        // nonce ##
        $nonce = \wp_create_nonce( 'q-admin-nonce' );

        // pass variable values defined in parent class ##
        \wp_localize_script( 'q-user-admin-js', 'q_user_admin', array(
            'ajaxurl'           => \admin_url( 'admin-ajax.php', \is_ssl() ? 'https' : 'http' ), ## add 'https' to use secure URL ##
            'debug'             => self::$debug,
            'nonce'             => $nonce
        ));

    }



    /*
    * style enqueuer 
    *
    * @since  2.0
    */
    public static function wp_enqueue_styles() {

        // club only faeture ##
        if ( ! theme_core::is_site( "club" ) ) {
            
            #helper::log( 'Not club.. template_redirect'  );

            return false;

        }

        if ( TRUE === self::$options->theme_css ) {

            // add css ##
            #\wp_register_style( 'theme-club-css', \get_stylesheet_directory_uri() . '/style.club.css', '', self::$theme_version );
            #\wp_enqueue_style( 'theme-club-css' );

            \wp_register_style( 'theme-club-css', helper::get( 'theme/scss/theme.css', 'return' ), '', self::$theme_version );
            \wp_enqueue_style( 'theme-club-css' );

            // jquery UI - custom build ## 
            \wp_register_style( 'jquery-ui-theme-css-flat', helper::get( 'theme/css/jquery-ui-flat-theme.custom.min.css', 'return' ), '', '1.11.4' );
            \wp_enqueue_style( 'jquery-ui-theme-css-flat' );


        }



    }
    


}
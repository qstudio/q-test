<?php

/*
 * Plugin Name:     Q Test
 * Description:     Test suite
 * Version:         0.0.1
 * Author:          Q Studio
 * Author URI:      http://qstudio.us/
 * License:         GPL2
 * Class:           q_test
 * Text Domain:     q-test
*/

defined( 'ABSPATH' ) OR exit;

if ( ! class_exists( 'q_test' ) ) {
    
    // instatiate plugin via WP plugins_loaded - init is too late for CPT ##
    add_action( 'plugins_loaded', array ( 'q_test', 'get_instance' ), 6 );
    
    class q_test {
                
        // Refers to a single instance of this class. ##
        private static $instance = null;
                       
        // Plugin Settings
        const version = '0.0.1';
        const text_domain = 'q-test'; // for translation ##
        
        static $debug = true;
        // static $device; // current device ##
        // static $locale; // current locale ##
        
        /**
         * Creates or returns an instance of this class.
         *
         * @return  Foo     A single instance of this class.
         */
        public static function get_instance() 
        {

            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;

        }
        
        
        /**
         * Instatiate Class
         * 
         * @since       0.2
         * @return      void
         */
        private function __construct() 
        {
            
            // activation ##
            register_activation_hook( __FILE__, array ( $this, 'register_activation_hook' ) );

            // deactvation ##
            register_deactivation_hook( __FILE__, array ( $this, 'register_deactivation_hook' ) );

            // set text domain ##
            add_action( 'init', array( $this, 'load_plugin_textdomain' ), 1 );
            
            // define debug ##
            // self::$debug = 
            //     ( true === self::$debug ) ? 
            //     true : 
            //         class_exists( 'Q' ) ? 
            //         \Q::$debug : // use Q debug setting, as plugin property not active ##
            //         self::$debug ;

            // load libraries ##
            self::load_libraries();

            // hook up WP Cron test for email ##
            self::email();

        }


        // the form for sites have to be 1-column-layout
        public function register_activation_hook() {

            add_site_option( 'q_test_configured', true );

            // flush rewrites ##
            global $wp_rewrite;
            $wp_rewrite->flush_rules();

        }


        public function register_deactivation_hook() {

            delete_option( 'q_test_configured' );

        }


        
        /**
         * Load Text Domain for translations
         * 
         * @since       1.7.0
         * 
         */
        public function load_plugin_textdomain() 
        {
            
            // set text-domain ##
            $domain = self::text_domain;
            
            // The "plugin_locale" filter is also used in load_plugin_textdomain()
            $locale = apply_filters('plugin_locale', get_locale(), $domain);

            // try from global WP location first ##
            load_textdomain( $domain, WP_LANG_DIR.'/plugins/'.$domain.'-'.$locale.'.mo' );
            
            // try from plugin last ##
            load_plugin_textdomain( $domain, FALSE, plugin_dir_path( __FILE__ ).'languages/' );
            
        }
        
        
        
        /**
         * Get Plugin URL
         * 
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_url( $path = '' ) 
        {

            return plugins_url( $path, __FILE__ );

        }
        
        
        /**
         * Get Plugin Path
         * 
         * @since       0.1
         * @param       string      $path   Path to plugin directory
         * @return      string      Absoulte URL to plugin directory
         */
        public static function get_plugin_path( $path = '' ) 
        {

            return plugin_dir_path( __FILE__ ).$path;

        }
        

        /**
        * Load Libraries
        *
        * @since        2.0
        */
		private static function load_libraries()
        {

            // methods ##
            require_once self::get_plugin_path( 'library/core/helper.php' );
            require_once self::get_plugin_path( 'library/core/config.php' );
            require_once self::get_plugin_path( 'library/core/core.php' );

            // backend ##
            require_once self::get_plugin_path( 'library/admin/admin.php' );
            
            // plugin hooks and filters ##
            // require_once self::get_plugin_path( 'library/plugin/plugin.php' );

            // frontend ##
            // require_once self::get_plugin_path( 'library/theme/theme.php' );
            // require_once self::get_plugin_path( 'library/theme/template.php' );

        }




        /**
        * Schedule Email Cron check
        *
        */
        public static function email()
        {

            // add library ##
            // require_once self::get_plugin_path( 'library/admin/email.php' );

            // add geoid check cron event ##
            if ( ! \wp_next_scheduled ( 'q_test_hourly_email' ) ) {
                
                \wp_schedule_event(time(), 'hourly', 'q_test_hourly_email' );

            }

            // schedule geoid check ##
            \add_action( 'q_test_hourly_email', [ 'q\\test\\admin\\email', 'cron' ] );

        }

    }

}
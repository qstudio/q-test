<?php

/**
 * Check if email is working, log checks, provde a viewer and download option
 * 
 * 
 */

namespace q\test\admin;

use q\test\core\core as core; 
use q\test\core\helper as helper;
use q\test\admin\log as log;

\q\test\admin\email::run();

class email extends \q_test {
    

    public static function run()
    {

        // error logging ##
        \add_action( 'admin_init', array( get_class(), 'setup' ), 1 );

        if ( \is_admin() ) {

            // add Email menu ##
            \add_action( 'admin_menu', array( get_class(), 'admin_menu' ), 1000 );
    
            // allow cron method to be tested via http GET request  ##
            if ( 
                isset( $_GET['q_test'] ) 
                && $_GET['q_test'] == 'email' 
            ) {

                \add_action( 'wp_loaded', [ get_class(), 'cron' ] );

            }

        }

    }


    /**
     * Run once a day to check if log files are set-up correctly
     * 
     * @since 0.0.1
     */
    public static function setup() 
    {

        // crash it ##
        // \delete_site_transient( 'q/test/email/log/check' );

        if ( false === ( $check = \get_site_transient( 'q/test/email/log/check' ) ) ) {

            helper::log( 'setting up email log check...' );

            // set-up log ##
            log::args([
                // empty for now ##
            ]);

            // run the logger ##
            log::run();

            \set_site_transient( 'q/test/email/log/check', true, 24 * HOUR_IN_SECONDS );
        
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
            __('Q Test : Email','q-textdomain'),
            __('Q Test : Email','q-textdomain'),
            'manage_options',
            'q-test-email',
            [ get_class(), 'render' ]
        );

    }



    public static function render()
    {

        // set-up log ##
        log::args([
            // empty for now ##
        ]);

        // run log render ##
        log::render();

    }




    /**
     * Cron check to see if email is deliverable via stored SMTP settings
     * Run once every hour or directly via http GET request
     *
     * 
     * @since   0.0.01
     * @return  void
     */
    public static function cron()
    {

        // helper::log( 'debugging: '.self::$debug );

        // bulk on localhost ##
        if ( 
            false === self::$debug
            && (
                helper::is_localhost() 
                // || helper::is_staging()
            )
        ) { 

            helper::log( 'Email Check blocked by debugging or domain settings...' );
            
            return false; 
        
        }

        // set-up log ##
        log::args([
            // empty for now ##
        ]);
        
        // empty array ##
        $debug = [];

        // run test to see if email can be delivered ##
        $debug = self::test();

        // grab data from buffer ##
        ob_start();
        var_dump($debug);
        $debug_data = ob_get_clean();

        // helper::log( 'Log finished..' );
        // helper::log( $debug_data );

        // email -- ironic ##
        \wp_mail( 'ray@qstudio.us', 'Cron : Q Test Email', $debug_data );

    }




    public static function test( $url = null )
    {

        // run test ##
        if ( ! class_exists( 'EasyWPSMTP' ) ) {

            helper::log( 'SMTP class missing, no way to run test...' ) ;

            return false;

        }

        // get instance of SMTP control class ##
        $EasyWPSMTP	= \EasyWPSMTP::get_instance();

        // test ##
        $results = $EasyWPSMTP->test_mail( 'wordpress@greenheart.org', 'Q Test Email', 'Test message...' );

        // helper::log( $results );

        // response is messy, let's clean it up ##
        $response = 
            isset( $results['error'] ) && isset( $results['debug_log'] ) ? 
            $results['debug_log'] :
            'Test email was successfully sent. No errors occurred during the process.' ;
        
        // clean up ##
        $response = str_replace( array( "\n", "\t", "\r" ), ' - ', $response );

        // helper::log( $response );

        // compile data ##
        $array = [ 
            'status'    => isset( $results['error'] ) ? 'ERROR' : 'WORKING',
            // 'code'      => '200', // @todo ##
            'response'  => $response,
        ];

        // write to the log file ##
        log::write( $array['status'].' --> '.$array['response'] );

        // if we found an error, we need to try and open a tas in Asana ##
        if( 'ERROR' == $array['status'] ) {

            self::asana_email_create_task([
                'response' => $array['status'].' --> '.$array['response']
            ]);

        }

        // kick it back ##
        return $array;

    }




    /**
     * Create task in Asana via API
     * 
     * https://asana.com/developers/api-reference/tasks#create
     * https://github.com/Asana/php-asana
     */
    public static function asana_api_create_task()
    {}



    /**
     * Create task in Asana via Email
     * 
    Add tasks by email
    You can add a task to this list by sending an email to:
    x+310727860574480@mail.asana.com
    The subject will be the task name
    The body will be the task description
    All email attachments will be attached to the task
    You can cc teammates to add them as task followers
    Learn more from our Asana Guide article.
     */
    public static function asana_email_create_task( Array $args = null )
    {

        // sanity ##
        if ( 
            is_null( $args )
            || ! is_array( $args )
            || ! isset( $args['response'] )
        ) {

            helper::log( 'Error in passed araguments...' );

            return false;

        }

        // content ##
        $content = $args['response'];

        // headers --- CLUCKY.. ##
        $headers =  'MIME-Version: 1.0' . "\r\n"; 
        $headers .= 'From: Web Team<mgurner@greenheart.org>' . "\r\n";
        $headers .= 'Content-Type: text/plain; charset=\"utf-8\"\r\n' . "\r\n"; 
        $headers .= "Reply-To: wordpress@greenheart.org\r\n";
        $headers .= 'Cc: btoth@greenheart.org' . "\r\n";

        // Create Asana task via email ##
        $email = mail(
            
            'x+310727860574480@mail.asana.com',
            'Email Delivery Error',
            $content,
            $headers

        );

        // log ##
        helper::log( 'Email sent to Asana: '.$email );

        // kick back ##
        return true;

    }

}
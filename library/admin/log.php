<?php

namespace q\test\admin;

use q\test\core\core as core; 
use q\test\core\helper as helper;

class log extends \q_test {
    
    public static $defined = false ;
    public static $file = false ; // helper::get( 'logs/email.log' ); // replaces Q_SUPPORT_LOG_FILE ##
    public static $path = false ;
    public static $url = false ;
    public static $max_size = false;

    public static function args( Array $args = null )
    {

        self::$file = isset( $args['file'] ) ? $args['file'] : 'email.log'; // helper::get( 'logs/email.log' ); ##
        self::$path = isset( $args['path'] ) ? self::get_plugin_path( $args['path'] ) : self::get_plugin_path( 'library/logs/' );
        self::$url = isset( $args['url'] ) ? self::get_plugin_url( $args['url'] ) : self::get_plugin_url( 'library/logs/' );
        self::$max_size = isset( $args['max_size'] ) ? $args['max_size'] : '1024' ;

        // update tracker ##
        self::$defined = true ;

    }



    /**
     * Set Error Handlers
     * 
     * @since       0.3
     * @link        http://us1.php.net/set_error_handler
     * @ return     void
     */
    public static function run()
    {

        // check if args re defined ##
        if ( ! self::$defined ) {

            // we can stop right now ##
            helper::log( 'Log settings not defined correctly' );

            return false;

        }
        
        // create the error log file ##
        self::make();
        
    }
    


    
    /**
     * Make Error Log
     * 
     * @since       0.3
     * @return      void
     */
    public static function make() 
    {
        
        // get error log details ##
        $check = self::check();

        // helper::log( $check );
                
        if ( $check["file_exists"] === false ) {
            
            $make_error_log = @fopen( self::$path.self::$file, 'w' );
            
            if ( $make_error_log === false ) {
                
                helper::log( 'Error creating log file' );

                return false;
                
            }
            
        }

    }
    

    
    /**
     * Check Error Log
     * 
     * @since       0.3
     * @return      Array     data about error log ##
     */
    public static function check() 
    {
        
        $check = array();

        $check['file'] = self::$path.self::$file;
        $check["filesize"] = 0; // default to zero ##
        $check["file_exists"] = false; // default to no ##
        
        if ( file_exists( self::$path.self::$file ) ) {

            // ok ! ##
            $check["file_exists"] = true;

            // grab size ##
            $check["filesize"] = round( filesize( self::$path.self::$file ) / 1024 );
            
        }
        
        if ( is_readable( self::$path.self::$file ) ) {
            
            $check["is_readable"] = true;

        }
        
        if ( is_writable( self::$path.self::$file ) ) {
            
            $check["is_writable"] = true;

        }
        
        // archive file, if it's too big ##
        if ( $check["filesize"] > self::$max_size ) {
            
            $check["filesize"] = 'Larger than '.self::$max_size;

            self::rename();
            
        }
        
        return $check;
        
    }



    
    /**
     * Rename Error Log
     * 
     * @since       0.3
     * @return      boolean     True or False ##
     */
    public static function rename() 
    {
        
        // get todays date ##
        $now = date( "Y-m-d-H-i-s", strtotime( "now" ));
        
        helper::log( 'Renamed log file: '.$now );

        // do some renaming ##
        return @rename( self::$path.self::$file, self::$path.$now.'_email.log' );
        
    }        
    


    /**
     * write to log file
     * 
     * @since       0.3
     * 
     * @return      void
     */
    public static function write( String $string = null ) 
    {

        // sanity ##
        if ( is_null( $string ) ) {

            helper::log( 'Passed string variable empty...' );

            return false;

        }

        $now = new \DateTime();
        $now->setTimezone( new \DateTimeZone( 'America/Chicago' ) ); // set locale ##

        // compile ##
        $message = $now->format( 'Y-m-d H:i:s' )." - ".$string;

        // write ##
        $return = file_put_contents( self::$path.self::$file, $message . PHP_EOL, FILE_APPEND );

        // helper::log( 'Write debug: '.$return );

        // Don't execute PHP internal error handler ##
        return false;
        
    }

    

    /**
     * Delete Error Log
     * 
     * @since       0.3
     * @return      void
     */
    public static function delete() 
    {
        
        #@unlink( self::$path.self::$file );
        
        // get todays date ##
        $now = date( "Y-m-d-H-i-s", strtotime( "now" ));
        
        $files = glob( self::$path.'*' ); // get all log files ##
        foreach ( $files as $file ){ // iterate files ##
            if ( is_file( $file ) ) {

                helper::log( 'Deleted log file: '.$now );

                @unlink( $file ); // delete file ##
            }
        }
        
    }        
    
    
    
    
    /**
     * Get Error Log contents
     * 
     * @since       0.3
     * @return      array   Containing sanitized content from error log ##
     */
    public static function render( $lines = 26, $length = 60, $file = null ) 
    {
        
        // quick check on who's viewing ##
        if ( ! current_user_can( 'manage_options' ) ) { 
            
            wp_die( _e( "You do not have sufficient permissions to access this page.", "q-textdomain" ) );
            
        }
        
        if ( !$file ) { $file = self::$path.self::$file; } // default to plugin error log file ##
        if ( !$lines ) { $lines = 21; } // lines backup - WHY?? ##
        
        // we double the offset factor on each iteration ##
        $multiplier = 1;
        
        // add slashes -- required for winNT? ##
        #$file = addslashes( $file );
        
        // get the size of the file ##
        $bytes = file_exists( $file ) ? filesize( $file ) : 0; 
        
        // helper::log( $bytes. ' / ' . $file );
        
        // not yet complete ##
        $complete = FALSE;
        
        // start a new array ##
        $log = array();
        
        // open it up ##
        $fp = fopen( $file, "r" ) or wp_die( _e( "Can't open $file", "q-textdomain" ) );
        
        while ( $complete === FALSE ) {
            
            //seek to a position close to end of file
            $offset = ( (int)$lines * (int)$length * (int)$multiplier );
            #echo "offset: {$offset} / lines: {$lines} / length {$length} / multiplier {$multiplier} <br />";
            fseek( $fp, -$offset, SEEK_END );

            // we might seek mid-line, so read partial line
            // if our offset means we're reading the whole file, 
            // we don't skip...
            if ( $offset < $bytes ) {
                fgets( $fp );
            }
            
            // read all following lines, store last x ##
            while ( !feof( $fp ) ) {
                
                $line = fgets( $fp );
                #echo($line);
                array_push( $log, $line );
                #$log[] = $line;
                if ( count( $log ) > $lines ) {
                    array_shift( $log );
                    $complete = TRUE;
                }
                
            }
            
            #echo count($log);
            
            // if we read the whole file, we're done, even if we don't have enough lines ##
            if ( $offset >= $bytes ) {
                $complete = TRUE;
            } else {
                $multiplier *= 2; //otherwise let's seek even further back
            }
            
        }
        
        // tidy up ##
        fclose( $fp );
        
        // flip the error log - perhaps a bit controversial !! ##
        $log = array_reverse($log);
        
        // format to print ##
        $log_format = '';
        
        // counter ##
        $count = 0;
        
        // download log option ##
        $log_downloadable = true;
        
        // empty log - good work !! ##
        if ( ! array_filter( $log ) ) {
            
            $log_format = "<p class='q_support_log_format'>".__( 'Email Log is empty', 'q-textdomain' )."</p>";
            
            // no point in downloading nothing ##
            $log_downloadable = false;
            
        } else {
        
            // clean up the array ##
            foreach( $log as $key => $value ) {

                // remove empty rows ##
                if( is_null($value) || $value == '' || $value === false ) {

                    unset( $log[$key] );

                // format nicely ##
                } else {

                    $log_format .= "<p class='q_support_log_format'>{$value}</p>";

                }

                // iterate counter ##
                $count++;

            }
        
        }
        
        // open wrap ##
        echo '<div class="wrap q_support_wrap">';
        
        // icon and h2 ##
        echo '<h2>'; _e("Email Log"); echo '</h2>';
        
        // intro blurb ##
        printf( 
            '<p>Here are the last 25 entries ( in reverse order ) from the Email Log, you can view the entire file using the link at the bottom, if the file has data.</p>'
            // ,esc_url( Q_SUPPORT_DOMAIN.'plugin/error-log/' )
            // ,esc_html( __("Documentation", "q-textdomain" ) ) 
        );

        // basic css ##
        echo '
        <style>
        p.q_support_log_format {
            margin: 0;
            border-bottom: 1px solid #ddd;
            padding: 0.2em 0 0.5em;
        }
        </style>';
        
        // dump it ##
        echo ( $log_format );
        
        // link to error log ##
        if ( $log_downloadable ) {
        
            printf( 
                '<a href="%s" class="button" target="_blank" style="margin-top: 20px;">%s ( %s )</a>.'
                ,esc_url( self::$url.self::$file )
                ,esc_html( __("View Full Log File", "q-textdomain" ) )
                ,esc_html( round( $bytes / 1024 )." kb" )
            );

            printf( 
                '<a href="%s" class="button" style="margin-top: 20px;">%s</a>.'
                ,   '#'
                ,   esc_html( __("Empty Log File", "q-textdomain" ) )
            );
        
        }
        
        // close wrap ##
        echo '</div>';
        
    }

    
}
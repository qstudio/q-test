<?php

namespace q\test\admin;

#use q\test\core\helper as helper;

// load it up ##
\q\test\admin\ajax::run();

class ajax extends \q_test {

    public static function run()
    {

        // ajax user / connect calls ##
        \add_action( 'wp_ajax_q_user_connection', array( 'q\\club\\user\\connect', 'ajax_connection' ) );
        #\add_action( 'wp_ajax_nopriv_q_user_connection', array( 'q\\club\\user\\connect', 'ajax_connection' ) );

    }

}
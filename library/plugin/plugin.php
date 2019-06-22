<?php

namespace q\test\plugin;

use q\test\core\core as core;
use q\test\core\helper as helper;

// load it up ##
// \q\test\plugin\plugin::run();

class plugin extends \q_test {

    public static function run()
    {

        // load templates ##
        self::load_libraries();

    }


    /**
    * Load Libraries
    *
    * @since        2.0.0
    */
    private static function load_libraries()
    {

        // plugins ##
        // require_once self::get_plugin_path( 'library/plugin/acf.php' );
        // require_once self::get_plugin_path( 'library/plugin/q-report.php' );
        // require_once self::get_plugin_path( 'library/plugin/anspress.php' );

    }

}
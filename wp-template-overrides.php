<?php
/**
 * Plugin Name: WP Template Overrides
 * Version: 0.2
 * Description: A plugin to assist in minor template tweaks, without all the hassle.
 * Author: Tiger Strike Media
 * Author URI: http://www.tigerstrikemedia.com
 * License: GPLv2 or later
 */

class WP_Template_Overrides{

    var $overrides;

    var $option_hook;

    var $override_file_location;

    /**
     * Construct The Plugin Class
     */
    function __construct(){

        $this->overrides = get_option( 'WPTO_OVERRIDES' );

        $this->override_file_location = WP_CONTENT_DIR . '/theme-overrides/';

        $this->plugin_defines();

        $this->setup_filters();

        $this->setup_actions();

    }

    /**
     * Generic Plugin Defines.
     */
    function plugin_defines(){

        define( 'WPTO_PLUGIN_PATH', trailingslashit( WP_PLUGIN_DIR.'/' . str_replace(basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) ) );
        define( 'WPTO_PLUGIN_URL' , trailingslashit( WP_PLUGIN_URL.'/' . str_replace(basename( __FILE__ ), "", plugin_basename( __FILE__ ) ) ) );

    }

    /**
     * Setup The WordPress Filters
     */
    function setup_filters(){

        add_filter( 'template_include', array( $this, 'template_include' ) );

    }

    /**
     * initialise the appropriate actions for WordPress
     */
    function setup_actions(){

        add_action( 'admin_init', array( $this, 'admin_init' ) );

        add_action( 'admin_menu'           , array( $this, 'admin_menu'            ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'plugins_loaded'       , array( $this, 'plugins_loaded'        ) );

        //admin-ajax Actions
        add_action( 'wp_ajax_wpto_get_templates_for_theme', array( $this, 'ajax_get_templates_for_theme' ) );
        add_action( 'wp_ajax_wpto_get_file_contents'      , array( $this, 'ajax_get_file_contents'       ) );
        add_action( 'wp_ajax_wpto_save_override'          , array( $this, 'ajax_save_override'           ) );
        add_action( 'wp_ajax_wpto_update_override'        , array( $this, 'ajax_update_override'         ) );

    }

    /**
     * Admin Init Hook.
     */
    function admin_init(){

        //handle Deletion.
        if( isset( $_GET['delete_template_override'] ) ){

            $key = wp_kses( $_GET['delete_template_override'], array() );

            $nonce = wp_kses( $_GET['_nonce'], array() );

            if( wp_verify_nonce( $nonce, 'delete_template_override_' . $key ) && current_user_can( 'manage_options' ) ){

                //Delete The File
                unlink( $this->overrides[$key]['file'] );

                //Remove the array key.
                unset( $this->overrides[$key] );

                //Save It
                update_option( 'WPTO_OVERRIDES', $this->overrides );

                wp_redirect( remove_query_arg( array( 'delete_template_override', '_nonce', 'edit_override' ) ) );
                exit;

            }

        }

    }


    /**
     * Hook into admin_menu to add the options page.
     */
    function admin_menu(){

        //add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function = '' )
        $this->option_hook = add_management_page( __( 'WP Template Overrides', 'wp-template-overrides' ), __( 'Template Overrides', 'wp-template-overrides' ), 'manage_options', 'wp-template-overrides.php', array( $this, 'options_page' ) );

    }

    /**
     * Enqueue appropriate scripts in wp-admin
     *
     * @param $hook
     */
    function admin_enqueue_scripts( $hook ){

        if( $hook === $this->option_hook ){
            //wp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false )
            wp_enqueue_script( 'ace', WPTO_PLUGIN_URL . 'lib/ace/ace.js', array(), '1', true );
            wp_enqueue_script( 'wpto-init', WPTO_PLUGIN_URL . 'assets/js/init.js', array(), 1, true );
        }

    }

    /**
     * Hook for plugins_loaded to load the I18N text domain.
     */
    function plugins_loaded(){
        load_plugin_textdomain( 'wp-template-overrides', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
    }

    /**
     * Hook to override template include function.
     *
     * @param $template
     */
    function template_include( $template ){

        $theme = get_stylesheet();

        $key = md5( $template . $theme );

        if( isset( $this->overrides[$key] ) ){
            return $this->overrides[$key]['file'];
        }

        return $template;
    }

    /**
     * Include the options page HTML file
     */
    function options_page(){
        include WPTO_PLUGIN_PATH . 'inc/wpto-admin-page.php';
    }

    /**
     * Retrieve the template files for the selected theme
     *
     * @return bool
     */
    function ajax_get_templates_for_theme(){

        if( ! defined( 'DOING_AJAX' ) ) return false;

        $nonce = $_POST['nonce'];

        if( ! $nonce || ! wp_verify_nonce( $nonce, 'get_templates_for_theme' ) ) die(-1);

        $theme = wp_get_theme( wp_kses( $_POST['theme'] ,array() ) );

        if ( ! $theme->exists() || $theme->errors() )
            die( -1 );

        $allowed_files = $theme->get_files( 'php', 1 );

        header('Content-Type: application/json');
        echo json_encode( $allowed_files );

        exit;
    }

    /**
     * Retrieve the file contents for the selected file.
     *
     * @return bool
     */
    function ajax_get_file_contents(){

        if( ! defined( 'DOING_AJAX' ) ) return false;

        $nonce = $_POST['nonce'];

        if( ! $nonce || ! wp_verify_nonce( $nonce, 'get_file_content' ) ) die(-1);

        $filename = wp_kses( $_POST['file'] , array() );

        if( ! is_readable( $filename ) ) die( -1 );

        $file = file_get_contents( $filename );

        if( $file ){
            echo $file;
        } else {
            echo 'UNABLE TO READ FILE';
        }

        exit;
    }

    /**
     * Save the desired override.
     */
    function ajax_save_override(){

        if( ! defined( 'DOING_AJAX' ) ) return false;

        $nonce = $_POST['nonce'];

        if( ! $nonce || ! wp_verify_nonce( $nonce, 'save_override' ) ) die(-1);

        $original_file  = wp_kses( $_POST['file'], array() );
        $name           = wp_kses( $_POST['name'], array() );
        $theme          = wp_kses( $_POST['theme'], array() );
        $content        = stripslashes( $_POST['content'] );

        if( ! $this->ensure_directory_available() ){
            echo __( 'Unable to write to the overrides directory, please ensure that the webserver can write to the directory wp-content/theme-overrides/.', 'wp-template-overrides' );
            exit;
        }

        $filename = $theme . '-' . basename( $original_file );

        $file = $this->override_file_location . $filename;

        touch( $file );
        file_put_contents( $file, $content );

        if( ! file_exists( $file ) ){
            echo __( 'Unable to save override file. Please ensure that the webserver has appropriate permissions.', 'wp-template-overrides' );
            exit;
        }

        if( ! $this->overrides ) $this->overrides = array();

        //Get A Unique Key
        $key = md5( $original_file . $theme );

        $data = array(
            'name'          => $name,
            'file'          => $file,
            'theme'         => $theme,
            'original_file' => $original_file,
            'key'           => $key
        );

        if( ! array_key_exists( $key, $this->overrides ) ){

            $this->overrides[$key] = $data;

            update_option( 'WPTO_OVERRIDES', $this->overrides );

            echo __( 'The override has been saved.', 'wp-template-overrides' );

        } else {
            echo __( 'That override already exists.', 'wp-template-overrides' );
        }

        exit;
    }

    /**
     * Update the given override
     */
    function ajax_update_override(){

        if( ! defined( 'DOING_AJAX' ) ) return false;

        $nonce = $_POST['nonce'];

        if( ! $nonce || ! wp_verify_nonce( $nonce, 'update_override' ) ) die(-1);

        $key = wp_kses( $_POST['key'], array() );

        if( ! isset( $this->overrides[$key] ) ){
            echo 'Override not found.';
            exit;
        }

        $override = $this->overrides[$key];

        $content = stripslashes( $_POST['content'] );

        file_put_contents( $override['file'], $content );

        echo __( 'Override has been updated.', 'wp-template-overrides' );

        exit;

    }

    /**
     * Ensure we have a directory to put our override files.
     *
     * @return bool
     */
    function ensure_directory_available(){

        if( ! is_dir( $this->override_file_location ) ){
            @mkdir( $this->override_file_location );

            if( is_dir( $this->override_file_location ) ){

                touch( $this->override_file_location . '.htaccess' );

                if( is_writable( $this->override_file_location . '.htaccess' ) ){

                    $lines = array(
                        'order deny,allow',
                        'deny from all',
                        'allow from none'
                    );

                    $fp = fopen( $this->override_file_location . '.htaccess', 'w' );
                    foreach( $lines as $line ){
                        fwrite( $fp, $line . "\r\n" );
                    }
                    fclose( $fp );

                }

            }

        }

        if( is_dir( $this->override_file_location ) && is_writable( $this->override_file_location ) ){
            return true;
        } else {
            return false;
        }

    }

}
$GLOBALS['WP_Template_Overrides'] = new WP_Template_Overrides();
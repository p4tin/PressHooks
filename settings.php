<?php

class PressHookSettings_Page
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin',
            'PressHooks',
            'manage_options',
            'presshooks-options',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'presshooks_name' );
        ?>
        <div class="wrap">
            <h1>PressHooks Options</h1>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'presshooks_group' );
                do_settings_sections( 'presshooks-options' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'presshooks_group', // Option group
            'presshooks_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'Destination', // Title
            array( $this, 'print_section_info' ), // Callback
            'presshooks-options' // Page
        );

        add_settings_field(
            'target_id', // ID
            'Target URL', // Title
            array( $this, 'target_url_callback' ), // Callback
            'presshooks-options', // Page
            'setting_section_id' // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     * @return null
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['target_id'] ) )
            $new_input['target_id'] = $input['target_id'];

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'Enter your settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function target_url_callback()
    {
        printf(
            '<input type="text" id="target_id" name="presshooks_name[target_id]" size="96" value="%s" />',
            isset( $this->options['target_id'] ) ? esc_attr( $this->options['target_id']) : ''
        );
    }
}

if( is_admin() ) {
    $presshook_settings_page = new PressHookSettings_Page();
}

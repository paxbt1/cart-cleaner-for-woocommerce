<?php

class cart_freeze_admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this,'stock_freeze_menu'));

        add_action('admin_init', array($this,'stock_freeze_settings_init'));

    }

    // Register the admin menu item
    public function stock_freeze_menu()
    {
        add_menu_page('Stock Freeze Settings', 'Stock Freeze', 'manage_options', 'stock-freeze-settings', array($this,'stock_freeze_settings_page'));
    }

    // Create the plugin settings page
    public function stock_freeze_settings_page()
    {
        ?>
    <div class="wrap">
        <h2>Stock Freeze Settings</h2>
        <form method="post" action="options.php">
            <?php
                settings_fields('stock_freeze_settings');
        do_settings_sections('stock-freeze-settings');
        submit_button();
        ?>
        </form>
    </div>
    <?php
    }

    // Define and register the setting for stock-freeze-time
    public function stock_freeze_settings_init()
    {
        register_setting('stock_freeze_settings', 'stock_freeze_time', 'intval');
        add_settings_section('stock_freeze_section', 'Stock Freeze Time', array($this,'stock_freeze_section_callback'), 'stock-freeze-settings');
        add_settings_field('stock_freeze_time', 'Minutes', array($this,'stock_freeze_stock_freeze_time_callback'), 'stock-freeze-settings', 'stock_freeze_section');
    }

    // Callback functions for the settings page
    public function stock_freeze_section_callback()
    {
        echo 'Set the Stock Freeze Time (in minutes):';
    }

    public function stock_freeze_stock_freeze_time_callback()
    {
        $stock_freeze_time = get_option('stock_freeze_time');
        echo '<input type="number" name="stock_freeze_time" value="' . esc_attr($stock_freeze_time) . '" />';
    }

}

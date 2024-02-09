<?php

class CartCleanAdmin
{
    public function __construct()
    {
        // Constructor: Initializes admin menu and settings page actions.
        add_action('admin_menu', array($this, 'cart_clean_menu'));
        add_action('admin_init', array($this, 'cart_clean_settings_init'));
    }

    // Register the admin menu item
    public function cart_clean_menu()
    {
        // cart_clean_menu: Registers the admin menu item.
        add_menu_page(esc_html__('Cart Clean Settings', 'auto-cart-cleaner-for-wooCommerce'), esc_html__('Cart Clean', 'auto-cart-cleaner-for-wooCommerce'), 'manage_options', 'cart-clean-settings', array($this, 'cart_clean_settings_page'));
    }

    // Create the plugin settings page
    public function cart_clean_settings_page()
    {
        // cart_clean_settings_page: Renders the plugin settings page.
        ?>
        <div class="wrap">
            <h2><?php  esc_html__('Cart Clean Settings', 'auto-cart-cleaner-for-wooCommerce');?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('cart_clean_settings');
        do_settings_sections('cart-clean-settings');
        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    // Define and register the setting for cart-clean-time and stock_quantity_sync
    public function cart_clean_settings_init()
    {
        // cart_clean_settings_init: Initializes and registers plugin settings.

        // Register the cart clean time setting
        register_setting('cart_clean_settings', 'cart_clean_time', 'intval');
        add_settings_section('cart_clean_section', esc_html__('Cart Clean Time', 'auto-cart-cleaner-for-wooCommerce'), array($this, 'cart_clean_section_callback'), 'cart-clean-settings');
        add_settings_field('cart_clean_time', esc_html__('Minutes', 'auto-cart-cleaner-for-wooCommerce'), array($this, 'cart_clean_cart_clean_time_callback'), 'cart-clean-settings', 'cart_clean_section');

        // Register the Stock Quantity Sync setting as a checkbox
        register_setting('cart_clean_settings', 'stock_quantity_sync', 'intval');
        add_settings_section('stock_quantity_sync_section', esc_html__('Stock Quantity Sync', 'auto-cart-cleaner-for-wooCommerce'), array($this, 'stock_quantity_sync_section_callback'), 'cart-clean-settings');
        add_settings_field('stock_quantity_sync', esc_html__('Enable Stock Quantity Sync', 'auto-cart-cleaner-for-wooCommerce'), array($this, 'stock_quantity_sync_callback'), 'cart-clean-settings', 'stock_quantity_sync_section');
    }

    // Callback functions for the settings page
    public function cart_clean_section_callback()
    {
        // cart_clean_section_callback: Callback for the Cart Clean Time section.
        echo esc_html__('Set the Cart Clean Time (in minutes):', 'auto-cart-cleaner-for-wooCommerce');
    }

    public function cart_clean_cart_clean_time_callback()
    {
        // cart_clean_cart_clean_time_callback: Callback for the Cart Clean Time setting field.
        $cartCleanTime = get_option('cart_clean_time');
        echo '<input type="number" name="cart_clean_time" value="' . esc_attr($cartCleanTime) . '" />';
    }

    public function stock_quantity_sync_section_callback()
    {
        // stock_quantity_sync_section_callback: Callback for the Stock Quantity Sync Quantity section.
        echo esc_html__('Stock Quantity Sync : Enable this option to dynamically adjust product stock quantities when items are added to the cart, and automatically return them to their original levels when items are removed.', 'auto-cart-cleaner-for-wooCommerce');
    }

    public function stock_quantity_sync_callback()
    {
        // stock_quantity_sync_callback: Callback for the Stock Quantity Sync checkbox field.
        $stock_quantity_sync = get_option('stock_quantity_sync');
        $checked = checked(1, $stock_quantity_sync, false); // Check the checkbox if the option is 1 (enabled).
        echo '<label for="stock_quantity_sync"><input type="checkbox" id="stock_quantity_sync" name="stock_quantity_sync" value="1" ' . $checked . ' /> Enable</label>';
    }
}

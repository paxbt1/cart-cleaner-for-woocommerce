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
        add_menu_page(esc_html__('Cart Clean Settings', 'CCforWoocommerce'), esc_html__('Cart Clean', 'CCforWoocommerce'), 'manage_options', 'cart-clean-settings', array($this, 'cart_clean_settings_page'));
    }

    // Create the plugin settings page
    public function cart_clean_settings_page()
    {
        // cart_clean_settings_page: Renders the plugin settings page.
        ?>
        <div class="wrap">
            <h2><?= esc_html__('Cart Clean Settings', 'CCforWoocommerce');?></h2>
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

    // Define and register the setting for cart-clean-time and stock-freeze-quantity
    public function cart_clean_settings_init()
    {
        // cart_clean_settings_init: Initializes and registers plugin settings.

        // Register the cart clean time setting
        register_setting('cart_clean_settings', 'cart_clean_time', 'intval');
        add_settings_section('cart_clean_section', esc_html__('Cart Clean Time', 'CCforWoocommerce'), array($this, 'cart_clean_section_callback'), 'cart-clean-settings');
        add_settings_field('cart_clean_time', esc_html__('Minutes', 'CCforWoocommerce'), array($this, 'cart_clean_cart_clean_time_callback'), 'cart-clean-settings', 'cart_clean_section');

        // Register the stock freeze quantity setting as a checkbox
        register_setting('cart_clean_settings', 'stock_freeze_quantity', 'intval');
        add_settings_section('stock_freeze_section', esc_html__('Stock Freeze Quantity', 'CCforWoocommerce'), array($this, 'stock_freeze_section_callback'), 'cart-clean-settings');
        add_settings_field('stock_freeze_quantity', esc_html__('Enable Stock Freeze Quantity', 'CCforWoocommerce'), array($this, 'stock_freeze_quantity_callback'), 'cart-clean-settings', 'stock_freeze_section');
    }

    // Callback functions for the settings page
    public function cart_clean_section_callback()
    {
        // cart_clean_section_callback: Callback for the Cart Clean Time section.
        echo esc_html__('Set the Cart Clean Time (in minutes):', 'CCforWoocommerce');
    }

    public function cart_clean_cart_clean_time_callback()
    {
        // cart_clean_cart_clean_time_callback: Callback for the Cart Clean Time setting field.
        $cartCleanTime = get_option('cart_clean_time');
        echo '<input type="number" name="cart_clean_time" value="' . esc_attr($cartCleanTime) . '" />';
    }

    public function stock_freeze_section_callback()
    {
        // stock_freeze_section_callback: Callback for the Stock Freeze Quantity section.
        echo esc_html__('Stock Freeze Quantity Information: With this option, you can reduce stock quantity by just adding to cart. Please note that if a product is automatically removed from the cart, stock quantity will be restored to the product.', 'CCforWoocommerce');
    }

    public function stock_freeze_quantity_callback()
    {
        // stock_freeze_quantity_callback: Callback for the Stock Freeze Quantity checkbox field.
        $stockFreezeQuantity = get_option('stock_freeze_quantity');
        $checked = checked(1, $stockFreezeQuantity, false); // Check the checkbox if the option is 1 (enabled).
        echo '<label for="stock_freeze_quantity"><input type="checkbox" id="stock_freeze_quantity" name="stock_freeze_quantity" value="1" ' . $checked . ' /> Enable Stock Freeze Quantity</label>';
    }
}

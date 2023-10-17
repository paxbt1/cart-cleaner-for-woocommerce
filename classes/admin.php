<?php
class CartCleanAdmin
{
    public function __construct()
    {
        // Constructor: Initializes admin menu and settings page actions.
        add_action('admin_menu', array($this, 'cartCleanMenu'));
        add_action('admin_init', array($this, 'cartCleanSettingsInit'));
    }

    // Register the admin menu item
    public function cartCleanMenu()
    {
        // cartCleanMenu: Registers the admin menu item.
        add_menu_page(esc_html('Cart Clean Settings', 'CCforWoocommerce'), esc_html('Cart Clean', 'CCforWoocommerce'), 'manage_options', 'cart-clean-settings', array($this, 'cartCleanSettingsPage'));
    }

    // Create the plugin settings page
    public function cartCleanSettingsPage()
    {
        // cartCleanSettingsPage: Renders the plugin settings page.
        ?>
        <div class="wrap">
            <h2><?= esc_html('Cart Clean Settings', 'CCforWoocommerce');?></h2>
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
    public function cartCleanSettingsInit()
    {
        // cartCleanSettingsInit: Initializes and registers plugin settings.

        // Register the cart clean time setting
        register_setting('cart_clean_settings', 'cart_clean_time', 'intval');
        add_settings_section('cart_clean_section', esc_html('Cart Clean Time', 'CCforWoocommerce'), array($this, 'cartCleanSectionCallback'), 'cart-clean-settings');
        add_settings_field('cart_clean_time', esc_html('Minutes', 'CCforWoocommerce'), array($this, 'cartCleanCartCleanTimeCallback'), 'cart-clean-settings', 'cart_clean_section');

        // Register the stock freeze quantity setting as a checkbox
        register_setting('cart_clean_settings', 'stock_freeze_quantity', 'intval');
        add_settings_section('stock_freeze_section', esc_html('Stock Freeze Quantity', 'CCforWoocommerce'), array($this, 'stockFreezeSectionCallback'), 'cart-clean-settings');
        add_settings_field('stock_freeze_quantity', esc_html('Enable Stock Freeze Quantity', 'CCforWoocommerce'), array($this, 'stockFreezeQuantityCallback'), 'cart-clean-settings', 'stock_freeze_section');
    }

    // Callback functions for the settings page
    public function cartCleanSectionCallback()
    {
        // cartCleanSectionCallback: Callback for the Cart Clean Time section.
        echo esc_html('Set the Cart Clean Time (in minutes):', 'CCforWoocommerce');
    }

    public function cartCleanCartCleanTimeCallback()
    {
        // cartCleanCartCleanTimeCallback: Callback for the Cart Clean Time setting field.
        $cartCleanTime = get_option('cart_clean_time');
        echo '<input type="number" name="cart_clean_time" value="' . esc_attr($cartCleanTime) . '" />';
    }

    public function stockFreezeSectionCallback()
    {
        // stockFreezeSectionCallback: Callback for the Stock Freeze Quantity section.
        echo esc_html('Stock Freeze Quantity Information: With this option, you can reduce stock quantity by just adding to cart. Please note that if a product is automatically removed from the cart, stock quantity will be restored to the product.', 'CCforWoocommerce');
    }

    public function stockFreezeQuantityCallback()
    {
        // stockFreezeQuantityCallback: Callback for the Stock Freeze Quantity checkbox field.
        $stockFreezeQuantity = get_option('stock_freeze_quantity');
        $checked = checked(1, $stockFreezeQuantity, false); // Check the checkbox if the option is 1 (enabled).
        echo '<label for="stock_freeze_quantity"><input type="checkbox" id="stock_freeze_quantity" name="stock_freeze_quantity" value="1" ' . $checked . ' /> Enable Stock Freeze Quantity</label>';
    }
}

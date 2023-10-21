<?php
/**
 * Plugin Name: Auto Cart Cleaner for WooCommerce
 * Description: Automatic cleaning cart and real-time stock management on add-to-cart and remove-from-cart actions.
 * Version: 1.0
 * Author: Saeed Ghourbanian
 * Author URI: https://www.linkedin.com/in/saeed-ghourbanian/
 * Text Domain: auto-cart-cleaner-for-wooCommerce
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 5.9
 * WC requires at least: 4.0
 * WC tested up to: 6.0
 */

if (!defined("ABSPATH")) {
    exit;
}

// Include class files
require_once "classes/admin.php";
require_once "classes/cleanner.php";

// Initialize classes
new CartCleanAdmin();
new CartManagement();

// Enqueue custom script for cart page
function enqueue_custom_cart_script()
{
    wp_enqueue_script('custom-cart-script', plugin_dir_url(__FILE__) . 'assets/js/script.js', null, '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_cart_script');

// Activation check
function acc_for_woocommerce_plugin_activation_check()
{
    if (!class_exists('WooCommerce')) {
        // WooCommerce is not installed or not active
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__("Auto Cart Cleaner for WooCommerce requires WooCommerce to be installed and active. Please install and activate WooCommerce before using this plugin.", 'auto-cart-cleaner-for-wooCommerce'));
    }
}
register_activation_hook(__FILE__, 'acc_for_woocommerce_plugin_activation_check');



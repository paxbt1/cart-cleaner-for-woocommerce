<?php
/**
 * Plugin Name: Stock Freeze by Add to Cart
 * Description: A brief description of Stock Freeze.
 * Version: 1.0
 * Author: Your Name
 */

if(!defined("ABSPATH")) {
    exit;
}

require_once "classes/admin.php";

new cart_freeze_admin();

add_action('woocommerce_before_calculate_totals', 'wc_minimum_hours_to_remove_product', 10, 1);
function wc_minimum_hours_to_remove_product($cart)
{

    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }

    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return;
    }

    foreach($cart->get_cart() as $cart_item_key => $cart_item) {


        $remove_cart_item = false;
        if(isset($cart_item['product_added_to_cart_date']) && ! empty($cart_item['product_added_to_cart_date'])) {
            $hourdiff = round((strtotime('now') - $cart_item['product_added_to_cart_date']) / 60, 1);
            if($hourdiff >= get_option('stock_freeze_time')) {
                $remove_cart_item = true;

                // Check if the product is a variation
                if (!empty($cart_item['variation_id'])) {
                    // Get the variation ID
                    $variation_id = $cart_item['variation_id'];

                    // Check if the product is a variation of a variable product
                    if (get_post_type($variation_id) === 'product_variation') {
                        // Get the parent product ID
                        $parent_id = wp_get_post_parent_id($variation_id);

                        // Increase the stock quantity of the variation by 1
                        $variation_product = wc_get_product($variation_id);
                        $stock_quantity = $variation_product->get_stock_quantity();
                        $new_stock_quantity = $stock_quantity + $cart_item['quantity'];
                        $variation_product->set_stock_quantity($new_stock_quantity);
                        $variation_product->save();
                    }
                } else {
                    // If it's not a variation (e.g., a simple product), increase its stock quantity by 1
                    $product_id = $cart_item['product_id'];
                    $product = wc_get_product($product_id);
                    $stock_quantity = $product->get_stock_quantity();
                    $new_stock_quantity = $stock_quantity + $cart_item['quantity'];
                    $product->set_stock_quantity($new_stock_quantity);
                    $product->save();
                }

            }

            if($remove_cart_item) {
                $cart->remove_cart_item($cart_item_key);
            }
        }
    }
}


add_filter('woocommerce_add_cart_item_data', 'add_custom_field_date_and_reduce_stock', 20, 3);

function add_custom_field_date_and_reduce_stock($cart_item_data, $product_id, $variation_id)
{

    // Check if the product is a variable product
    $product = wc_get_product($product_id);
    $is_variable = $product->is_type('variable');

    if ($is_variable) {

        // Increase the stock quantity of the selected variation by 1
        $variation_product = wc_get_product($variation_id);
        $stock_quantity = $variation_product->get_stock_quantity();
        $new_stock_quantity = $stock_quantity - $_POST['quantity'];
        $variation_product->set_stock_quantity($new_stock_quantity);
        $variation_product->save();
    } else {
        // If it's a simple product, increase its stock quantity by 1
        $stock_quantity = $product->get_stock_quantity();
        $new_stock_quantity = $stock_quantity - $_POST['quantity'];
        $product->set_stock_quantity($new_stock_quantity);
        $product->save();
    }

    // Add the date to cart item data
    $cart_item_data['product_added_to_cart_date'] = strtotime('now');
    $cart_item_data['unique_key'] = md5(microtime() . rand());

    return $cart_item_data;
}



function add_countdown_timer_after_cart_item_name($item_name, $cart_item, $cart_item_key)
{

    $hourdiff = (get_option('stock_freeze_time') * 60) - (strtotime('now') - $cart_item['product_added_to_cart_date']);
    // Append a countdown timer after the cart item name
    $item_name = '<span><span class="countdown-timer" data-timer="'.$hourdiff.'"></span> - to remove: </span>'.$item_name;

    return $item_name;
}
add_filter('woocommerce_cart_item_name', 'add_countdown_timer_after_cart_item_name', 10, 3);

add_action('woocommerce_before_cart', 'timer_script');
function timer_script()
{
    ?>
    <!-- Add this script in your HTML file -->
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var countdownTimers = document.querySelectorAll('.countdown-timer');

        countdownTimers.forEach(function(timerElement) {
            var timerValue = parseInt(timerElement.getAttribute('data-timer'));
            var timerInterval = setInterval(function() {
                if (timerValue <= 0) {
                    clearInterval(timerInterval);
                    location.reload();

                } else {
                    var minutes = Math.floor(timerValue / 60);
                    var seconds = timerValue % 60;
                    timerElement.innerHTML = pad(minutes) + ':' + pad(seconds);
                    timerValue--;

                    if (timerValue <= 20) {
                        timerElement.style.color = 'red';
                    }
                }
            }, 1000);
        });

        function pad(value) {
            return (value < 10) ? '0' + value : value;
        }
    });
</script>

    <?php
}

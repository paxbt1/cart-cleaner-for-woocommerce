<?php

class CartManagement
{
    private $cart_clean_timer = null;
    private $stock_freeze_quantity = null;

    public function __construct()
    {
        // Constructor: Initializes hooks and settings
        // and sets the cart clean timer value.
        add_action('woocommerce_before_calculate_totals', array($this, 'removeExpiredProducts'), 10, 1);
        add_filter('woocommerce_add_cart_item_data', array($this, 'addCustomFieldAndReduceStock'), 20, 3);
        add_filter('woocommerce_cart_item_name', array($this, 'addCountdownTimerAfterCartItemName'), 10, 3);
        add_action('woocommerce_remove_cart_item', array($this,'increaseStockQuantityOnRemove'), 10, 2);
        $this->cart_clean_timer = get_option('cart_clean_time', 5);
        $this->stock_freeze_quantity = get_option('cart_clean_time', 0);
    }

    public function increaseStockQuantityOnRemove($cart_item_key, $cart)
    {

        // Check if the "stock_freeze_quantity" option is true
        if ($this->stock_freeze_quantity == 1) {

            // Get the cart item data
            $cart_item = $cart->cart_contents[$cart_item_key];

            // Check if it's a variable product
            if (!empty($cart_item['variation_id'])) {
                $variation_id = $cart_item['variation_id'];
                $variation_product = wc_get_product($variation_id);
                $new_stock_quantity = $variation_product->get_stock_quantity() + $cart_item['quantity'];
                update_stock_quantity($variation_product, $new_stock_quantity);
            } else {
                $product_id = $cart_item['product_id'];
                $product = wc_get_product($product_id);
                $new_stock_quantity = $product->get_stock_quantity() + $cart_item['quantity'];
                update_stock_quantity($product, $new_stock_quantity);
            }
        }
    }

    public function update_stock_quantity($product, $new_stock_quantity)
    {
        // Update the stock quantity for the product
        $product->set_stock_quantity($new_stock_quantity);
        $product->save();
    }

    public function removeExpiredProducts($cart)
    {
        // removeExpiredProducts: Removes expired products from the cart.
        // $cart: The WooCommerce cart object

        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $remove_cart_item = false;

            if (isset($cart_item['product_added_to_cart_date']) && !empty($cart_item['product_added_to_cart_date'])) {
                $hourdiff = round((strtotime('now') - $cart_item['product_added_to_cart_date']) / 60, 1);

                if ($hourdiff >= $this->cart_clean_timer) {
                    $remove_cart_item = true;
                    if($this->stock_freeze_quantity) {
                        $this->increaseStockQuantity($cart_item);
                    }
                }

                if ($remove_cart_item) {
                    $cart->remove_cart_item($cart_item_key);
                }
            }
        }
    }

    public function addCustomFieldAndReduceStock($cart_item_data, $product_id, $variation_id)
    {
        // addCustomFieldAndReduceStock: Adds custom field data to cart items and reduces stock if necessary.
        // $cart_item_data: Data for the cart item
        // $product_id: The product ID
        // $variation_id: The variation ID, if applicable

        if($this->stock_freeze_quantity) {
            $product = wc_get_product($product_id);
            $is_variable = $product->is_type('variable');

            if ($is_variable) {
                $variation_product = wc_get_product($variation_id);
                $new_stock_quantity = $variation_product->get_stock_quantity() - $_POST['quantity'];
                $this->updateStockQuantity($variation_product, $new_stock_quantity);
            } else {
                $new_stock_quantity = $product->get_stock_quantity() - $_POST['quantity'];
                $this->updateStockQuantity($product, $new_stock_quantity);
            }
        }

        $cart_item_data['product_added_to_cart_date'] = strtotime('now');
        $cart_item_data['unique_key'] = md5(microtime() . rand());

        return $cart_item_data;
    }

    public function addCountdownTimerAfterCartItemName($item_name, $cart_item, $cart_item_key)
    {
        // addCountdownTimerAfterCartItemName: Adds a countdown timer after the cart item name.
        // $item_name: The cart item name
        // $cart_item: The cart item data
        // $cart_item_key: The cart item key

        $hourdiff = ($this->cart_clean_timer * 60) - (strtotime('now') - $cart_item['product_added_to_cart_date']);
        $item_name = sprintf('<span><span class="countdown-timer" data-timer="%d"></span> - %s</span>', $hourdiff, $item_name);
        return $item_name;
    }

    private function increaseStockQuantity($cart_item)
    {
        // increaseStockQuantity: Increases stock quantity for cart items.
        // $cart_item: The cart item data

        if (!empty($cart_item['variation_id'])) {
            $variation_id = $cart_item['variation_id'];
            $parent_id = wp_get_post_parent_id($variation_id);
            $variation_product = wc_get_product($variation_id);
            $new_stock_quantity = $variation_product->get_stock_quantity() + $cart_item['quantity'];
            $this->updateStockQuantity($variation_product, $new_stock_quantity);
        } else {
            $product_id = $cart_item['product_id'];
            $product = wc_get_product($product_id);
            $new_stock_quantity = $product->get_stock_quantity() + $cart_item['quantity'];
            $this->updateStockQuantity($product, $new_stock_quantity);
        }
    }

    private function updateStockQuantity($product, $new_stock_quantity)
    {
        // updateStockQuantity: Updates the stock quantity for a product.
        // $product: The product object
        // $new_stock_quantity: The new stock quantity to set

        $product->set_stock_quantity($new_stock_quantity);
        $product->save();
    }
}

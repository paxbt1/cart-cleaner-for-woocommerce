<?php


class CartManagement
{
    private $cart_clean_timer = null;
    private $stock_quantity_sync = null;

    public function __construct()
    {
        // Constructor: Initializes hooks and settings
        // and sets the cart clean timer value.
        add_action('woocommerce_before_calculate_totals', array($this, 'remove_expired_products'), 1, 1);
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_custom_field_and_reduce_stock'), 20, 3);
        add_filter('woocommerce_cart_item_name', array($this, 'add_countdown_timer_after_cart_item_name'), 10, 3);
        add_action('woocommerce_remove_cart_item', array($this,'increase_stock_quantity_on_remove'), 10, 2);
        $this->cart_clean_timer = get_option('cart_clean_time', 5);
        $this->stock_quantity_sync = get_option('stock_quantity_sync', 0);
    }

    public function increase_stock_quantity_on_remove($cart_item_key, $cart)
    {

        if(did_action('woocommerce_before_calculate_totals') >= 1) {
            return;
        }

        // Check if the "stock_quantity_sync" option is true
        if ($this->stock_quantity_sync == 1) {
            // Get the cart item data
            $cart_item = $cart->cart_contents[$cart_item_key];
            // Check if it's a variable product
            if (!empty($cart_item['variation_id'])) {
                $variation_id = $cart_item['variation_id'];
                $variation_product = wc_get_product($variation_id);
                $new_stock_quantity = $variation_product->get_stock_quantity() + $cart_item['quantity'];
                $this->update_stock_quantity($variation_product, $new_stock_quantity);
            } else {
                $product_id = $cart_item['product_id'];
                $product = wc_get_product($product_id);
                $new_stock_quantity = $product->get_stock_quantity() + $cart_item['quantity'];
                $this->update_stock_quantity($product, $new_stock_quantity);
            }
        }
    }

    public function update_stock_quantity($product, $new_stock_quantity)
    {
        // Update the stock quantity for the product
        $product->set_stock_quantity($new_stock_quantity);
        $product->save();
    }

    public function remove_expired_products($cart)
    {
        // remove_expired_products: Removes expired products from the cart.


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
                    if($this->stock_quantity_sync) {
                        $this->increase_stock_quantity($cart_item);
                    }
                }

                if ($remove_cart_item) {
                    $cart->remove_cart_item($cart_item_key);
                }
            }
        }
    }

    public function add_custom_field_and_reduce_stock($cart_item_data, $product_id, $variation_id)
    {
        if($this->stock_quantity_sync) {
            $product = wc_get_product($product_id);
            $is_variable = $product->is_type('variable');

            if ($is_variable) {
                $variation_product = wc_get_product($variation_id);
                $new_stock_quantity = $variation_product->get_stock_quantity() - $_POST['quantity'];
                $this->update_stock_quantity($variation_product, $new_stock_quantity);
            } else {
                $new_stock_quantity = $product->get_stock_quantity() - $_POST['quantity'];
                $this->update_stock_quantity($product, $new_stock_quantity);
            }
        }

        $cart_item_data['product_added_to_cart_date'] = strtotime('now');
        $cart_item_data['unique_key'] = md5(microtime() . rand());

        return $cart_item_data;
    }

    public function add_countdown_timer_after_cart_item_name($item_name, $cart_item, $cart_item_key)
    {
        $hourdiff = ($this->cart_clean_timer * 60) - (strtotime('now') - $cart_item['product_added_to_cart_date']);
        $item_name = sprintf('<span><span class="countdown-timer" data-timer="%d"></span> - %s</span>', $hourdiff, $item_name);
        return $item_name;
    }

    private function increase_stock_quantity($cart_item)
    {
        if (!empty($cart_item['variation_id'])) {
            $variation_id = $cart_item['variation_id'];
            $variation_product = wc_get_product($variation_id);
            $new_stock_quantity = $variation_product->get_stock_quantity() + $cart_item['quantity'];
            $this->update_stock_quantity($variation_product, $new_stock_quantity);
        } else {
            $product_id = $cart_item['product_id'];
            $product = wc_get_product($product_id);
            $new_stock_quantity = $product->get_stock_quantity() + $cart_item['quantity'];
            $this->update_stock_quantity($product, $new_stock_quantity);
        }
    }
}

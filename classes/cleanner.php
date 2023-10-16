<?php
class CartManagement {

    public function __construct() {
        add_action('woocommerce_before_calculate_totals', array($this, 'removeExpiredProducts'), 10, 1);
        add_filter('woocommerce_add_cart_item_data', array($this, 'addCustomFieldAndReduceStock'), 20, 3);
        add_filter('woocommerce_cart_item_name', array($this, 'addCountdownTimerAfterCartItemName'), 10, 3);
    }

    public function removeExpiredProducts($cart) {
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

                $cartCleanTime = get_option('cart_clean_time') ?: 5;

                if ($hourdiff >= $cartCleanTime) {
                    $remove_cart_item = true;
                    if($this->isStockFreezeEnabled()){
                        $this->increaseStockQuantity($cart_item);
                    }
                }

                if ($remove_cart_item) {
                    $cart->remove_cart_item($cart_item_key);
                }
            }
        }
    }

    private function isStockFreezeEnabled() {
        $stockFreezeQuantity = get_option('stock_freeze_quantity'); // Get the Stock Freeze Quantity option.
        return $stockFreezeQuantity == 1;
    }

    public function addCustomFieldAndReduceStock($cart_item_data, $product_id, $variation_id) {

        if($this->isStockFreezeEnabled()){
            
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

    public function addCountdownTimerAfterCartItemName($item_name, $cart_item, $cart_item_key) {
        $hourdiff = (get_option('cart_clean_time') * 60) - (strtotime('now') - $cart_item['product_added_to_cart_date']);
        $item_name = sprintf('<span><span class="countdown-timer" data-timer="%d"></span> - %s</span>', $hourdiff, $item_name);
        return $item_name;
    }

    private function increaseStockQuantity($cart_item) {
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

    private function updateStockQuantity($product, $new_stock_quantity) {
        $product->set_stock_quantity($new_stock_quantity);
        $product->save();
    }
}

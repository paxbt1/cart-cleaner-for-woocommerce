<?php
/**
 * Plugin Name: Auto Cart Cleanner for Woocommerce
 * Description: Auto Cart Cleanner for Woocommerce
 * Version: 1.0
 * Author: Saeed Ghourbanian
 * Text Domain:CCforWoocommerce
 */

if(!defined("ABSPATH")) {
    exit;
}

require_once "classes/admin.php";
require_once "classes/cleanner.php";

new CartCleanAdmin();
new CartManagement();


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
                    // location.reload();

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

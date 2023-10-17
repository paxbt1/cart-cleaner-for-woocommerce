document.addEventListener('DOMContentLoaded', function () {
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

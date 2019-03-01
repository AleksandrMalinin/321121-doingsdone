'use strict';

var $checkbox = document.getElementsByClassName('show_completed');

if ($checkbox.length) {
  $checkbox[0].addEventListener('change', function (event) {
    var is_checked = +event.target.checked;

    window.location = '/index.php?show_completed=' + is_checked;
  });
}

flatpickr('#date', {
  enableTime: false,
  dateFormat: "d.m.Y",
  time_24hr: true,
  locale: "ru"
});

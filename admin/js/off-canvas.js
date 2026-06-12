(function ($) {
  'use strict';
  $(function () {
    if (!$('.sidebar-overlay').length) {
      $('body').append('<div class="sidebar-overlay" aria-hidden="true"></div>');
    }

    function closeSidebar() {
      $('.sidebar-offcanvas').removeClass('active');
      $('body').removeClass('sidebar-open');
    }

    function openSidebar() {
      $('.sidebar-offcanvas').addClass('active');
      $('body').addClass('sidebar-open');
    }

    $('[data-toggle="offcanvas"]').on('click', function (e) {
      e.preventDefault();
      if ($('.sidebar-offcanvas').hasClass('active')) {
        closeSidebar();
      } else {
        openSidebar();
      }
    });

    $('.sidebar-overlay').on('click touchstart', function (e) {
      e.preventDefault();
      closeSidebar();
    });

    $(window).on('resize', function () {
      if (window.innerWidth > 991) {
        closeSidebar();
      }
    });

    $(document).on('keyup', function (e) {
      if (e.key === 'Escape') {
        closeSidebar();
      }
    });

    function resetMobileLayout() {
      closeSidebar();
      window.scrollTo(0, 0);
    }

    window.addEventListener('orientationchange', resetMobileLayout);
    window.addEventListener('resize', function () {
      if (window.innerWidth > 991) {
        resetMobileLayout();
      }
    });
  });
})(jQuery);


$(document).ready(function() {
    var currentPath = window.location.pathname;
    $(".navbar-nav .nav-link").removeClass("active");
    $(".navbar-nav .nav-link").each(function() {
      if ($(this).attr("href") === currentPath) {
        $(this).parent().addClass("active");
      }
    });

    $('a.tag_art').each(function() {
      var href = $(this).attr('href');
      if (href === currentPath) {
          $(this).addClass('active');
      }
    }); 

});










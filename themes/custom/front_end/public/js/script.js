
  $(document).ready(function() {
      var currentPath = window.location.pathname;
      $(".navbar-nav .nav-link").removeClass("active");
      $(".navbar-nav .nav-link").each(function() {
        if ($(this).attr("href") === currentPath) {
          $(this).parent().addClass("active");
        }else if(currentPath =='/'){
          $('.navbar-nav .nav-link.home').addClass('active');
        }
      });

      var tag = $('a.tag-detail').text();
      var hrefTag = $('a.tag-detail').attr('href');
      $('a.tag_art').each(function() {
        var href = $(this).attr('href');
        if (href === currentPath) {
            $(this).addClass('active');
        }else if(href==hrefTag){
            $(this).addClass('active');
        }
      }); 
  });










(function ($, Drupal, drupalSettings) {
        $(document).ready(function() {
            var langCode = drupalSettings.langCode;
            var page = 0; 
            var totalPages = 0; 
            function loadContent(page) {
                $.ajax({
                    url: '/home-data',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                    page: page,
                    langcode:langCode
                    },
                    success: function (response) {
                        var content = response.content;
                        totalPages = response.pages;
                        var html = '';
                        for (var i = 0; i < content.length; i++) {
                            var maxLength = 250; 
                            var truncatedBody = content[i].body.length > maxLength ? content[i].body.substring(0, maxLength) + '...' : content[i].body;
                            html += '<div class="blog-post">';
                            if (content[i].image_url) {
                                html +='<div class="blog-thumb">';
                                html += '<a href="/'+langCode+'/detail/' + content[i].nid + '"><img src="' + content[i].image_url + '" alt="Image"></a>';
                                html += '</div>';
                            }
                            html +=    '<div class="down-content">';
                            html += '<h4><a href="/'+langCode+'/detail/' + content[i].nid + '">' + content[i].title + '</a></h4>';
                            html +=        '<div class="body_content">';
                            html +=            '<p>' + truncatedBody + '</p>';
                            html +=        '</div>'
                            html +=        '<div class="post-options">';
                            html +=            '<div class="row">';
                            html +=           '<div class="col-6">';
                            html +=                '<ul class="post-tags">';
                            html +=                '<li><i class="fa fa-tags"></i></li>';
                            html +=                '<li><a href="#">Beauty</a>,</li>';
                            html +=                '<li><a href="#">Nature</a></li>';
                            html +=                '</ul>';
                            html +=            '</div>';
                            html +=            '<div class="col-6">';
                            html +=                '<ul class="post-share">';
                            html +=                '<li><i class="fa fa-share-alt"></i></li>';
                            html +=                '<li><a href="#">Facebook</a>,</li>';
                            html +=                '<li><a href="#"> Twitter</a></li>';
                            html +=                '</ul>';
                            html +=            '</div>';
                            html +=            '</div>';
                            html +=        '</div>';
                            html +=    '</div>';
                            html += '</div>';
                        }
                    $('.pagination-content').html(html);
                    updatePaginationButtons(page);
                    renderPageNumbers();
                    },
                    error: function (xhr, status, error) {
                    console.log(error);
                    }
                });
            }
        
            function updatePaginationButtons(page) {
                if (page > 0) {
                    $('.pagination-previous').prop('disabled', false);
                } else {
                    $('.pagination-previous').prop('disabled', true);
                }
            
                if (page < totalPages - 1) {
                    $('.pagination-next').prop('disabled', false);
                } else {
                        $('.pagination-next').prop('disabled', true);
                }
            }
        
            function renderPageNumbers() {
                var paginationContainer = $('.pagination-numbers');
                paginationContainer.empty();
              
                var maxDisplayedPages = 3;
                var halfDisplayedPages = Math.floor(maxDisplayedPages / 2);
              
                var startPage = Math.max(0, Math.min(page - halfDisplayedPages, totalPages - maxDisplayedPages));
                var endPage = Math.min(startPage + maxDisplayedPages - 1, totalPages - 1);
              
                if (startPage > 0) {
                    var firstPageLink = $('<a>', {
                        href: '/home-data?page=1',
                        text: '<<',
                        click: function (event) {
                        event.preventDefault();
                        page = 0;
                        loadContent(page);
                        }
                    });
                    paginationContainer.append(firstPageLink);
                }

                for (var i = startPage; i <= endPage; i++) {
                    var pageNumber = i + 1;
                    var isActive = (i === page) ? 'active' : '';
                
                    var pageLink = $('<a>', {
                        href: '/home-data?page=' + pageNumber,
                        class: isActive,
                        text: pageNumber,
                        click: function (event) {
                        event.preventDefault();
                        page = parseInt($(this).text()) - 1;
                        loadContent(page);
                        }
                    });
                    paginationContainer.append(pageLink);
                }
              
                if (endPage < totalPages - 1) {
                    var lastPageLink = $('<a>', {
                        href: '/home-data?page=' + totalPages,
                        text: '>>',
                        click: function (event) {
                        event.preventDefault();
                        page = totalPages - 1;
                        loadContent(page);
                        }
                    });
                    paginationContainer.append(lastPageLink);
                }
            }  
            $('.pagination-previous').click(function () {
                if (page > 0) {
                    page--;
                    loadContent(page);
                }
            });
        
            $('.pagination-next').click(function () {
                if (page < totalPages - 1) {
                    page++;
                    loadContent(page);
                }
            });
        
            loadContent(page);
        });
})(jQuery, Drupal, drupalSettings);

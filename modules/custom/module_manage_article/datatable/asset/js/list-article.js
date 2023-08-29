
$(document).ready(function() {
    var langCode = $("#lang-code-list").attr("data-lang-code");
    var page = 0; 
    var totalPages = 0;
    function loadContent(page) {
        $.ajax({
            url: '/list-art',
            type: 'GET',
            dataType: 'json',
            data: {
            page: page,
            langcode:langCode,
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
                        html += '<a class="image_list" href="/'+langCode+'/detail/' + content[i].nid + '"><img style="width:100%" src="' + content[i].image_url + '" alt="Image"></a>';
                        html += '</div>';
                    }else{
                        html +='<div class="blog-thumb">';
                        html += '<a href="/'+langCode+'/detail/' + content[i].nid + '"><img src="' + content[i].image_url + '" alt="Image"></a>';
                        html += '</div>';
                    }
                    html +=    '<div class="down-content">';
                    html += '<h4><a href="/'+langCode+'/detail/' + content[i].nid + '">' + content[i].title + '</a></h4>';
                    html +=       '<ul class="post-info">'
                    html +=             '<li><a href="#">'+content[i].author+'</a></li>';
                    html +=             '<li><a href="#">'+formatDate(content[i].changed)+'</a></li>';
                    // html +=           '<li><a href="#">12 Comments</a></li>';
                    html +=        '</ul>';
                    html +=        '<div class="body_content">';
                    html +=            '<p>' + truncatedBody + '</p>';
                    html +=        '</div>'
                    html +=        '<div class="post-options">';
                    html +=            '<div class="row">';
                    html +=                 '<div class="col-6">';
                    html +=                        '<ul class="post-tags">';
                    html +=                             '<li><a href="'+langCode+'/tag-article/'+content[i].tag+'"><i class="fa fa-tags"></i>'+content[i].tag+'</a></li>';
                    html +=                        '</ul>';
                    html +=                 '</div>';
                    html +=            '</div>';
                    html +=        '</div>';
                    html +=    '</div>';
                    html += '</div>';
                }
            $('#pagination-content').html(html);
            // Cập nhật nút phân trang
            updatePaginationButtons(page);
            //  số trang
            renderPageNumbers();
            },
            error: function (xhr, status, error) {
            console.log(error);
            }
        });
    }

    function updatePaginationButtons(page) {
        // Cập nhật trạng thái của nút "Trang trước"
        if (page > 0) {
            $('#pagination-previous').prop('disabled', false);
        } else {
            $('#pagination-previous').prop('disabled', true);
        }
    
        // Cập nhật trạng thái của nút "Trang kế tiếp"
        if (page < totalPages - 1) {
            $('#pagination-next').prop('disabled', false);
        } else {
                $('#pagination-next').prop('disabled', true);
        }
    }

    function renderPageNumbers() {
        var paginationContainer = $('#pagination-numbers');
        paginationContainer.empty();
        
        var maxDisplayedPages = 3; // Số trang tối đa được hiển thị
        var halfDisplayedPages = Math.floor(maxDisplayedPages / 2); // Số trang được hiển thị bên trái và bên phải của trang hiện tại
        
        var startPage = Math.max(0, Math.min(page - halfDisplayedPages, totalPages - maxDisplayedPages));
        var endPage = Math.min(startPage + maxDisplayedPages - 1, totalPages - 1);
        
        // Hiển thị nút "Trang đầu"
        if (startPage > 0) {
            var firstPageLink = $('<a>', {
                href: '/list-art?page=1',
                text: '<<',
                click: function (event) {
                event.preventDefault();
                page = 0;
                loadContent(page);
                }
            });
            paginationContainer.append(firstPageLink);
        }
        
        // Hiển thị các số trang
        for (var i = startPage; i <= endPage; i++) {
            var pageNumber = i + 1;
            var isActive = (i === page) ? 'active' : '';
        
            var pageLink = $('<a>', {
                href: '/list-art?page=' + pageNumber,
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
        
        // Hiển thị nút "Trang cuối"
        if (endPage < totalPages - 1) {
            var lastPageLink = $('<a>', {
                href: '/list-art?page=' + totalPages,
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
    // Xử lý sự kiện khi nhấp vào nút "Trang trước"
    $('#pagination-previous').click(function () {
        if (page > 0) {
            page--;
            loadContent(page);
        }
    });

    // Xử lý sự kiện khi nhấp vào nút "Trang kế tiếp"
    $('#pagination-next').click(function () {
        if (page < totalPages - 1) {
            page++;
            loadContent(page);
        }
    });
    
    loadContent(page);
    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);

        var year = date.getFullYear(); 
        var month = date.getMonth() + 1; 
        var day = date.getDate(); 
        var formattedDate = month + '/' + day + '/' + year;
        return formattedDate;
    }

    $("#date_from").datepicker({
        format: "dd/mm/yyyy hh:ii",
        startDate: "00:00",
    
    });
    
    $("#date_to").datepicker({
        format: "dd/mm/yyyy hh:ii",
        startDate: "00:00", 

    });
});



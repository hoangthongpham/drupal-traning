$(document).ready(function() {

    var http = window.location.href;
    if(http =="http://localhost/vi/admin/articles"){
        url_data= '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
    }else{
        url_data='//cdn.datatables.net/plug-ins/1.13.4/i18n/en-GB.json'
    }
    var table =$('#listTable').DataTable({
        processing: true,
        serverSide: true,
        searching:true,
        lengthChange:true,
        ordering:true,
        lengthMenu :[5, 10, 15, 100],
        pageLength :5,
        scrollCollapse : true,
        ajax: {
            url: '/admin/get-list',
            dataType: 'json',
            data: function (data) {
                data.status = $("#status option:selected").val();
                data.langcode = $("#langcode option:selected").val();
            },
        },
        aoColumns: [
            { data: 'serial_no'},
            { data: 'title'},
            { data: 'status' },
            { data: 'langcode' },
            { data: 'changed' },
            { data: Drupal.t('action'), }
        ],
        columnDefs:[
            {
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                targets: 0,
                
            },
            {
                render: function (data, type, row) {
                    return data;
                },
                data: "title",
                targets: 1,
            },
            {
                render: function (data, type, row) {
                    if( data ==1){
                        data = Drupal.t('Active');
                    }else {
                        data = Drupal.t('InActive');
                    }
                    return data;
                },
                data: "status",
                targets: 2,
            },
            {
                render: function (data, type, row) {
            
                    return data;
                },
                data: "langcode",
                targets:3,
            },  
             {
                render: function (data, type, row) {
                    return  formatDate(row.changed)
                },
                data: "created",
                targets: 4,
            },
            {
                render: function (data, type, row) { 
                    var action = '<ul class="icons-list" >' +
                    '<li class="dropdown" >' +
                    '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' +
                    '<i class="icon-menu9"> </i></a>' +
                    '<ul class="dropdown-menu dropdown-menu-right">';
                    action += '<li><a class="delete_item" data-id='+ row.nid +'  href="/admin/article/delete/' + row.nid + '">'+Drupal.t('Delete')+'</a> </li>';
                    action += '<li> <a  href="/admin/article/edit/' + row.nid + '">'+Drupal.t('Edit')+'</a> </li>';
                    action += '<li> <a href="javascript:void(0)" data-toggle="modal" data-target="#edit_modal" class="quick_edit" id="quick_edit"  data-id='+ row.nid +'>'+Drupal.t('Quick edit')+'</a> </li>';
                    action += '<li> <a  href="javascript:void(0)/' + row.nid + '" data-toggle="modal" data-target="#view_modal" class="view_article" id="view_article"  data-id='+ row.nid +'>'+Drupal.t('View')+'</a></li> </ul></li></ul>';
                    return 	action;            
                        var deleteLink = '<a class="delete_item" data-id='+ row.nid +'  href="/admin/article/delete/' + row.nid + '">'+Drupal.t('Delete')+'</a>';
                        var editLink = '<a  href="/admin/article/edit/' + row.nid + '">'+Drupal.t('Edit')+'</a>';
                        var quickEditLink = '<a href="javascript:void(0)" data-toggle="modal" data-target="#edit_modal" class="quick_edit" id="quick_edit"  data-id='+ row.nid +'>'+Drupal.t('Quick edit')+'</a>';
                        var viewLink = '<a  href="javascript:void(0)/' + row.nid + '" data-toggle="modal" data-target="#view_modal" class="view_article" id="view_article"  data-id='+ row.nid +'>'+Drupal.t('View')+'</a>';
                    return deleteLink + ' || ' + editLink +' || '+ quickEditLink +' || '+ viewLink;
                    
                },
                data: Drupal.t('action'),
                targets: 5,
            }
        ],
        language: {
            lengthMenu: ""+Drupal.t('Display')+" "+ "_MENU_" + " "+Drupal.t('entries')+"",
            zeroRecords: ""+Drupal.t('Nothing found - sorry')+"",
            info:  ""+Drupal.t('Showing')+" _START_ "+Drupal.t('to')+" _END_ "+Drupal.t('of')+" _TOTAL_ "+Drupal.t('entries')+"",
            infoEmpty: "No records available",
            infoFiltered: "(filtered from _MAX_ total records)",
            paginate: {
                "first":      ""+Drupal.t('First')+"",
                "last":       ""+Drupal.t('Last')+"",
                "next":       ""+Drupal.t('Next')+"",
                "previous":   ""+Drupal.t('Previous page')+""
            },
        },
        
    });
    $(document).on('change', '#status', function (evt) { 
        table
        .draw();
    });

    $(document).on('change', '#langcode', function (evt) {
        table
        .draw();
    });

    $(document).on('click', '#btn_search', function(evt) {
        var searchValue = $('#search_form').val();
        table
          .search(searchValue)
          .draw();
    });

    $(document).on('click','.delete_item',function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        var confirmation = confirm(Drupal.t('Are you sure you want to delete this article?'));
        if(confirmation){
            $.ajax({
                url: '/admin/article/delete/'+id+'',
                type: 'GET',
                data: {id:id} ,
                beforeSend: function () {     
                },
                success: function (data) {
                    alert(Drupal.t('Delete successfully'));
                    table.draw();
                },
                error: function (data) {
                    
                }
            });
        }
    })

    // view article
    $(document).on('click','.view_article',function(){
        var nid = $(this).data('id');
        var lang = $("#langcode option:selected").val();
        console.log(lang);
        $.ajax({
            type: "GET",
            contentType: "application/json",
            url: "/admin/view/"+nid+"?langcode="+lang+"",
            dataType: 'json',
            success: function (res) {
                $('.title_view').text(res.data.title)
                $('.body_value_view').text(res.data.body_value)
                $('#image').attr("src",res.url)
            },
            error: function () {
                alert('error');
            }
        }); 
    });

    // quick edit
    $(document).on('click','.quick_edit',function(){
        var nid = $(this).data('id');
        var lang = $("#langcode option:selected").val();
        $.ajax({
            type: "GET",
            contentType: "application/json",
            url: "/admin/quick-edit/"+nid+"?langcode="+lang+"",
            data:{id:nid},
            dataType: 'json',
            success: function (res) {
                $("input[name='nid']").val(res.data.nid)
                $('.title').val(res.data.title)
                $('.body_value').val(res.data.body_value)
            },
            error: function () {
                alert('error');
            }
        }); 
    });

    $("#quickForm").validate({
        rules:{
            title:{
                required: true,
                minlength: 2,
            },
            body_value:{
                required: true,
            }
        },
        highlight: function (element) {
            $(element).closest('.form-group').addClass('has-error').css('color', 'red');
        },
        messages:{
            title:{
                required: "This field is required",
                minlength: "Name must be at least 2 characters",
            },
        },
        submitHandler: function(form) {
            var nid = $(this).data('id');
            var lang = $("#langcode option:selected").val();
            $.ajax({
                type: "POST",
                url: "/admin/update-article/"+nid+"?langcode="+lang+"",
                data: $('form.quickForm').serialize(),
                success: function(response) {
                    alert('Updated successfully');
                    table.draw();
                    $('#edit_modal').modal('hide');
                },
                error: function() {
                    alert('Error');
                    $('#edit_modal').modal('hide');
                }
            });
        }
    });
    // $('#quickForm').submit(function(e){
    //     e.preventDefault();
    //     $.ajax({
    //         type: "POST",
    //         url: "/admin/update-article",
    //         data: $('form.quickForm').serialize(),
    //         success: function(response) {
    //             alert('Updated successfully');
    //             table.draw();
    //             $('#edit_modal').modal('hide');
    //         },
    //         error: function() {
    //             alert('Error');
    //             $('#edit_modal').modal('hide');
    //         }
    //     });
    // })

    function formatDate(timestamp) {
        var date = new Date(timestamp * 1000);
      
        var year = date.getFullYear(); 
        var month = date.getMonth() + 1; 
        var day = date.getDate(); 
        var formattedDate = day + '/' + month + '/' + year;
      
        return formattedDate;
    }


    
    
});

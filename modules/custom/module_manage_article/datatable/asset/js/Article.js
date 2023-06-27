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
        ajax: {
            url: '/admin/get-list',
            dataType: 'json',
        },
        aoColumns: [
            { data: 'serial_no'},
            { data: 'title'},
            { data: 'body_value' },
            { data: Drupal.t('action')}
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
                    return data;
                },
                data: "body_value",
                targets: 2,
            },
            {
                render: function (data, type, row) {             
                        var deleteLink = '<a class="delete_item" data-id='+ row.nid +'  href="/admin/article/delete/' + row.nid + '">'+Drupal.t('Delete')+'</a>';
                        var editLink = '<a  href="/admin/article/edit/' + row.nid + '">'+Drupal.t('Edit')+'</a>';
                    return deleteLink + ' || ' + editLink;
                    
                },
                data: Drupal.t('action'),
                targets: 3,
            }
        ],
        language: {
            url: url_data,
        },
        
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
});

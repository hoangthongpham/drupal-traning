$(document).ready(function() {
    var table =$('#listTable').DataTable({
        processing: true,
        serverSide: true,
        searching:true,
        lengthChange:true,
        order:true,
        lengthMenu :[5, 10, 15, 100],
        pageLength : 5,
        ajax: {
            url: '/admin/get-list',
            dataType: 'json',
            // dataSrc: function ( json ) {
            //     return json.data
            // }
        },
        aoColumns: [
            { data: 'serial_no'},
            { data: 'title'},
            { data: 'body_value' },
            { data: 'action'}
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
                    
                    return row.delete +' || '+ row.edit;
                },
                data: "action",
                targets: 3,
            }
        ]
    });

    $(document).on('click', '#btn_search', function(evt) {
        var searchValue = $('#search_form').val();
        console.log(searchValue);
        table
          .columns(0).search(searchValue)
          .draw();
    });

    // $('#search_form').on( 'keyup', function () {
    //     console.log(this.value);
    //     table.search( this.value ).draw();
    // } );

    $(document).on('click','.delete_item',function(e){
        e.preventDefault();
        var id = $(this).attr('data-id');
        var confirmation = confirm('Bạn có chắc chắn muốn xóa loại này?');
        if(confirmation){
            $.ajax({
                url: '/admin/article/delete/'+id+'',
                type: 'GET',
                data: {id:id} ,
                beforeSend: function () {     
                },
                success: function (data) {
                    alert('Xóa Thành Công');
                    table.draw();
                },
                error: function (data) {
                    
                }
            });
        }
    })   
});

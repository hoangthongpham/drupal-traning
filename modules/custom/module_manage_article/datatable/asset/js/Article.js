
    $(document).ready(function() {
        var langCode = $("#lang-code-table").attr("data-lang-code");
        if(langCode=='vi'){
            url_data= '//cdn.datatables.net/plug-ins/1.13.4/i18n/vi.json'
        }else{
            url_data='//cdn.datatables.net/plug-ins/1.13.4/i18n/en-GB.json'
        }
        var table =$('#listTable').DataTable({
            processing: true,
            serverSide: true,
            searching:true,
            lengthChange:true,
            order: [[4, 'desc']],
            lengthMenu :[5, 10, 15, 100],
            pageLength :5,
            scrollCollapse : true,
            ajax: {
                url: '/admin/get-list',
                dataType: 'json',
                data: function (data) {
                    var dataFrom = $('#date_from').val();
                    var dataTo = $('#date_to').val();
                    data.status = $("#status option:selected").val();
                    data.langcode = $("#langcode option:selected").val();
                    data.changed =[dataFrom,dataTo]  ;
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
                    orderable: false
                    
                },
                {
                    render: function (data, type, row) {
                        return data;
                    },
                    data: "title",
                    targets: 1,
                    orderable: false
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
                    orderable: false
                },
                {
                    render: function (data, type, row) {
                
                        return data;
                    },
                    data: "langcode",
                    targets:3,
                    orderable: false
                },  
                {
                    render: function (data, type, row) {
                        return  formatDate(row.changed)
                    },
                    data: "changed",
                    targets: 4,
                    orderable: true
                },
                {
                    render: function (data, type, row) { 
                        var lang = $("#langcode option:selected").val();
                        var action = '<ul class="icons-list" >' +
                        '<li class="dropdown" >' +
                        '<a href="#" class="dropdown-toggle" data-toggle="dropdown">' +
                        '<i class="icon-menu9"> </i></a>' +
                        '<ul class="dropdown-menu dropdown-menu-right">';
                        action += '<li><a class="delete_item" data-id='+ row.nid +'  href="/admin/article/delete/' + row.nid + '">'+Drupal.t('Delete')+'</a> </li>';
                        action += '<li> <a  href="/admin/article/edit/' + row.nid + '?langcode='+lang+'">'+Drupal.t('Edit')+'</a> </li>';
                        action += '<li> <a href="javascript:void(0)" data-toggle="modal" data-target="#edit_modal" class="quick_edit" id="quick_edit"  data-id='+ row.nid +'>'+Drupal.t('Quick edit')+'</a> </li>';
                        action += '<li> <a  href="javascript:void(0)/' + row.nid + '" data-toggle="modal" data-target="#view_modal" class="view_article" id="view_article"  data-id='+ row.nid +'>'+Drupal.t('View')+'</a></li> </ul></li></ul>';
                        return 	action;              
                    },
                    data: Drupal.t('action'),
                    targets: 5,
                    orderable: false
                }
            ],
            language: {
                url:url_data
                // lengthMenu: ""+Drupal.t('Display')+" "+ "_MENU_" + " "+Drupal.t('entries')+"",
                // zeroRecords: ""+Drupal.t('Nothing found - sorry')+"",
                // info:  ""+Drupal.t('Showing')+" _START_ "+Drupal.t('to')+" _END_ "+Drupal.t('of')+" _TOTAL_ "+Drupal.t('entries')+"",
                // infoEmpty: "No records available",
                // infoFiltered: "(filtered from _MAX_ total records)",
                // paginate: {
                //     "first":      ""+Drupal.t('First')+"",
                //     "last":       ""+Drupal.t('Last')+"",
                //     "next":       ""+Drupal.t('Next')+"",
                //     "previous":   ""+Drupal.t('Previous page')+""
                // },
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

        // delete article

        $(document).on('click', '.delete_item', function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var modal = $('<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">');
            var modalDialog = $('<div class="modal-dialog" role="document">');
            var modalContent = $('<div class="modal-content">');
            var modalHeader = $('<div class="modal-header">');
            var modalTitle = $('<h5 class="modal-title" id="exampleModalLabel">Delete article</h5>');
            var modalBody = $('<div class="modal-body">'+Drupal.t('Are you sure you want to delete this article?')+'</div>');
            var modalFooter = $('<div class="modal-footer">');
            var confirmButton = $('<button type="button" class="btn btn-primary" data-dismiss="modal">Yes</button>');
            var cancelButton = $('<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>');
            
            modalHeader.append(modalTitle);
            modalContent.append(modalHeader);
            modalContent.append(modalBody);
            modalFooter.append(confirmButton);
            modalFooter.append(cancelButton);
            modalContent.append(modalFooter);
            modalDialog.append(modalContent);
            modal.append(modalDialog);
        
            $('body').append(modal);
            $('#confirmationModal').modal('show');
            
            confirmButton.click(function() {
                $.ajax({
                    url: '/admin/article/delete/' + id,
                    type: 'GET',
                    data: { id: id },
                    beforeSend: function() {
                    },
                    success: function(data) {
                        var successModal = $('<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">');
                        var successModalDialog = $('<div class="modal-dialog" role="document">');
                        var successModalContent = $('<div class="modal-content">');
                        var successModalHeader = $('<div class="modal-header">');
                        var successModalTitle = $('<h5 class="modal-title" id="exampleModalLabel">Success</h5>');
                        var successModalBody = $('<div class="modal-body">'+Drupal.t('Delete successfully')+'</div>');
                        var successModalFooter = $('<div class="modal-footer">');
                        var closeButton = $('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>');
                    
                        successModalHeader.append(successModalTitle);
                        successModalContent.append(successModalHeader);
                        successModalContent.append(successModalBody);
                        successModalFooter.append(closeButton);
                        successModalContent.append(successModalFooter);
                        successModalDialog.append(successModalContent);
                        successModal.append(successModalDialog);
        
                        $('body').append(successModal);
                        $('#successModal').modal('show');
                        $('#confirmationModal').modal('hide');
                        table.draw();
                    },
                    error: function(data) {
                        alert('Error');
                    }
                });
            });
            
            cancelButton.click(function() {
                $('#confirmationModal').modal('hide');
            });
        });
        

        // view article
        $(document).on('click','.view_article',function(){
            var nid = $(this).data('id');
            var lang = $("#langcode option:selected").val();
            $.ajax({
                type: "GET",
                contentType: "application/json",
                url: "/admin/view/"+nid+"?langcode="+lang+"",
                dataType: 'json',
                success: function (res) {
                    var escapedBody = $('<div>').html(res.data.body_value).text();
                    $('.title_view').text(res.data.title);
                    $('.body_value_view').html(escapedBody);
                    $('#image').attr("src",res.url);
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
                var lang = $("#langcode option:selected").val();
                $.ajax({
                    type: "POST",
                    url: "/admin/update-article/?langcode="+lang+"",
                    data: $('form.quickForm').serialize(),
                    success: function(response) {
                        $(document).find('#successEditModal').remove();
                        var successModal = $('<div class="modal fade" id="successEditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">');
                        var successModalDialog = $('<div class="modal-dialog" role="document">');
                        var successModalContent = $('<div class="modal-content">');
                        var successModalHeader = $('<div class="modal-header">');
                        var successModalTitle = $('<h5 class="modal-title" id="exampleModalLabel">Success</h5>');
                        var successModalBody = $('<div class="modal-body">'+Drupal.t('Updated successfully')+'</div>');
                        var successModalFooter = $('<div class="modal-footer">');
                        var closeButton = $('<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>');
                    
                        successModalHeader.append(successModalTitle);
                        successModalContent.append(successModalHeader);
                        successModalContent.append(successModalBody);
                        successModalFooter.append(closeButton);
                        successModalContent.append(successModalFooter);
                        successModalDialog.append(successModalContent);
                        successModal.append(successModalDialog);
        
                        $('body').append(successModal);
                        $('#successEditModal').modal('show');
                        // alert('Updated successfully');
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
          
    
        $(document).on('change', '#date_from', function (evt) { 
            table
            .draw();
        });
    
        $(document).on('change', '#date_to', function (evt) { 
            table
            .draw();
        });
    });


  

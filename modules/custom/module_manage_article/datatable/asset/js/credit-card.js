
jQuery(document).ready(function($) {
  $('#edit-entity-id').on('input', function() {
    var entityId = $(this).val();
    console.log(entityId)
    if (entityId) {
      $.get('/form/data', { entity_id: entityId }, function(data) {
        console.log(data.langcode)
        $('#edit-langcode').val(data.langcode);
        $('#edit-body-value-value').val(data.body_value);
        $('#edit-field-tags').val(data.field_tags);
        $('#edit-status').prop('checked', data.status);
      }, 'json');
    }
  });
});


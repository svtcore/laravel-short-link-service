$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('input[name="_token"]').val()
    }
});

$('#maintenance_mode').on('change', function() {
    var $toggle = $(this);
    var status = $toggle.val();
    $toggle.prop('disabled', true);

    $.ajax({
        url: $toggle.data('url'),
        method: 'POST',
        data: { status: status },
        success: function(response) {
            console.log('Success:', response);
            
            if (response.success) {
                if (response.secret) {
                    window.location.href = window.location.origin + '/' + response.secret;
                } 
                else {
                    window.location.reload();
                }
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr.responseJSON?.message);
            
            $toggle.val(status == 1 ? 0 : 1);
            
            alert(xhr.responseJSON?.message || 'Error toggling maintenance mode. Please try again.');
        },
        complete: function() {
            $toggle.prop('disabled', false);
        }
    });
});
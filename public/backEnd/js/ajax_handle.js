$(document).ready(function () {
    $(".preloader")
        .fadeOut("slow", function () {
            $(this).hide();
        });
    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf"]').attr('content'),
            'Accept': 'application/json',
        }
    });
    // set Toaster Optinos
    setToastrOptions();
    // delete modal for users
    $('#delete-modal').on('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        var deleteUrl = btn.getAttribute('data-delete-url');
        var message = btn.getAttribute('data-message');
        var name = btn.getAttribute('data-name');
        var modalForm = $(this).find('form[name="confirm-delete-form"]');
        modalForm.attr('action', deleteUrl);
        modalForm.attr('method', 'DELETE');
        $(this).find('.modal-body p').text(message + "\t" + name);
    });
    //Handle delete confirmation form
    $(document).on('submit', 'form[name="confirm-delete-form"]', function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: $(this).attr('method'),
            data: {},
            success: function (response) {
                if (response.is_deleted) {
                    toastr.success(response.message);
                    $('#row-' + response.row_to_delete).parent().parent().parent().remove();
                    $('#delete-modal').modal('hide');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function (response) {
                toastr.error(response.message);
            }
        });
    });
});

function setToastrOptions() {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "slideDown",
        "hideMethod": "fadeOut"
    }
}


/* ############# GENERAL FORM SUBMIT ################ */

$(document).on('submit', '.custom-form', function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    // Show the preloader
    $(".preloader")
        .fadeIn("slow", function () {
            $(this).show();
        });


    $.ajax({
        url: $(this).attr('action'),
        type: $(this).attr('method'),
        processData: false,
        contentType: false,
        data: formData,
        enctype: "multipart/form-data",
        success: function (response) {
            if (response.status) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
            if (response.reset_form) {
                $(this).trigger('reset');
            }
            if (response.modal_to_hide) {
                $(response.modal_to_hide).modal('hide');
            }
            if (response.reload) {
                setTimeout(function () {
                    location.reload();
                }, 1000); // wait for 1 second
            }
            if (response.reload_table) {
                $(response.table).DataTable().ajax.reload();
            }
            if (response.redirect) {
                setTimeout(function () {
                    window.location.href = response.redirect;
                }, 1000); // wait for 1 second
            }
            if (response.elements_changed) {
                $.each(response.elements_changed, function (element, html) {
                    $(element).html(html);
                });
            }
            if (response.function_to_call) {
                openMeetingModal(response.function_params);
            }
            // Hide the preloader
            $(".preloader")
                .fadeOut("slow", function () {
                    $(this).hide();
                });



        }, error: function (response) {
            if (response.status == 422) {
                if (response.responseJSON.errors) {
                    $.each(response.responseJSON.errors, function (key, errorsArray) {
                        $.each(errorsArray, function (item, error) {
                            toastr.error(error);
                        });
                    });
                } else if (response.responseJSON.message) {
                    $.each(response.responseJSON.message, function (item, error) {
                        toastr.error(error);
                    });
                }
            } else if (response.responseJSON && response.responseJSON.message) {
                toastr.error(response.responseJSON.message);
            } else {
                toastr.error(response.message);
            }
            // Hide the preloader
            $(".preloader")
                .fadeOut("slow", function () {
                    $(this).hide();
                });



        }
    });
});

/* ############# GENERAL FORM SUBMIT ################ */




// Quick Toggle is Active status from the table row
function toggleStatus(input) {
    var id = input.data('id');
    var route = input.data('route');
    var status = input.prop('checked') == true ? 1 : 0;
    $.get(route, {
        id: id,
        status: status,
    }, function (reseponse) {
        if (reseponse.status) {
            toastr.success(reseponse.message);
        } else {
            toastr.error(reseponse.message);
        }
    });
}







/**
     * Master Checkbox trigger
     * */
$(document).on('change', '#master-checkbox', function () {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]:not(#master_checkbox)');
    const isChecked = this.checked;
    checkboxes.forEach(function (checkbox) {
        checkbox.checked = isChecked;
    });
});


// change image and preveiw
$('#uploadButton').on('click', function () {
    $('#changeImg').click();
})

$('#changeImg').change(function () {
    var file = this.files[0];
    var reader = new FileReader();
    reader.onloadend = function () {
        $('.image-input-wrapper').css('background-image', 'url("' + reader.result + '")');
    }
    if (file) {
        reader.readAsDataURL(file);
    }
});

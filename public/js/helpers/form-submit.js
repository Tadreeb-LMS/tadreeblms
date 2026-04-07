$.ajaxSetup({
  headers: {
    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
  },
});

$(document).on("submit", "form.ajax", function (event) {
  event.preventDefault();
  let enctype = $(this).prop("enctype");
  if (!enctype) {
    enctype = "application/x-www-form-urlencoded";
  }
  let submitbtn = $(this).find("button[type=submit]");
  submitbtn.prop("disabled", true);

  let obj = $(this);
  $.ajax({
    type: $(this).prop("method"),
    encType: enctype,
    contentType: false,
    processData: false,
    url: $(this).prop("action"),
    data: new FormData($(this)[0]),
    dataType: "json",
    beforeSend: function () {
      $("#loader").removeClass("d-none");
      // Show loading spinner on button
      submitbtn.find('.btn-text').addClass('d-none');
      submitbtn.find('.btn-spinner').removeClass('d-none');
    },
    complete: function () {
      $("#loader").addClass("d-none");
    },
    success: function (data) {
      showSuccessMessage(data);
      
      // Keep spinner visible briefly while toast shows (2.5 seconds), then hide
      setTimeout(() => {
        submitbtn.find('.btn-text').removeClass('d-none');
        submitbtn.find('.btn-spinner').addClass('d-none');
        submitbtn.prop("disabled", false);
      }, 2500);
      
      
      // handleModals(data);

      // close if any modal is opened
      if ($(".modal").length) {
        closeModalWithParams(data);
      }

      if (data.event) {
        obj.trigger(data.event, data.params ?? []);
      }
    },
    error: function (data) {
      // Hide loading spinner on error
      submitbtn.find('.btn-text').removeClass('d-none');
      submitbtn.find('.btn-spinner').addClass('d-none');
      submitbtn.prop("disabled", false);

      //alert("yes")
      showErrorMessage(obj, data);

      if (data.responseJSON?.event) {
        obj.trigger(data.responseJSON.event);
      }
    },
  });
});

function closeModalWithParams(obj, data, params = "") {
  // $('[data-dismiss="modal"]').trigger("click");
  $(".modal").modal("hide");

  $(".modal").on("hidden.bs.modal", function () {
    $(this).trigger(data?.event ?? "modalDataSaved", params);
  });
  // if ($(".modal").length) {
  //     $(".modal-backdrop").remove();
  //     $("body").removeClass("modal-open");
  //     $("#modalContainer").html("");
  //     obj.trigger(data?.event ?? "modalDataSaved", params);
  // }
}

function handleModals(data) {
  if (data.close_modal) {
    $(`#${data.close_modal}`).modal("hide");
  }
  if (data.next_modal) {
    $(`#${data.next_modal}`).modal("show");
  }
}
function showSuccessMessage(data) {
  toastr.remove();
  if (data.message) {
    toastr[data?.type ?? "success"](data.message);
  }
  if (data.redirect_route) {
    setTimeout(() => {
      window.location.href = data.redirect_route;
    }, 1000);
  }
}

function showErrorMessage(obj, data) {
  toastr.remove();
  $(".text-danger").remove();
  $(".is-invalid").removeClass("is-invalid");

  const errors = data.responseJSON?.errors || {};
  let hasMappedFieldError = false;
  let firstUnmappedError = null;

  for (var field in errors) {
    if (errors.hasOwnProperty(field)) {
      errors[field].forEach(function (errorMessage) {
        if (obj.find(`[name=${field}]`).length) {
          hasMappedFieldError = true;
          obj
            .find(`[name=${field}]`)
            .addClass("is-invalid")
            .parent()
            .append(`<span class="text-danger w-100">${errorMessage}</span>`);
        }
        if (
          !obj.find(`[name=${field}]`).length &&
          obj.find(`[name="${field}[]"]`).length
        ) {
          hasMappedFieldError = true;
          obj
            .find(`[name="${field}[]"]`)
            .addClass("is-invalid")
            .parent()
            .append(`<span class="text-danger w-100">${errorMessage}</span>`);
        }

        if (!firstUnmappedError && !obj.find(`[name=${field}]`).length && !obj.find(`[name="${field}[]"]`).length) {
          firstUnmappedError = errorMessage;
        }
      });
    }
  }

  scrollToClass("text-danger");

  if (!hasMappedFieldError && firstUnmappedError) {
    toastr["error"](firstUnmappedError);
  }

  if (data.status == 400) {
    toastr["error"](data.responseJSON.message);
  }
  if (data.status == 500) {
    const serverMessage = data.responseJSON?.message || "Something went wrong";
    toastr["error"](serverMessage);
  }
}

function scrollToClass(className) {
  // Find the first element with the specified class
  var targetElement = $(`form .${className}:first`);

  // Check if the element exists
  if (targetElement.length) {
    // Calculate the offset of the target element
    var offsetTop = targetElement.offset().top - 300;

    // Animate the scroll to the target element
    $("html, body").animate(
      {
        scrollTop: offsetTop,
      },
      800
    );
  }
}

"use strict";

$(function () {
  function appendDeleteModal() {
    const delete_modal = `

  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel"
      aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-body">

                  <div class="centerContPopup">
                      <div class="suspendContBlock">
                          <div class="icon"><i id="status-icon" class="icon-delete"></i></div>
                          <h4 id="Modal_head"></h4>
                          <p id="Modal_msg"></p>
                      </div>
                  </div>

                  <div class="text-center mt-4">
                      <button type="button" class="btn btn-primary me-2 modal-confirm" id="confirm-delete">Confirm</button>
                      <button type="button" data-bs-dismiss="modal"
                          class="btn btn-outline-primary waves-effect">Cancel</button>
                  </div>

              </div>

          </div>
      </div>
  </div>`;

    $("#confirmModal").remove();
    $("body").append(delete_modal);
  }

  // delete user
  $(document).on("click", ".delete-record", function (e) {
    e.preventDefault();
    appendDeleteModal();
    const data_url = $(this).data("url");
    let data_method = "get";
    if ($(this)?.data("method")?.length) {
      data_method = $(this).data("method");
    }
    const name = $(this).attr("data-name");
    const type = $(this).attr("data-type");
    $("#Modal_head").html(`${type} ${name}`);
    $("#Modal_msg").html(`Are you sure! Do you want to ${type} the ${name}?`);

    $("#confirm-delete").attr("data-url", data_url);
    $("#confirm-delete").attr("data-method", data_method);
    $("#delete_modal_name").text(name);

    $("#confirmModal").modal("show");
  });

  $(document).on("click", "#confirm-delete", function () {
    $(this).addClass("disabled");
    $.ajax({
      type: $(this).data("method"),
      url: $(this).data("url"),
      beforeSend: function () {
        $("#loaderOverlay").addClass("d-flex");
      },
      complete: function () {
        $("#loaderOverlay").removeClass("d-flex");
      },
      success: function (response) {
        displayNotification("success", "Operation successful", 5000);
        $("#confirmModal").modal("hide");
      },
      error: function (response) {
        const errors = response?.responseJSON?.errors;

        if (errors) {
          $.each(errors, function (key, value) {
            $.each(value, function (index, message) {
              displayNotification("error", message, 5000);
              // Example: Append to an error list
              $("#error-container").append("<li>" + message + "</li>");
            });
          });
        } else {
          displayNotification(
            "error",
            response?.responseJSON?.message ?? "Something went wrong!",
            5000,
          );
        }

        $("#confirmModal").modal("hide");
      },
    });
  });
  $(document).on("click", '[data-bs-dismiss="modal"]', function () {
    $("#confirmModal").modal("hide");
  });

  function displayNotification(type, message) {
    if (typeof toastr !== "undefined") {
      if (type === "success") {
        toastr.success(message);
      } else if (type === "error") {
        toastr.error(message);
      } else if (type === "warning") {
        toastr.warning(message);
      } else {
        toastr.info(message);
      }
    } else {
      alert(message);
    }
  }
});

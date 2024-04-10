$(function () {
  $('[data-toggle="tooltip"]').tooltip();

  $(document).on("change", 'input[type="file"]', function () {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").text(fileName);
  });

  $(".sug-vendor").autocomplete({
    source: function (request, response) {
      $.ajax({
        url: "/app/vendor/autocomplete",
        dataType: "json",
        data: {
          term: request.term,
        },
        success: function (data) {
          response(data);
        },
      });
    },
    minLength: 2,
  });

  $(document).on("click", ".btn-spinner", function () {
    $(this).prop("disabled", true);
    var originalText = $(this).text();
    $(this).html(
      '<div class="spinner-grow text-light spinner-grow-sm" role="status"><span class="sr-only">Loading...</span></div>'
    );
    var $button = $(this);
    setTimeout(function () {
      $button.prop("disabled", false);
      $button.html(originalText);
    }, 60000);
  });
});

function showKittingStatus(po_status){
  if (po_status == 'Pending') {
      return `<span class="text-danger">${po_status}</span>`;
  } else if (po_status == 'Completed') {
      return `<span class="text-success">${po_status}</span>`;
  } else {
      return `<span class="text-warning">${po_status}</span>`;
  }
}

$('.modal-dialog').draggable({
  handle: ".modal-header"
});

function resetModalPosition() {
  $('.modal-dialog').css({
    top: 0,
    left: 0
  });
}

$('.modal').on('hidden.bs.modal', function () {
  resetModalPosition();
});

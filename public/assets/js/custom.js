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

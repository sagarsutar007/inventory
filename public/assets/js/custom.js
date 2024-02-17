$(function () {
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
});

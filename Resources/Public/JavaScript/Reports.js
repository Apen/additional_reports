require(["jquery"], function ($) {
  function jumpToUrl (URL) {
    window.location.href = URL
  }

  $("#choice-display input[name='display']").click(function () {
    jumpToUrl($(this).data("url"))
  })
  $("#filtersCat").on("change", function () {
    jumpToUrl($(this).data("url") + "&filtersCat=" + $(this).val())
  })
})
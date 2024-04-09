document.addEventListener("DOMContentLoaded", function () {
    function jumpToUrl(URL) {
        window.location.href = URL;
    }

    document.querySelectorAll("#choice-display input[name='display']").forEach(function (input) {
        input.addEventListener("click", function () {
            jumpToUrl(this.dataset.url);
        });
    });

    if (document.getElementById("filtersCat")) {
        document.getElementById("filtersCat").addEventListener("change", function () {
            jumpToUrl(this.dataset.url + "&filtersCat=" + this.value);
        });
    }
});
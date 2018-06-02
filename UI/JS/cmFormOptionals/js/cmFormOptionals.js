
var cmFormOptionals = {
    init: function () {
        $("select.has-optionals").on("change change-update", function () {
            cmFormOptionals.showOptionals(this);
		}).trigger("change-update");
    },
    showOptionals: function (elem) {
        var form = $(elem.form);
        var name = $(elem).attr("name");
        var type = $(elem).attr("type");
        var value = name+"-" + $(elem).val();
        if (type === "checkbox") {
            value = name + "-" + $(elem).prop("checked");
        }
        var toHide = form.find(".optional." + name).not("." + value);
        var toShow = form.find(".optional." + value);
        if (!$(elem).data("status")) {
            toHide.hide();
            toShow.show();
            $(elem).data("status", 1);
            return;
        }
        switch ($(elem).data('animation')) {
        case 'fade':
            toHide.fadeOut();
            toShow.fadeIn();
            break;
        case 'slide':
            toHide.slideUp($(elem).data('speed-hide'));
            toShow.slideDown($(elem).data('speed-show'));
            break;
        default:
            toHide.hide();
            toShow.show();
        }
    }

};

function showOptionals (elem) {
    cmFormOptionals.showOptionals(elem);
}

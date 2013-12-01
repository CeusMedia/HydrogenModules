/*jslint browser: true */
(function ($) {
    "use strict";
    $.fn.darkbox = function (options) {
        var settings = $.extend({
            durationFadeIn: 200,
            durationFadeOut: 250,
            prefix: 'darkbox-',
            btnCloseLabel: '×',
            btnCloseTitle: 'Close (Esc)'
        }, options);

        if (!$('#' + settings.prefix + 'container').size()) {
            $('body').append('<div id="' + settings.prefix + 'container' + '"></div>');
            $('#' + settings.prefix + 'container').bind('click', function () {
                $(this).fadeOut(settings.durationFadeOut, function () {
                    $(this).html('');
                });
            });
        }

        this.each(function () {
            var link = $(this);
            link.bind('click', function (event) {
                event.stopPropagation();
                event.preventDefault();
                var url = link.attr('href'),
                    title = link.children('img').attr('alt'),
                    image = $('<img/>').attr('src', url),
                    wrapper = $('<div></div>').append(image),
                    figure = $('<figure></figure>').append(wrapper),
                    container = $('#' + settings.prefix + 'container'),
                    btnClose = $('<button></button>').html(settings.btnCloseLabel);
                if (title) {
                    figure.append($('<figcaption></figcaption>').html(title));
                }
                btnClose.attr('title', settings.btnCloseTitle);
                btnClose.attr('type', 'button').addClass(settings.prefix + 'button-close');
                container.html(figure).append(btnClose);
                container.fadeIn(settings.durationFadeIn);
            });
        });
        return this;
    };
}(window.jQuery || window.Zepto));

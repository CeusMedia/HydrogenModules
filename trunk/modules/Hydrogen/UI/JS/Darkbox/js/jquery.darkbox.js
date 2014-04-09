/*jslint browser: true, debug: true, unparam: true, vars: true */
(function ($) {
    "use strict";
    $.fn.darkbox = function (options) {
        var settings = $.extend({
            durationFadeIn: 200,
            durationFadeOut: 250,
            prefix: 'darkbox-',
            btnCloseLabel: '×',
            btnCloseTitle: 'Close (Esc)',
			onOpen: function (event) {},
			onOpened: function (event) {},
			onClose: function (event) {},
			onClosed: function (event) {}
        }, options);
        var body = $('body');
        var container = $('#' + settings.prefix + 'container');
        if (!container.size()) {
            $('body').append('<div id="' + settings.prefix + 'container' + '"></div>');
            container = $('#' + settings.prefix + 'container');
            container.bind('click', {body: body, container: container}, function (event) {
				settings.onClose(event);
                $(this).fadeOut(settings.durationFadeOut, function () {
                    event.data.body.css('overflow', event.data.container.data('darkbox-overflow'));
                    $(this).html('');
					settings.onClosed(event);
                });
            });
        }
        this.each(function () {
            var link = $(this);
            link.bind('click', {body: body, container: container}, function (event) {
				settings.onOpen(event);
                event.data.container.data('darkbox-overflow', event.data.body.css('overflow'));
                event.data.body.css('overflow', 'hidden');
                event.stopPropagation();
                event.preventDefault();
                var url = link.attr('href'),
                    title = link.children('img').attr('alt'),
                    image = $('<img/>').attr('src', url),
                    wrapper = $('<div></div>').append(image),
                    figure = $('<figure></figure>').append(wrapper),
                    btnClose = $('<button></button>').html(settings.btnCloseLabel);
                if (title) {
                    figure.append($('<figcaption></figcaption>').html(title));
                }
                btnClose.attr('title', settings.btnCloseTitle);
                btnClose.attr('type', 'button').addClass(settings.prefix + 'button-close');
                container.html(figure).append(btnClose);
                container.fadeIn(settings.durationFadeIn, function () {
                    settings.onOpened(event);
                });
            });
        });
        return this;
    };
}(window.jQuery || window.Zepto));

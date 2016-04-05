$('document').ready(function() {
    $body = $('body');

    $body.on('submit', 'form', function(e) {
        e.preventDefault();

        _ajax.doFormSubmit($(this));
    });
});

var _ajax = {
    doFormSubmit: function($form) {
        var data = $form.serializeArray();
        data.push({name: 'module', value: $form.attr('data-module')});
        data.push({name: 'action', value: $form.attr('data-action')});
        data.push({name: 'origin', value: 'form#' + $form.attr('id')});

        $.ajax({
            async: false,
            cache: false,
            complete: _ajax.completeHandler,
            data: data,
            dataType: 'json',
            timeout: 10
        });
    },

    completeHandler: function(data) {
        var response = $.parseJSON(data.responseText);

        // updateHTML
        if (response.update_html.length > 0) {
            for (var i = 0; i < response.update_html.length; i++) {
                _ajax.responseHandler.updateHtml(response.update_html[i]);
            }
        }

        // redirect
        if (response.redirect != null) {
            window.location.href = response.redirect;
        }
    },

    responseHandler: {
        updateHtml: function(details) {
            $(details.selector).html(details.html);
        }
    }
};
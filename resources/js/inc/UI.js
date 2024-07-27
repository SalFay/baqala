window.ui = function () {
    return {

        /**
         *
         */
        $window: $(window),

        /**
         *
         */
        $document: $(document),

        /**
         *
         */
        $body: $('body'),

        /**
         * Bootstrap the app with events and actions
         */
        init: function () {
            ui.$body.on('keypress', 'input,select,textarea', function () {
                ui._removeErrorBlock($(this))
            })
            ui.$body.on('change', 'input,select,textarea', function () {
                ui._removeErrorBlock($(this))
            })
        },

        /**
         *
         * @param open ID of the section to Open
         * @param close ID of the section to Close
         */
        toggleSection: function (open, close) {
            $('#' + open).slideDown()
            $('#' + close).slideUp()
        },

        successMessage: function (text) {
            $.toast({
                heading: 'Success',
                text: text,
                icon: 'success',
                position: 'top-right',
            })
        },

        errorMessage: function (text) {
            $.toast({
                heading: 'Error',
                text: text,
                icon: 'error',
                position: 'top-right',
            })
        },

        warningMessage: function (text) {
            $.toast({
                heading: 'Alert!',
                text: text,
                icon: 'warning',
                position: 'top-right',
            })
        },

        infoMessage: function (text) {
            $.toast({
                heading: 'Info',
                text: text,
                icon: 'info',
                position: 'top-right',
            })
        },

        ajaxError: function (res) {
            if (!_.isEmpty(res.responseJSON)) {
                let msg = res.responseJSON.message
                if (msg === 'Unauthenticated.') {
                    ui.loginDialog()
                    return
                }
                let errorBag = $('<ul></ul>')
                _.forEach(res.responseJSON.errors, function (v, k) {
                    console.log(k)
                    errorBag.append('<li>' + v.toString() + '</li>')
                    /* --------------------------------------------------------------
                     *  Add Validation errors
                     * --------------------------------------------------------------
                     */
                    let field = $('[name="' + k + '"]')
                    let $wrapper = field.closest('div')
                    $wrapper.addClass('has-error')
                    //let inputType = field.attr('type');
                    //if (inputType !== 'checkbox' || inputType !== 'radio') {
                    let $help = $wrapper.find('.help-block')
                    if (!$help.length) {
                        $wrapper.append('<span class="help-block"></span>')
                        $help = $wrapper.find('.help-block')
                    }
                    $help.html(v.toString())
                    //}
                })
                ui.errorMessage(msg + errorBag.html())
                return true
            }
            ui.errorMessage(res.statusText)
        },

        _removeErrorBlock: function ($el) {
            let $wrapper = $el.closest('div')
            $wrapper.removeClass('has-error')
            $wrapper.find('.help-block').remove()
        }

    }
}()// AdminApp
window.ui.init()

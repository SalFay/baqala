let UI = {

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
        if ($.fn.dataTable !== undefined) {
            $.extend(true, $.fn.dataTable.defaults, {
                dom: 'tr<"row data-table-footer"<"col-sm-6"i><"col-sm-6"p>>',
            })
        }

        setTimeout(function () {
            $('[title]:not(.iconpicker-item)').tooltip()
            $('[data-title]').tooltip()
        }, 1000)

        UI.$body.on('keypress', 'input,select,textarea', function () {
            UI._removeErrorBlock($(this))
        })
        UI.$body.on('change', 'input,select,textarea', function () {
            UI._removeErrorBlock($(this))
        })
        /* --------------------------------------------------------------
         *  Show Login
         * --------------------------------------------------------------
         */
        UI.$body.on('click', '#login-popup', function (e) {
            e.preventDefault()
            UI.loginDialog()
        })
    },

    loginDialog: function () {
        $.confirm({
            content: $('#login-lightbox').html(),
            buttons: {
                formSubmit: {
                    text: 'Change Password',
                    btnClass: 'btn-success',
                    action: function () {
                        window.location.href = '/password/reset'
                    }
                },
                cancel: function () {
                    //close
                },
            },
            onContentReady: function () {
                /*// bind to events
                let jc = this
                this.$content.find('form').on('submit', function (e) {
                    // if the user submits the form by pressing enter in the field.
                    e.preventDefault()
                    jc.$$formSubmit.trigger('click') // reference the button and click it
                })*/
            }
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
                UI.loginDialog()
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
            UI.errorMessage(msg + errorBag.html())
            return true
        }
        UI.errorMessage(res.statusText)
    },

    _removeErrorBlock: function ($el) {
        let $wrapper = $el.closest('div')
        $wrapper.removeClass('has-error')
        $wrapper.find('.help-block').remove()
    }

}// AdminApp

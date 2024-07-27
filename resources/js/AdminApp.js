window.AdminApp = {

  $body: $('body'), toggleSection: function (open, close) {
    $('#' + close).hide()
    $('#' + open).show()
  },

  /* --------------------------------------------------------------
   *  Alerts
   * --------------------------------------------------------------
   */
  success: function (message) {
    toastr.options.progressBar = true
    toastr.options.closeButton = true
    toastr.options.positionClass = 'toast-top-center'
    toastr.success(message, 'Success')
  },

  error: function (message) {
    toastr.options.progressBar = true
    toastr.options.closeButton = true
    toastr.options.positionClass = 'toast-top-center'
    toastr.error(message)
  },

  ajaxError: function (res) {
    if (res.responseJSON !== '' && res.responseJSON !== null && res.responseJSON !== undefined) {
      if (res.responseJSON.errors) {
        let msg = res.responseJSON.message
        let errorBag = $('<ul></ul>')
        $.each(res.responseJSON.errors, function (v, k) {
          console.log(k)
          errorBag.append('<li>' + k.toString() + '</li>')
          /* --------------------------------------------------------------
           *  Add Validation errors
           * --------------------------------------------------------------
           */
          let field = $('[name="' + v + '"]')
          let $wrapper = field.closest('div')
          $wrapper.addClass('has-error')
          //let inputType = field.attr('type');
          //if (inputType !== 'checkbox' || inputType !== 'radio') {
          let $help = $wrapper.find('.help-block').removeAttr('style')
          if (!$help.length) {
            $wrapper.append('<span class="help-block"></span>')
            $help = $wrapper.find('.help-block')
          }
          $help.html(k.toString())
          //}
        })
        AdminApp.error(msg + errorBag.html())
      } else {
        let $msgBody = res.responseJSON.message
        if (typeof $msgBody === 'string') {
          AdminApp.error(res.responseJSON.message)
        } else {
          let errorBag = $('<ol></ol>')
          $.each(res.responseJSON.message, function (i, j) {
            errorBag.append('<li>' + i.toString() + '</li>')
            $.each(j, function (v, k) {
              errorBag.append('<li>' + k.toString() + '</li>')
              /* --------------------------------------------------------------
               *  Add Validation errors
               * --------------------------------------------------------------
               */
              let field = $('[name="' + v + '"]')
              let $wrapper = field.closest('div')
              $wrapper.addClass('has-error')
              //let inputType = field.attr('type');
              //if (inputType !== 'checkbox' || inputType !== 'radio') {
              let $help = $wrapper.find('.help-block').removeAttr('style')
              if (!$help.length) {
                $wrapper.append('<span class="help-block"></span>')
                $help = $wrapper.find('.help-block')
              }
              $help.html(k.toString())
              //}
            })
          })
          AdminApp.error(errorBag.html())
        }
      }
      return true
    }
    AdminApp.error(res.statusText)
  }
  ,
  ajaxErrors: function (res) {
    if (res.responseJSON !== '' && res.responseJSON !== null && res.responseJSON !== undefined) {
      $.each(res.responseJSON.errors, function (v, k) {
        /* --------------------------------------------------------------
         *  Add Validation errors
         * --------------------------------------------------------------
         */
        let field = $('[name="' + v + '"],[name="' + v + '[]"]')
        let $wrapper = field.closest('div')
        $wrapper.addClass('has-error')
        //let inputType = field.attr('type');
        //if (inputType !== 'checkbox' || inputType !== 'radio') {
        let $help = $wrapper.find('.help-block').removeAttr('style')
        if (!$help.length) {
          $wrapper.append('<span class="help-block"></span>')
          $help = $wrapper.find('.help-block')
        }
        $help.html(k.toString())
        //}
      })
      return true
    }
  }
  ,

  warning: function (message) {
    toastr.options.progressBar = true
    toastr.options.closeButton = true
    toastr.options.positionClass = 'toast-top-center'
    toastr.warning(message, 'Warning')
  }
  ,

  info: function (message) {
    toastr.options.progressBar = true
    toastr.options.closeButton = true
    toastr.options.positionClass = 'toast-top-center'
    toastr.info(message, 'Info')
  }
  ,

  blockCard: function ($el) {
    $el.closest('.card').block({
      message: feather.icons['refresh-cw'].toSvg({ class: 'font-large-3 spinner text-primary' }), css: {
        backgroundColor: 'transparent', border: 'none'
      }, overlayCSS: {
        backgroundColor: '#fff',
      },
    })
  }
  ,
  unblockCard: function ($el) {
    $el.closest('.card').unblock()
  }
  ,

  blockElement: function (element) {
    $(element).block({
      message: feather.icons['refresh-cw'].toSvg({ class: 'font-large-3 spinner text-primary' }), css: {
        backgroundColor: 'transparent', border: 'none'
      }, overlayCSS: {
        backgroundColor: '#fff',
      },
    })
  }
  ,

  unblockElement: function (element) {
    $(element).unblock()
  }
  ,

  buttonLoader: function (button, end, text) {
    if (typeof button !== 'object') {
      button = $(button)
    }

    if (!text) {
      text = button.attr('data-text')
    }

    if (end) {
      button.html(text)
      button.removeAttr('disabled')
      return
    }

    let template = '<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>\n' + '<span class="ml-25 align-middle">Loading...</span>'
    button.html(template).attr('disabled', true)
  }
  ,

  cardUi: function (content, title) {
    if (!title) {
      title = ''
    }
    let tpl = '<div class="card"><div class="card-header"><div class="card-title">{title}</div></div>{body}</div>'
    tpl = tpl.replace('{body}', '<div class="card-body">{content} <br> <br> <div id="timerDiv" ></div></div>')
    tpl = tpl.replace('{title}', title)
    tpl = tpl.replace('{content}', content)
    return tpl
  }

}// AdminApp

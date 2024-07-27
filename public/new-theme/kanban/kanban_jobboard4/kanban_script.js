$('.arrowdiv').click(function () {
  let upArrowText = '<i class="me-3 fa fa-arrow-up" aria-hidden="true"></i>'
  let downArrowText = '<i class="me-3 fa fa-arrow-down" aria-hidden="true"></i>'

  let idd = $(this).attr('id')
  let name = $(this).attr('name')

  if (name == 'arrowDown') {
    $(this).attr('name', 'arrowUp')
    $(this).html(upArrowText)
    document.getElementById('table' + idd).style.display = 'none'
  } else {
    $(this).attr('name', 'arrowDown')
    $(this).html(downArrowText)
    document.getElementById('table' + idd).style.display = 'block'
  }

})


// check for saved 'darkMode' in localStorage
let emptyColumn = localStorage.getItem('emptyToggle')

const emptyColumnToggle = document.querySelector('#toggleColumn')

const enableEmptyToggle = () => {
  emptyColumnToggle.setAttribute('checked', 'checked')
  localStorage.setItem('emptyToggle', 'checked')
}

const disableEmptyToggle = () => {
  emptyColumnToggle.removeAttribute('checked')
  localStorage.setItem('emptyToggle', null)
}

// If the user already visited and enabled darkMode
// start things off with it on
if (emptyColumn === 'checked') {
  enableEmptyToggle()
}

// When someone clicks the button
emptyColumnToggle.addEventListener('click', () => {
  // get their darkMode setting
  emptyColumn = localStorage.getItem('emptyToggle')

  // if it not current enabled, enable it
  if (emptyColumn !== 'checked') {
    enableEmptyToggle()
    // if it has been enabled, turn it off
  } else {
    disableEmptyToggle()
  }
})

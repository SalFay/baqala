//variables
let cardBeignDragged
let dropzones = document.querySelectorAll('.dropzone')
let priorities
// let cards = document.querySelectorAll('.kanbanCard');
let dataColors = [
  { color: 'position1', title: 'Verifing Paperwork' },
  { color: 'position2', title: 'On Hold' },
  { color: 'position3', title: 'Financier Stipulation' },
  { color: 'position4', title: 'Change Order' },
  { color: 'position5', title: 'Paperwork Complete' },
  { color: 'position6', title: 'Site Survey Scheduled' },
  { color: 'position7', title: 'Site Survey Report Audit' },
  { color: 'position8', title: 'Site Survey Completed' },
  { color: 'position9', title: 'CAD Design' },
  { color: 'position10', title: 'Engineering Review' }
]
let dataCards = {
  config: {
    maxid: 0
  },
  cards: []
}
let theme = 'light'

//initialize

function addDummyValues () {
  let id = 1
  const newCard = {
    id,
    position: 'position1',
    priority: false
  }
  dataCards.cards.push(newCard)
  dataCards.config.maxid = id
  save()
  appendComponents(newCard)
  initializeCards()

  id = 2
  let newCard1 = {
    id,
    position: 'position1',
    priority: false
  }
  dataCards.cards.push(newCard1)
  dataCards.config.maxid = id
  save()
  appendComponents(newCard1)
  initializeCards()

  id = 3
  let newCard2 = {
    id,
    position: 'position1',
    priority: false
  }
  dataCards.cards.push(newCard2)
  dataCards.config.maxid = id
  save()
  appendComponents(newCard2)
  initializeCards()
}

$(document).ready(() => {
  dataCards = {
    config: {
      maxid: 0
    },
    cards: []
  }
  save()
  $('#loadingScreen').addClass('d-none')
  theme = localStorage.getItem('@kanban:theme')
  if (theme) {
    $('body').addClass(`${theme === 'light' ? '' : 'darkmode'}`)
  }
  initializeBoards()
  if (JSON.parse(localStorage.getItem('@kanban:data'))) {
    dataCards = JSON.parse(localStorage.getItem('@kanban:data'))
    initializeComponents(dataCards)
  }
  initializeCards()
  addDummyValues()
})

//functions
function initializeBoards () {
  dataColors.forEach(item => {
    let htmlString = `
		<div class="board" style="margin-top: 10px; background: #9B9B9B; border-radius: 10px; box-shadow: 0 2px 14px 0 rgb(0 0 0 / 16%">
			<div class="card thead_item">
					<h3 style="background-color: #9B9B9B; color: white; font-size: 19px; " class="text-center">${item.title.toUpperCase()}</h3>
			</div>
			<div class="dropzone" style="margin-top: -35px;" id="${item.color}">
					
		   </div>
		</div>
        `
    $('#boardsContainer').append(htmlString)
  })
  let dropzones = document.querySelectorAll('.dropzone')
  dropzones.forEach(dropzone => {
    dropzone.addEventListener('dragenter', dragenter)
    dropzone.addEventListener('dragover', dragover)
    dropzone.addEventListener('dragleave', dragleave)
    dropzone.addEventListener('drop', drop)
  })
}

function initializeCards () {
  cards = document.querySelectorAll('.kanbanCard')

  cards.forEach(card => {
    card.addEventListener('dragstart', dragstart)
    card.addEventListener('drag', drag)
    card.addEventListener('dragend', dragend)
  })
}

function initializeComponents (dataArray) {
  //create all the stored cards and put inside of the todo area
  dataArray.cards.forEach(card => {
    appendComponents(card)
  })
}

function appendComponents (card) {
  //creates new card inside of the todo area
  let htmlString = `
		<div id=${card.id.toString()} class="kanbanCard ${card.position}" draggable="true">
			<div class="card" style="height: 320px;">
				<div class="card-body">
					<h5>M Elezibeth Throne</h5>
					<h6>639G Ministrial House, NY, USA</h6>
					<div><span class="gray_label">7KW: National Grid</span></div>
					<div class="linee"></div>
					<div><span class="gray_label">Sales Rep - Muhammad Suleman</span></div>
					<div><span class="gray_label">Manager - Salman Sherin</span></div>
					<div class="linee"></div>
					<div><span class="gray_label">Survey - 12-12-2021 01:12:12</span></div>
					<div><span class="gray_label">Installation: TBD</span></div>
					<div class="linee"></div>
					<div style="margin-top: -2px;"><span class="gray_label">Day Since</span></div>
					<div>
						<div class="row" style="text-align: center;">
							<div class="col-sm-6"><span class="gray_label">Created</span></div>
							<div class="col-sm-6"><span class="gray_label">Updated</span></div>
						</div>
						<div class="row" style="text-align: center; height: 35px;">
							<div class="col-sm-6"><div class="card" style="background-color: green; color: white; width: 30px; display: inline-block;">24</div></div>
							<div class="col-sm-6"><div class="card" style="background-color: orange; color: white; width: 30px; display: inline-block;">24</div></div>
						</div>
						<div class="linee"></div>
						<div style="margin-top: -2px;"><span class="gray_label">Day Till</span></div>
						<div class="row" style="text-align: center;">
							<div class="col-sm-6"><span class="gray_label">Cr. Expiration</span></div>
							<div class="col-sm-6"><span class="gray_label">Installation</span></div>
						</div>
						<div class="row" style="text-align: center; height: 35px;">
							<div class="col-sm-6"><div class="card" style="background-color: purple; color: white; width: 30px; display: inline-block;">24</div></div>
							<div class="col-sm-6"><div class="card" style="background-color: blue; color: white; width: 30px; display: inline-block;">24</div></div>
						</div>
					</div>
				</div>
			</div>
		</div>
    `
  $(`#${card.position}`).append(htmlString)
  priorities = document.querySelectorAll('.priority')
}

function togglePriority (event) {
  event.target.classList.toggle('is-priority')
  dataCards.cards.forEach(card => {
    if (event.target.id.split('-')[1] === card.id.toString()) {
      card.priority = card.priority ? false : true
    }
  })
  save()
}

function removeClasses (cardBeignDragged, color) {
  cardBeignDragged.classList.remove('red')
  cardBeignDragged.classList.remove('blue')
  cardBeignDragged.classList.remove('purple')
  cardBeignDragged.classList.remove('green')
  cardBeignDragged.classList.remove('yellow')
  cardBeignDragged.classList.add(color)
  position(cardBeignDragged, color)
}

function save () {
  localStorage.setItem('@kanban:data', JSON.stringify(dataCards))
}

function position (cardBeignDragged, color) {
  const index = dataCards.cards.findIndex(card => card.id === parseInt(cardBeignDragged.id))
  dataCards.cards[index].position = color
  save()
}

//cards
function dragstart () {
  dropzones.forEach(dropzone => dropzone.classList.add('highlight'))
  this.classList.add('is-dragging')
}

function drag () {

}

function dragend () {
  dropzones.forEach(dropzone => dropzone.classList.remove('highlight'))
  this.classList.remove('is-dragging')
}

// Release cards area
function dragenter () {

}

function dragover ({ target }) {
  this.classList.add('over')
  cardBeignDragged = document.querySelector('.is-dragging')
  if (this.id === 'yellow') {
    removeClasses(cardBeignDragged, 'yellow')

  } else if (this.id === 'green') {
    removeClasses(cardBeignDragged, 'green')
  } else if (this.id === 'blue') {
    removeClasses(cardBeignDragged, 'blue')
  } else if (this.id === 'purple') {
    removeClasses(cardBeignDragged, 'purple')
  } else if (this.id === 'red') {
    removeClasses(cardBeignDragged, 'red')
  }

  this.appendChild(cardBeignDragged)
}

function dragleave () {

  this.classList.remove('over')
}

function drop () {
  this.classList.remove('over')
}
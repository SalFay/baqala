/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')
require('./inc/plugins')
require('./inc/admin')
require('./inc/UI')


window.Pusher = require('pusher-js')

/*
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '29b747b2f10c33b68602',
    cluster: 'us2',
    authEndpoint: '/api/auth-with-channel',
    auth: {
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('_token')
        }
    },
    encrypted: true
    //forceTLS: true,
    //client: Pusher
})
 */

/*
window.Echo.private('designs')
    .listen('designChange', function (e) {

    });
 */

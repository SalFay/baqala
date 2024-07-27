window.$ = window.jQuery = jQuery
window.toastr = require('./../admin/vendors/js/extensions/toastr.min')
window.IMask = require('imask').default

/**
 * Admin temp
 */
window.ApexCharts = require('./../admin/vendors/js/charts/apexcharts')

require('./../admin/js/core/app-menu')
require('./../admin/js/core/app')
require('./../admin/js/scripts/forms/form-wizard.min')
require('./../admin/js/scripts/pages/app-todo.min')
require('./../admin/js/scripts/customizer.min')
require('./../admin/js/scripts/components/components-tooltips.min')
//require('./../admin/js/scripts/pages/dashboard-ecommerce')
require('./../admin/js/scripts/charts/chart-apex.min')
require('./../admin/vendors/js/tables/datatable/jquery.dataTables.min')
window.Swal = require('./../admin/vendors/js/extensions/sweetalert2.all.min')
require('./AdminApp')
require('./inc/darkMode.js')


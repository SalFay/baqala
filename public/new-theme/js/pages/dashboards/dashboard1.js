/*
Template Name: Admin Pro Admin
Author: Wrappixel
Email: niravjoshi87@gmail.com
File: js
*/

var jobChartData = document.getElementById('jobsChartData')

var jobsNumbers = [{
  label: 'On Hold',
  color: '#ff4000',
  data: 17
}, {
  label: 'Initial Stage',
  color: '#00ff40',
  data: 17
}, {
  label: 'Design Stage',
  color: '#00bfff',
  data: 20
}, {
  label: 'Legal Stage',
  color: '#8000ff',
  data: 30
}, {
  label: 'Completed',
  color: '#ff0080',
  data: 15
}]

$(function () {
  'use strict'
  // ==============================================================
  // Newsletter
  // ==============================================================

  var offset = 0
  plotJobsGraph()

  /*function plot() {
      var sin = [],
          cos = [];
      for (var i = 0; i < 12; i += 0.2) {
          sin.push([i, Math.sin(i + offset)]);
          cos.push([i, Math.cos(i + offset)]);
      }
      var options = {
          series: {
              lines: {
                  show: true
              },
              points: {
                  show: true
              }
          },
          grid: {
              hoverable: true //IMPORTANT! this is needed for tooltip to work
          },
          yaxis: {
              min: -1.2,
              max: 1.2
          },
          colors: ["#009efb", "#55ce63"],
          grid: {
              color: "#AFAFAF",
              hoverable: true,
              borderWidth: 0,
              backgroundColor: '#FFF'
          },
          tooltip: true,
          tooltipOpts: {
              content: "'%s' of %x.1 is %y.4",
              shifts: {
                  x: -60,
                  y: 25
              }
          }
      };
      var plotObj = $.plot($("#flot-line-chart"), [{
          data: sin,
          label: "sin(x)",
      }, {
          data: cos,
          label: "cos(x)"
      }], options);
  } */

  function plotJobsGraph () {
    var options = {
      series: {
        pie: {
          show: true,
          innerRadius: 0.89,
          radius: 1
        }
      },
      grid: {
        hoverable: true
      },
      tooltip: true,
      tooltipOpts: {
        cssClass: 'flotTip',
        content: '%s: %p.0%',
        defaultTheme: false
      }
    }

    $.plot($('#flot-line-chart'), jobsNumbers, options)
    showJobsNumberChartData()
  }

  function showJobsNumberChartData () {
    for (let i = 0; i < jobsNumbers.length; i++) {
      let elem = document.createElement('div')
      elem.className = 'row chart_row'
      elem.innerHTML = '<div style = "background-color: ' + jobsNumbers[i].color + '" class="chart_titlecolor">		</div><div class="chart_title" class="text-align: center;">' + jobsNumbers[i].label + '</div><div class="chart_value">' + jobsNumbers[i].data + '%</div>'

      jobChartData.appendChild(elem)
    }
  }
})
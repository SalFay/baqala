@push('head')
	<link rel="stylesheet" href="{{asset('plugins/css/charts/apexcharts.css')}}"/>
@endpush

@push('footer')
	<script src="{{asset('plugins/js/charts/apexcharts.min.js')}}"></script>
	<script>
    var flatPicker = $('.flat-picker')
    chartColors = {
      column: {
        series1: '#826af9',
        series2: '#d2b0ff',
        bg: '#f8d3ff'
      },
      success: {
        shade_100: '#7eefc7',
        shade_200: '#06774f'
      },
      donut: {
        series1: '#ffe700',
        series2: '#00d4bd',
        series3: '#826bf8',
        series4: '#2b9bf4',
        series5: '#FFA1A1'
      },
      area: {
        series3: '#a4f8cd',
        series2: '#60f2ca',
        series1: '#2bdac7'
      }
    }

    // heat chart data generator
    function generateDataHeat (count, yrange) {
      var i = 0
      var series = []
      while (i < count) {
        var x = 'w' + (i + 1).toString()
        var y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min

        series.push({
          x: x,
          y: y
        })
        i++
      }
      return series
    }

    // Init flatpicker
    if (flatPicker.length) {
      var date = new Date()
      flatPicker.each(function () {
        $(this).flatpickr({
          mode: 'range',
          defaultDate: ['2019-05-01', '2019-05-10']
        })
      })
    }

	</script>
@endpush

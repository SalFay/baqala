@push('footer')
    <script>
      $(document).ready(function () {
          @if(\App\Helpers\Alert::hasAlerts('error'))
          AdminApp.error("{!! \App\Helpers\Alert::parseAlerts('error') !!}", 'Error!')
          @endif

          @if(\App\Helpers\Alert::hasAlerts('success'))
          AdminApp.success("{!! \App\Helpers\Alert::parseAlerts('success') !!}", 'Success!')
          @endif

          @if(\App\Helpers\Alert::hasAlerts('warning'))
          AdminApp.warning("{!! \App\Helpers\Alert::parseAlerts('warning') !!}", 'Warning!')
          @endif

          @if(\App\Helpers\Alert::hasAlerts('info'))
          AdminApp.info("{!! \App\Helpers\Alert::parseAlerts('info') !!}", 'Info!')
          @endif
      })
    </script>
@endpush

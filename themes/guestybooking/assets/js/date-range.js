jQuery(document).ready(function($) {
    $('#date-range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });

    $('#date-range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
    });

    $('#date-range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
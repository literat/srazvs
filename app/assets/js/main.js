require('eonasdan-bootstrap-datetimepicker')

$(function () {
    $('.datepicker').datetimepicker({
        format: 'DD. MM. YYYY',
        locale: moment().locale('cs'),
        showTodayButton: true,
        showClose: true,
    });
    $('.datepicker').datetimepicker().datetimepicker('maxDate', moment().format('DD. MM. YYYY'));
})

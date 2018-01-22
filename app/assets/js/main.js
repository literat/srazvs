const $ = require('jquery')
const moment = require('moment')
require('eonasdan-bootstrap-datetimepicker')
import LiveForm from 'live-form-validation'
console.log(LiveForm)
$(function () {
    $('.datepicker').datetimepicker({
        format: 'DD. MM. YYYY',
        locale: moment().locale('cs'),
        showTodayButton: true,
        showClose: true,
    });
    $('.datepicker').datetimepicker().datetimepicker('maxDate', moment().format('DD. MM. YYYY'));
})

LiveForm.setOptions({
    messageParentClass: 'form-group',
    messageErrorClass: 'help-block text-danger col-md-offset-3'
});

$('#id_submitbutton2').on('click', function () {
	customValidator();
});

$('#id_submitbutton').on('click', function () {
	customValidator();
});


function customValidator() {
	var form = getNewSubmissionForm();
	if(form != undefined){
		if(validForm()){
			form.submit();
		}
	}
}
function getNewSubmissionForm(){
	return $('#mform1');
}

function validForm() {
	if($('#id_assignsubmission_babelium_enabled').checked){
		//babelium submission
		var select = $('#id_assignsubmission_babelium_exerciseid');
		if(select !== undefined){
			return ( select.options.length > 0 );
		}
	}
	return true;
}
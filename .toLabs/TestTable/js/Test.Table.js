$(document).ready(function(){
	$("input.toggler").change(function(){
		var value	= $(this).attr("selected");
		console.log($(this));
		console.log($(this.form));
		$("input",$(this.form)).attr("selected","selected");
	});
});

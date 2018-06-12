function vault(selector, title, callback){
	var button = jQuery("<button></button>").attr({type: "button"});
	button.html(title+' laden');
	button.on("click", function(){
		callback();
	});
	var txt = jQuery('<p>An dieser Stelle wird ein externer Service verwendet. Bitte bestätigen Sie, dass Sie diesen Inhalt laden möchten.</p>');
	var div = jQuery('<div class="vault"></div>').append(txt).append(button);
	jQuery(selector).html(div);
}

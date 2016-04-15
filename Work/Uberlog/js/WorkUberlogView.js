var WorkUberlogView = {

	baseUri: './work/uberlog/',
	interval: null,
	seconds: 15,

	init: function(){
		return;
		this.interval = window.setInterval(function(){
			$.ajax({
				url: WorkUberlogView.baseUri+'ajaxUpdateIndex',
				method: 'post',
				data: {action: 'update', lastId: lastId},
				dataType: 'json',
				success: function(json){
					console.log(json);
					for(var i=0; i<json.length; i++){
						lastId = json[i].recordId;
						WorkUberlogView.renderRecord(json[i], "table tbody", 200);
					}
				}
			});
		}, this.seconds * 1000);
	},

	renderRecord: function(record, selector, speed){
		return;
		if(record.client.length){
			parts = record.client.split(/ /);
			title = parts.shift();
			record.client	= '<acronym title="'+parts.join(' ')+'">'+title+'</acronym>';
		}
		if(typeof(record.category) == "object")
			record.category	= record.category.title;
		if(typeof(record.client) == "object")
			record.client	= record.client.title;
		row = $("<tr></tr>").attr("id","record-"+record.recordId);
		row.append('<td>'+record.recordId+'</td>');
		row.append('<td>'+record.type+'</td>');
		row.append('<td>'+record.category+'</td>');
		row.append('<td>'+record.code+'</td>');
		row.append('<td>'+record.source+' '+record.line+'</td>');
		row.append('<td>'+record.message+'</td>');
		row.append('<td>'+record.client+'</td>');
		row.append('<td>'+record.timestamp+'</td>');
		row.append('<td><button type="button" onclick="WorkUberlogView.removeRecord('+record.recordId+');">X</button></td>');
		$(selector).prepend(row.hide());
		row.fadeIn(speed);
	},

	removeRecord: function(recordId, speed){
		var speed = typeof(speed) == 'undefined' ? 0 : parseInt(speed);
		$.ajax({
			url: this.baseUri+'remove/'+recordId,
			type: 'post',
			data: {},
			success: function(){
				$("#record-"+recordId).fadeOut(speed,function(){$(this).remove()});
			}
		});
	}
};

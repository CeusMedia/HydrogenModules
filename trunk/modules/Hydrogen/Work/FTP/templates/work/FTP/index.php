<?php


return '
<script>
FTP_Client = {
	index: function(path){
		$.ajax({
			url: "./FTP/ajaxIndex",
			data: {path: path},
			type: "POST",
			dataType: "json",
			success: function(data){
				var table = $("<table></table>").addClass("table table-condensed");
				table.append("<thead><tr><th>Name</th><th>Size</th><th>Permissions</th></tr></thead>");
				var tbody = $("<tbody></tbody>").appendTo(table);
				var entry, icon, row;
				Object.keys(data).forEach(function(key) {
					console.log(key, data[key]);
					entry = data[key];
					icon = $("<i></i>").attr("class", "icon-file");
					if(entry.isdir)
						icon = $("<i></i>").attr("class", "icon-folder-close");
					row = $("<tr></tr>");
					row.append($("<td></td>").append(icon).append(" "+entry.name));
					row.append($("<td></td>").append(entry.size));
					row.append($("<td></td>").append(entry.permissions));
					tbody.append(row);
				});
				$("#list").html(table);
			}
		})
	}
};
/*
$(document).ready(function(){
	FTP_Client.index();
});*/
</script>
<div></div>
<div id="actions" class="navbar">
	<div class="navbar-inner">
		<form class="navbar-form">
			<a href="./FTP/addFile?path='.$pathCurrent.'" class="btn btn-small btn-success"><i class="icon-file icon-white"></i> Datei hochladen</a>
			<a href="./FTP/addFolder?path='.$pathCurrent.'" class="btn btn-small btn-success"><i class="icon-folder-open icon-white"></i> Ordner anlegen</a>
			<div class="pull-right">
				<a href="./FTP?path='.$pathCurrent.'&refresh" class="btn btn-small"><i class="icon-refresh"></i> neu laden</a>
			</div>
		</form>
		<ul class="nav">
			<li>
			</li>
		</ul>
	</div>
</div>
<div id="position">'.$position.'</div>
<div id="list">'.$table.'</div>
<div>'.$time.'</div>
';
?>
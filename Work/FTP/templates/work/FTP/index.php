<?php
return '
<script>
FTP_Client = {
	init: function(){
	}
};
$(document).ready(function(){
	FTP_Client.init();
});
</script>
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

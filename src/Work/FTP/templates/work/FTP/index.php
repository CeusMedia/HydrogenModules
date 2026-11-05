<?php

/** @var \CeusMedia\HydrogenFramework\Environment\Web $env */

$words	= $env->getLanguage()->getWords( 'work/ftp' );						//  explicit, since controller path is upper case

$w	= (object) $words['index'];

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
			<a href="./work/FTP/addFile?path='.$pathCurrent.'" class="btn btn-small btn-success"><i class="icon-file icon-white"></i> '.$w->buttonUpload.'</a>
			<a href="./work/FTP/addFolder?path='.$pathCurrent.'" class="btn btn-small btn-success"><i class="icon-folder-open icon-white"></i> '.$w->buttonAddFolder.'</a>
			<div class="pull-right">
				<a href="./work/FTP?path='.$pathCurrent.'&refresh" class="btn btn-small"><i class="icon-refresh"></i> '.$w->buttonRefresh.'</a>
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
<div>'.$time.'</div>';

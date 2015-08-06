<?php
$w	= (object) $words['index.folder'];

$listImages		= $view->listImages( $path, 120, 80 );
if( !$listImages )
	$listImages		= '<div><em><small class="muted">'.$w->noEntries.'</small></em></div><br/>';

$linkEditFolder	= '';
if( $path != "." )
	$linkEditFolder	= '&nbsp;&nbsp;<a class="btn btn-mini" href="./manage/content/image/editFolder" title="ändern" alt="ändern"><i class="icon-pencil"></i></a>';

$labelFolder	= preg_replace( "/^\.\//", "", $path );

return '
<script>
$(document).ready(function(){
	$(".thumbs>li").bind("click", function(){
		var url = "./manage/content/image/editImage/"+$(this).data("image-hash");
		document.location.href = url;
	});
});
</script>
<div class="content-panel">
	<h3>'.sprintf( $w->heading, $labelFolder.$linkEditFolder ).'</h4>
	<div class="content-panel-inner">
		<div style="position: not-relative">
			'.$listImages.'
			<div style="clear: left"></div>
		</div>
		<div class="buttonbar">
			<a href="./manage/content/image/addImage" class="btn btn-small not-btn-info btn-success"><i class="icon-plus icon-white"></i>&nbsp;'.$w->buttonAddFile.'</a>
		</div>
	</div>
</div>';
?>

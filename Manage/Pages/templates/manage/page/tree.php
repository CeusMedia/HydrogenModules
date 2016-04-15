<?php

$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

$page		= isset( $page ) ? $page : NULL;

$listPages	= $view->renderTree( $tree,
$page
 );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<select class="span12" name="scope" id="input_scope" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
		<div class="buttonbar">
			<a href="./manage/page/add" class="btn btn-small btn-success">'.$iconAdd.' neue Seite</a>
		</div>
	</div>
</div>';

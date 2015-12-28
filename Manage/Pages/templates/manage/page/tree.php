<?php

$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

$listPages	= $this->renderTree( $tree, NULL );

return '
<div class="content-panel">
	<div class="content-panel-inner">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<a href="./manage/page/add" class="btn btn-mini btn-primary pull-right">'.$iconAdd.'</a>
			<select class="span10" name="scope" id="input_scope" class="span10" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
	</div>
</div>';

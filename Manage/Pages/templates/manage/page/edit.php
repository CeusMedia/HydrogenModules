<?php

$listPages	= $this->renderTree( $tree, $page );

$optScope	= array();
foreach( $words['scopes'] as $key => $value )
	$optScope[$key]	= $value;
$optScope	= UI_HTML_Elements::Options( $optScope, $scope );

$tabTemplates	= array(
	0	=> 'edit.settings.php',
	1	=> 'edit.preview.php',
	2	=> 'edit.content.php',
	3	=> 'edit.meta.php',
);

$tabs	= $view->renderTabs( $words['tabs'], $tabTemplates, $tab );

$iconAdd	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-plus icon-white' ) );

//  --  LAYOUT  --  //
return '
<div class="row-fluid">
	<div id="manage-page-tree" class="span3">
		<div>
			<label for="input_scope">Navigationstyp</label>
			<a href="./manage/page/add" class="btn btn-mini btn-primary pull-right">'.$iconAdd.'</a>
			<select class="span10" name="scope" id="input_scope" onchange="document.location.href=\'./manage/page/setScope/\'+this.value;">'.$optScope.'</select>
		</div>
		'.$listPages.'
<!--		<button type="button" onclick="document.location.href=\'./manage/page/add\';" class="btn btn-small btn-info"><i class="icon-plus icon-white"></i> neue Seite</button>-->
	</div>
	<div id="manage-page-main" class="span9">
		<div style="float: left; width: 100%">
			<form action="./manage/page/edit/'.$current.'/'.$version.'" method="post" class="cmFormChange-auto">
				'.$tabs.'
				<div class="buttonbar">
					<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
					<button type="reset" class="btn btn-small">zurücksetzen</button>
					<a href="./manage/page/copy/'.$current.'" class="btn btn-small">kopieren</a>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	PageEditor.init();
});
</script>
';
?>

<?php

$listPages	= $this->renderTree( $tree, $page );

$content	= "";
if( $current ){
	$listTabs		= array();
	foreach( array_values( $words['tabs'] ) as $nr => $label ){
		$attributes	= array( 'href' => '#tab'.++$nr, 'data-toggle' => 'tab' );
		$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
		$attributes	= array( 'id' => 'page-editor-tab-'.$nr, 'class' => $nr == $tab ? "active" : NULL );
		$listTabs[]	= UI_HTML_Tag::create( 'li', $link, $attributes );
	}
	$listTabs	= UI_HTML_Tag::create( 'ul', $listTabs, array( 'class' => "nav nav-tabs" ) );

	$content	= '
<form action="./manage/page/edit/'.$current.'" method="post">
	<div class="tabbable" id="tabs-page-editor"> <!-- Only required for left/right tabs -->
		'.$listTabs.'
		<div class="tab-content">
			<div class="tab-pane" id="tab1">
				'.$view->loadTemplateFile( 'manage/page/edit.settings.php' ).'
			</div>
			<div class="tab-pane" id="tab2">
				'.$view->loadTemplatefile( 'manage/page/edit.content.php' ).'
			</div>
			<div class="tab-pane" id="tab3">
				'.$view->loadTemplateFile( 'manage/page/edit.meta.php' ).'
			</div>
		</div>
		<div class="buttonbar">
			<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
			<button type="reset" class="btn btn-small">zurücksetzen</button>
		</div>
	</div>
</form>
';
}

//  --  LAYOUT  --  //
return '
<div>
	<div id="manage-page-tree">
		<h4>Seiten</h4>
		'.$listPages.'
<!--		<button type="button" onclick="document.location.href=\'./manage/page/add\';" class="btn btn-small btn-info"><i class="icon-plus icon-white"></i> neue Seite</button>-->
	</div>
	<div style="margin-left: 220px">
		<div style="float: left; width: 100%">
			'.$content.'
		</div>
	</div>
</div>
';
?>

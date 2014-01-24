<?php
$helper		= new View_Helper_TimePhraser( $env );
$table		= '<em><small class="muted">Keine.</small></em>';
if( $threads ){
	$rows	= array();
	foreach( $threads as $thread ){
		$url	= './info/forum/thread/'.$thread->threadId;
		$link	= UI_HTML_Tag::create( 'a', $thread->title, array( 'href' => $url ) );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $link, array() ),
			UI_HTML_Tag::create( 'td', 'vor '.$helper->convert( $thread->createdAt, TRUE ), array() ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells );
	}
	$heads	= UI_HTML_Elements::TableHeads( array(
		$words['index']['headTitle'],
		$words['index']['headFacts'],
	) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '75%', '25%' );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed' ) );
}

$panelAdd	= $view->loadTemplateFile( 'info/forum/index.add.php' );

return '
<h3>'.$words['index']['heading'].'</h3>
<div class="row-fluid">
	<div class="span9">
		'.$table.'
		<br/>
	</div>
	<div class="span3">
		'.$panelAdd.'
	</div>
</div>
';
?>
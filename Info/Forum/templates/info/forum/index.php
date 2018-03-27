<?php
extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/info/forum/' ) );
$textTop	= $textIndexTop	? $textIndexTop : '';
$textBottom	= $textIndexBottom	? $textIndexBottom : '';

$helper		= new View_Helper_TimePhraser( $env );
$iconUp		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-up' ) );
$iconDown	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-down' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
$iconEdit	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );

$rows	= array();
foreach( $topics as $topic ){
	$buttons	= array();
	if( in_array( 'rankTopic', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconUp, array(
			'href'	=> './info/forum/rankTopic/'.$topic->topicId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconDown, array(
			'href'	=> './info/forum/rankTopic/'.$topic->topicId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		) );
	}
	if( in_array( 'ajaxRenameTopic', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'button', $iconEdit, array(
			'onclick'	=> 'InfoForum.changeTopicName('.$topic->topicId.', \''.$topic->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonRename'],
		) );
	}
	if( in_array( 'removeTopic', $rights ) ){
		$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './info/forum/removeTopic/'.$topic->topicId,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove'],
		) );
	}
	$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
	$url		= './info/forum/topic/'.$topic->topicId;
	$link		= UI_HTML_Tag::create( 'a', $topic->title, array( 'href' => $url ) );
	$modifiedAt	= $helper->convert( $topic->createdAt, TRUE );
	$underline	= 'Themen: '.$topic->threads.' | Beiträge: '.$topic->posts.' | Letzter Beitrag: vor '.$modifiedAt;
	$label		= $link.'<br/><small class="muted">'.$underline.'</small>';
	$cells		= array(
		UI_HTML_Tag::create( 'td', $label, array( 'class' => 'topic-label') ),
		UI_HTML_Tag::create( 'td', $buttons ),
	);
	$rows[]	= UI_HTML_Tag::create( 'tr', $cells );
}
$heads	= UI_HTML_Elements::TableHeads( array(
	$words['index']['headTitle'],
	'',//$words['index']['headFacts'],
) );
$colgroup	= UI_HTML_Elements::ColumnGroup( '90%', '20%' );
$thead		= UI_HTML_Tag::create( 'thead', $heads );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed table-fixed' ) );
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/index.add.php' );

$iconHome	= new \CeusMedia\Bootstrap\Icon( 'home', TRUE );
$buttons	= array( new \CeusMedia\Bootstrap\Button( $iconHome, 'btn-inverse', NULL, TRUE ) );
$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
$position->setClass( 'position-bar' );

return $textTop.'
<!--<h3>'.$words['index']['heading'].'</h3>-->
<div>'.$position.'</div><br/>
<div class="row-fluid">
	<div class="span12">
		'.$panelList.'
		<br/>
	</div>
</div>
<div class="row-fluid">
	<div class="span8">
		'.$panelAdd.'
	</div>
</div>
'.$textBottom;
?>

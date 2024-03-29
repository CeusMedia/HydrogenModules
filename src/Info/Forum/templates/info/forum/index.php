<?php

use CeusMedia\Bootstrap\Button as BootstrapButton;
use CeusMedia\Bootstrap\Button\Group as BootstrapButtonGroup;
use CeusMedia\Bootstrap\Icon as BootstrapIcon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

extract( $view->populateTexts( ['index.top', 'index.bottom'], 'html/info/forum/' ) );
$textTop	= $textIndexTop	? $textIndexTop : '';
$textBottom	= $textIndexBottom	? $textIndexBottom : '';

$helper		= new View_Helper_TimePhraser( $env );
$iconUp		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-up'] );
$iconDown	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-down'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );
$iconEdit	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );

$rows	= [];
foreach( $topics as $topic ){
	$buttons	= [];
	if( in_array( 'rankTopic', $rights ) ){
		$buttons[]	= HtmlTag::create( 'a', $iconUp, [
			'href'	=> './info/forum/rankTopic/'.$topic->topicId,
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		] );
		$buttons[]	= HtmlTag::create( 'a', $iconDown, [
			'href'	=> './info/forum/rankTopic/'.$topic->topicId.'/down',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonUp'],
		] );
	}
	if( in_array( 'ajaxRenameTopic', $rights ) ){
		$buttons[]	= HtmlTag::create( 'button', $iconEdit, array(
			'onclick'	=> 'InfoForum.changeTopicName('.$topic->topicId.', \''.$topic->title.'\')',
			'class'	=> 'btn not-btn-small',
			'title'	=> $words['index']['buttonRename'],
		) );
	}
	if( in_array( 'removeTopic', $rights ) ){
		$buttons[]	= HtmlTag::create( 'a', $iconRemove, [
			'href'	=> './info/forum/removeTopic/'.$topic->topicId,
			'class'	=> 'btn not-btn-small btn-danger',
			'title'	=> $words['index']['buttonRemove'],
		] );
	}
	$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
	$url		= './info/forum/topic/'.$topic->topicId;
	$link		= HtmlTag::create( 'a', $topic->title, ['href' => $url] );
	$modifiedAt	= $helper->convert( $topic->createdAt, TRUE );
	$underline	= 'Themen: '.$topic->threads.' | Beiträge: '.$topic->posts.' | Letzter Beitrag: vor '.$modifiedAt;
	$label		= $link.'<br/><small class="muted">'.$underline.'</small>';
	$cells		= array(
		HtmlTag::create( 'td', $label, array( 'class' => 'topic-label') ),
		HtmlTag::create( 'td', $buttons ),
	);
	$rows[]	= HtmlTag::create( 'tr', $cells );
}
$heads	= HtmlElements::TableHeads( [
	$words['index']['headTitle'],
	'',//$words['index']['headFacts'],
] );
$colgroup	= HtmlElements::ColumnGroup( '90%', '20%' );
$thead		= HtmlTag::create( 'thead', $heads );
$tbody		= HtmlTag::create( 'tbody', $rows );
$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped not-table-condensed table-fixed'] );
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/index.add.php' );

$iconHome	= new BootstrapIcon( 'home', TRUE );
$buttons	= [new BootstrapButton( $iconHome, 'btn-inverse', NULL, TRUE )];
$position	= new BootstrapButtonGroup( $buttons );
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

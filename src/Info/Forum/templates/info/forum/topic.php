<?php

use CeusMedia\Bootstrap\Button as BootstrapButton;
use CeusMedia\Bootstrap\Button\Group as BootstrapButtonGroup;
use CeusMedia\Bootstrap\Button\Link as BootstrapLinkButton;
use CeusMedia\Bootstrap\Icon as BootstrapIcon;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var View_Info_Forum $view */

extract( $view->populateTexts( ['index.top', 'index.bottom', 'topic.top', 'topic.bottom'], 'html/info/forum/' ) );
$textTop	= $textTopicTop ?: $textIndexTop;
$textBottom	= $textTopicBottom ?: $textIndexBottom;

$helper		= new View_Helper_TimePhraser( $env );
$iconSticky	= HtmlTag::create( 'i', '', ['class' => 'icon-exclamation-sign not-icon-white'] );
$iconSticky	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] );

$iconRename	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconStar	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-thumb-tack'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$userCanStar	= $env->getAcl()->has( 'ajax/info/forum', 'starThread' );
$userCanEdit	= $env->getAcl()->has( 'ajax/info/forum', 'renameThread' );
$userCanRemove	= in_array( 'removeThread', $rights );
$userIsManager	= in_array( 'removeTopic', $rights );

$table		= '<em><small class="muted">Keine.</small></em>';
if( $threads ){
	$rows	= [];
	foreach( $threads as $thread ){
		$buttons		= [];
		$userIsAuthor	= $thread->authorId == $userId;
		$userCanChange	= $userIsManager || $userIsAuthor;
		if( $userCanEdit && $userCanChange ){
			$buttons[]	= HtmlTag::create( 'button', $iconRename, [
				'onclick'	=> 'InfoForum.changeThreadName('.$thread->threadId.', '.$thread->topicId.', \''.htmlentities( $thread->title, ENT_QUOTES, 'UTF-8' ).'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonRename'],
			] );
		}
		if( $userCanStar && $userCanChange ){
			$buttons[]	= HtmlTag::create( 'button', $iconStar, [
				'onclick'	=> 'InfoForum.changeThreadType('.$thread->threadId.', '.$thread->topicId.', \''.$thread->type.'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonStar'],
			] );
		}
		if( $userCanRemove && $userCanChange ){
			$buttons[]	= HtmlTag::create( 'button', $iconRemove, [
				'onclick'	=> 'if(confirm(\'Wirklich ?\')) document.location.href = \'./info/forum/removeThread/'.$thread->threadId.'\';',
				'class'	=> 'btn not-btn-small btn-danger',
				'title'	=> $words['topic']['buttonRemove']
			] );
		}

		$class		= '';
		$url		= './info/forum/thread/'.$thread->threadId;
		$link		= HtmlTag::create( 'a', $thread->title, ['href' => $url] );
		if( $thread->type ){
			$class	= 'type-important info';
			$link	= $iconSticky.' '.$link;
		}
		$modifiedAt	= $helper->convert( $thread->createdAt, TRUE );
		$underline	= 'Einträge: '.$thread->posts.', Latest: vor '.$modifiedAt;
		$label		= $link.'<br/><small class="muted">'.$underline.'</small>';
		$buttons	= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
		$cells		= [
			HtmlTag::create( 'td', $label, ['class' => 'thread-label autocut'] ),
			HtmlTag::create( 'td', $buttons ),
		];
		$rows[]	= HtmlTag::create( 'tr', $cells, ['class' => $class] );
	}
	$heads	= HtmlElements::TableHeads( [
		$words['topic']['headTitle'],
		$words['topic']['headActions'],
	] );
	$colgroup	= HtmlElements::ColumnGroup( '85%', '15%' );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped not-table-condensed'] );
}
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/topic.add.php' );


$iconHome	= new BootstrapIcon( 'home' );
$iconFolder	= new BootstrapIcon( 'folder-open', TRUE );
$url		= './info/forum/';
$buttons	= [
	new BootstrapLinkButton( $url, $iconHome ),
	new BootstrapButton( $topic->title, 'btn-inverse', $iconFolder, TRUE ),
];
$position	= new BootstrapButtonGroup( $buttons );
$position->setClass( 'position-bar' );

return $textTop.'
<!--<h3><a href="./info/forum"><span class="muted">'.$words['topic']['heading'].':</span></a> '.$topic->title.'</h3>-->
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

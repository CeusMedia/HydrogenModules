<?php
extract( $view->populateTexts( array( 'index.top', 'index.bottom', 'topic.top', 'topic.bottom' ), 'html/info/forum/' ) );
$textTop	= $textTopicTop	? $textTopicTop: $textIndexTop;
$textBottom	= $textTopicBottom ? $textTopicBottom : $textIndexBottom;

$helper		= new View_Helper_TimePhraser( $env );
$iconSticky	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-exclamation-sign not-icon-white' ) );
$iconSticky	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-star not-icon-white' ) );

$iconRename	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconStar	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-star' ) );
$iconRemove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$userCanStar	= in_array( 'ajaxStarThread', $rights );
$userCanEdit	= in_array( 'ajaxRenameThread', $rights );
$userCanRemove	= in_array( 'removeThread', $rights );
$userIsManager	= in_array( 'removeTopic', $rights );

$table		= '<em><small class="muted">Keine.</small></em>';
if( $threads ){
	$rows	= array();
	foreach( $threads as $thread ){
		$buttons		= array();
		$userIsAuthor	= $thread->authorId == $userId;
		$userCanChange	= $userIsManager || $userIsAuthor;
		if( $userCanEdit && $userCanChange ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconRename, array(
				'onclick'	=> 'InfoForum.changeThreadName('.$thread->threadId.', '.$thread->topicId.', \''.htmlentities( $thread->title, ENT_QUOTES, 'UTF-8' ).'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonRename'],
			) );
		}
		if( $userCanStar && $userCanChange ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconStar, array(
				'onclick'	=> 'InfoForum.changeThreadType('.$thread->threadId.', '.$thread->topicId.', \''.$thread->type.'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonStar'],
			) );
		}
		if( $userCanRemove && $userCanChange ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconRemove, array(
				'onclick'	=> 'if(confirm(\'Wirklich ?\')) document.location.href = \'./info/forum/removeThread/'.$thread->threadId.'\';',
				'class'	=> 'btn not-btn-small btn-danger',
				'title'	=> $words['topic']['buttonRemove']
			) );
		}

		$class		= '';
		$url		= './info/forum/thread/'.$thread->threadId;
		$link		= UI_HTML_Tag::create( 'a', $thread->title, array( 'href' => $url ) );
		if( $thread->type ){
			$class	= 'type-important info';
			$link	= $iconSticky.' '.$link;
		}
		$modifiedAt	= $helper->convert( $thread->createdAt, TRUE );
		$underline	= 'Einträge: '.$thread->posts.', Latest: vor '.$modifiedAt;
		$label		= $link.'<br/><small class="muted">'.$underline.'</small>';
		$buttons	= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
		$cells		= array(
			UI_HTML_Tag::create( 'td', $label, array( 'class' => 'thread-label autocut' ) ),
			UI_HTML_Tag::create( 'td', $buttons ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array( 'class' => $class ) );
	}
	$heads	= UI_HTML_Elements::TableHeads( array(
		$words['topic']['headTitle'],
		$words['topic']['headActions'],
	) );
	$colgroup	= UI_HTML_Elements::ColumnGroup( '85%', '15%' );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped not-table-condensed' ) );
}
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/topic.add.php' );


$iconHome	= new \CeusMedia\Bootstrap\Icon( 'home' );
$iconFolder	= new \CeusMedia\Bootstrap\Icon( 'folder-open', TRUE );
$url		= './info/forum/';
$buttons	= array(
	new \CeusMedia\Bootstrap\LinkButton( $url, $iconHome ),
	new \CeusMedia\Bootstrap\Button( $topic->title, 'btn-inverse', $iconFolder, TRUE ),
);
$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
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
?>

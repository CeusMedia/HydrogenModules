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



$table		= '<em><small class="muted">Keine.</small></em>';
if( $threads ){
	$rows	= array();
	foreach( $threads as $thread ){
		$buttons	= array();
		if( in_array( 'ajaxRenameThread', $rights ) ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconRename, array(
				'onclick'	=> 'InfoForum.changeThreadName('.$thread->threadId.', '.$thread->topicId.', \''.$thread->title.'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonRename'],
			) );
		}
		if( in_array( 'ajaxStarThread', $rights ) ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconStar, array(
				'onclick'	=> 'InfoForum.changeThreadType('.$thread->threadId.', '.$thread->topicId.', \''.$thread->type.'\')',
				'class'	=> 'btn not-btn-small',
				'title'	=> $words['topic']['buttonRename'],
			) );
		}
		if( in_array( 'removeThread', $rights ) ){
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
			UI_HTML_Tag::create( 'td', $label, array( 'class' => 'thread-label' ) ),
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

$panelAdd	= $view->loadTemplateFile( 'info/forum/topic.add.php' );

return $textTop.'
<h3><a href="./info/forum"><span class="muted">'.$words['topic']['heading'].':</span></a> '.$topic->title.'</h3>
<div class="row-fluid">
	<div class="span7">
		'.$table.'
		<br/>
	</div>
	<div class="span5">
		'.$panelAdd.'
	</div>
</div>
'.$textBottom;
?>
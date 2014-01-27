<?php
extract( $view->populateTexts( array( 'index.top', 'index.bottom', 'thread.top', 'thread.bottom' ), 'html/info/forum/' ) );
$textTop	= $textThreadTop	? $textThreadTop: $textIndexTop;
$textBottom	= $textThreadBottom ? $textThreadBottom : $textIndexBottom;

$helper			= new View_Helper_TimePhraser( $env );
$iconApprove	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconEdit		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-pencil' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );

$table	= '<em><small class="muted">Keine.</small></em>';
$userCanApprove	= in_array( 'approvePost', $rights );
$userCanEdit	= in_array( 'ajaxEditPost', $rights );
$userCanRemove	= in_array( 'removePost', $rights );

if( $posts ){
	$rows	= array();
	foreach( $posts as $post ){
		$buttons	= array();
		if( (int) $post->status === 0 ){
			if( $userCanApprove )
				$buttons[]	= UI_HTML_Tag::create( 'a', $iconApprove, array(
					'href'	=> './info/forum/approvePost/'.$post->postId,
					'class'	=> 'btn not-btn-small btn-success',
					'title'	=> $words['thread']['buttonApprove']
				) );
		}
		if( $userCanEdit && ( $post->authorId == $userId || $userCanRemove ) ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconEdit, array(
				'onclick'	=> 'InfoForum.preparePostEditor('.$post->postId.')',
				'class'		=> 'btn not-btn-small',
				'title'		=> $words['thread']['buttonEdit']
			) );
		}
		if( $userCanRemove ){
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
				'onclick'	=> 'if(confirm(\'Wirklich ?\')) document.location.href = \'./info/forum/removePost/'.$post->postId.'\';',
				'href'	=> './info/forum/removePost/'.$post->postId,
				'class'	=> 'btn not-btn-small btn-danger',
				'title'	=> $words['thread']['buttonRemove']
			) );
		}
		$user	= '-';
		if( $post->author ){
			$gravatar	= 'http://www.gravatar.com/avatar/'.md5( strtolower( trim( $post->author->email ) ) ).'?s=32&d=mm&r=g';
			$gravatar	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $gravatar, 'class' => 'avatar' ) );
			$user		= $gravatar.$post->author->username;
		}
		$buttons		= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group' ) );
		$cells	= array(
			UI_HTML_Tag::create( 'td', $user ),
			UI_HTML_Tag::create( 'td', nl2br( $post->content, TRUE ), array( 'class' => 'content' ) ),
			UI_HTML_Tag::create( 'td', $buttons ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array( 'id' => 'post-'.$post->postId ) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '25%', '60%', '15%' );
	$heads		= UI_HTML_Elements::TableHeads( array() );
	$thead		= UI_HTML_Tag::create( 'tbody', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}

$panelAdd	= $view->loadTemplateFile( 'info/forum/thread.add.php' );

return $textTop.'
<h3><a href="./info/forum"><span class="muted">'.$words['topic']['heading'].':</span></a> '.$topic->title.'</h3>
<h4><a href="./info/forum/topic/'.$topic->topicId.'"><span class="muted">'.$words['thread']['heading'].':</span></a> '.$thread->title.'</h4>
<div class="row-fluid">
	<div class="span8">
		<h4>Beiträge</h4>
		'.$table.'
		<br/>
	</div>
	<div class="span4">
		'.$panelAdd.'
	</div>
</div>
<style>
img.avatar {
	float: left;
	width: 32px;
	height: 32px;
	margin-right: 8px;
	border: 1px solid gray;
	box-shadow: 1px 1px 2px rgba(0,0,0,0.2);
	}
</style>
'.$textBottom;

?>
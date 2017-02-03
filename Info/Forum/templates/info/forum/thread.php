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
$userIsManager	= in_array( 'removeTopic', $rights );

$lastPostId		= 0;

if( $posts ){
	$rows	= array();
	foreach( $posts as $nr => $post ){
		$buttons		= array();
		$postIsLast		= count( $posts ) === $nr + 1;
		$userIsAuthor	= $post->authorId == $userId;
		$userCanChange	= $userIsManager || ( $userIsAuthor && $postIsLast );

		if( (int) $post->status === 0 ){
			if( $userCanApprove )
				$buttons[]	= UI_HTML_Tag::create( 'a', $iconApprove, array(
					'href'	=> './info/forum/approvePost/'.$post->postId,
					'class'	=> 'btn not-btn-small btn-success',
					'title'	=> $words['thread']['buttonApprove']
				) );
		}
		if( $userCanEdit && $userCanChange && !$post->type ){
			$buttons[]	= UI_HTML_Tag::create( 'button', $iconEdit, array(
				'onclick'	=> 'InfoForum.preparePostEditor('.$post->postId.')',
				'class'		=> 'btn not-btn-small',
				'title'		=> $words['thread']['buttonEdit']
			) );
		}
		if( $userCanRemove && $userCanChange ){
			$buttons[]	= UI_HTML_Tag::create( 'a', $iconRemove, array(
				'onclick'	=> 'if(!confirm(\'Wirklich ?\')) return false;',
				'href'		=> './info/forum/removePost/'.$post->postId,
				'class'		=> 'btn not-btn-small btn-danger',
				'title'		=> $words['thread']['buttonRemove']
			) );
		}
		$user	= '-';
		if( $post->author ){
			$gravatar	= 'http://www.gravatar.com/avatar/'.md5( strtolower( trim( $post->author->email ) ) ).'?s=32&d=mm&r=g';
			$gravatar	= UI_HTML_Tag::create( 'img', NULL, array( 'src' => $gravatar, 'class' => 'avatar' ) );
			$nrPosts	= UI_HTML_Tag::create( 'small', ' ('.$userPosts[$post->author->userId].')', array( 'class' => 'muted' ) );
			$datetime	= UI_HTML_Tag::create( 'small', date( "d.m.Y H:i", $post->createdAt ), array( 'class' => 'muted' ) );
			$username	= UI_HTML_Tag::create( 'div', $post->author->username.$nrPosts, array( 'class' => 'username' ) );
			$user		= $gravatar.$username.$datetime;
		}
		$buttons		= UI_HTML_Tag::create( 'div', $buttons, array( 'class' => 'btn-group pull-right' ) );
		$content		= nl2br( $post->content, TRUE );
		if( $post->type == 1 ){
			$parts		= explode( "\n", $post->content );
			$title		= $parts[1] ? Alg_Text_Trimmer::trim( $parts[1], 100 ) : '';
			$caption	= $title ? UI_HTML_Tag::create( 'figcaption', htmlentities( $parts[1], ENT_QUOTES, 'UTF-8') ) : '';
			$image		= UI_HTML_Tag::create( 'img', NULL, array(
				'src'	=> 'contents/forum/'.$parts[0],
				'title'	=> htmlentities( $title, ENT_QUOTES, 'UTF-8')
			) );
			$content	= UI_HTML_Tag::create( 'figure', $image.$caption );
		}
		if( $post->modifiedAt ){
			$modifiedAt		= sprintf( $words['thread']['modifiedAt'], date( "d.m.Y H:i", $post->createdAt ) );
			$content		.= UI_HTML_Tag::create( 'div', $modifiedAt, array( 'class' => 'modified muted' ) );
		}
		$cells	= array(
			UI_HTML_Tag::create( 'td', $user ),
			UI_HTML_Tag::create( 'td', $content, array( 'class' => 'content' ) ),
			UI_HTML_Tag::create( 'td', $buttons ),
		);
		$rows[]	= UI_HTML_Tag::create( 'tr', $cells, array(
			'id'	=> 'post-'.$post->postId,
			'class'	=> 'post-type-'.$post->type
		) );
		$lastPostId	= $post->postId;
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '20%', '65%', '15%' );
	$heads		= UI_HTML_Elements::TableHeads( array() );
	$thead		= UI_HTML_Tag::create( 'thead', $heads );
	$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
	$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
}
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/thread.add.php' );

$iconHome	= new \CeusMedia\Bootstrap\Icon( 'home' );
$iconFolder	= new \CeusMedia\Bootstrap\Icon( 'folder-open' );
$iconFile	= new \CeusMedia\Bootstrap\Icon( 'file', TRUE );
$url		= './info/forum/';
$buttons	= array(
	new \CeusMedia\Bootstrap\LinkButton( $url, $iconHome ),
	new \CeusMedia\Bootstrap\LinkButton( $url.'topic/'.$topic->topicId, $topic->title, NULL, $iconFolder ),
	new \CeusMedia\Bootstrap\Button( $thread->title, 'btn-inverse disabled', $iconFile, TRUE ),
);
$position	= new \CeusMedia\Bootstrap\ButtonGroup( $buttons );
$position->setClass( 'position-bar' );

return $textTop.'
<!--<h3><a href="./info/forum"><span class="muted">'.$words['topic']['heading'].':</span></a> '.$topic->title.'</h3>
<h4><a href="./info/forum/topic/'.$topic->topicId.'"><span class="muted">'.$words['thread']['heading'].':</span></a> '.$thread->title.'</h4>-->
<div>'.$position.'</div><br/>
<div class="row-fluid">
	<div class="span12">
		<h4>Beiträge</h4>
		'.$panelList.'
		<br/>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelAdd.'
	</div>
</div>
<style>
div.username {
	line-height: 1.2em;
	font-size: 1.1em;
	}
div.modified {
	margin-top: 0.5em;
	padding-top: 0.5em;
	padding-bottom: 0.25em;
	border-top: 1px solid #DDD;
	}
img.avatar {
	float: left;
	width: 32px;
	height: 32px;
	margin-right: 8px;
	border: 1px solid gray;
	box-shadow: 1px 1px 2px rgba(0,0,0,0.2);
	}
</style>
<script>
$(document).ready(function(){
	InfoForum.pollForUpdates('.$thread->threadId.', '.$lastPostId.');
});
</script>
'.$textBottom;

?>

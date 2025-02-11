<?php

use CeusMedia\Bootstrap\Button as BootstrapButton;
use CeusMedia\Bootstrap\Button\Group as BootstrapButtonGroup;
use CeusMedia\Bootstrap\Button\Link as BootstrapLinkButton;
use CeusMedia\Bootstrap\Icon as BootstrapIcon;
use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */

extract( $view->populateTexts( ['index.top', 'index.bottom', 'thread.top', 'thread.bottom'], 'html/info/forum/' ) );
$textTop	= $textThreadTop ?: $textIndexTop;
$textBottom	= $textThreadBottom ?: $textIndexBottom;

$helper			= new View_Helper_TimePhraser( $env );
$iconApprove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconEdit		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-pencil'] );
$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$table	= '<em><small class="muted">Keine.</small></em>';
$userCanApprove	= in_array( 'approvePost', $rights );
$userCanEdit	= $env->getAcl()->has( 'ajax/info/forum', 'editPost' );
$userCanRemove	= in_array( 'removePost', $rights );
$userIsManager	= in_array( 'removeTopic', $rights );

$lastPostId		= 0;

if( $posts ){
	$rows	= [];
	foreach( $posts as $nr => $post ){
		$buttons		= [];
		$postIsLast		= count( $posts ) === $nr + 1;
		$userIsAuthor	= $post->authorId == $userId;
		$userCanChange	= $userIsManager || ( $userIsAuthor && $postIsLast );

		if( (int) $post->status === 0 ){
			if( $userCanApprove )
				$buttons[]	= HtmlTag::create( 'a', $iconApprove, [
					'href'	=> './info/forum/approvePost/'.$post->postId,
					'class'	=> 'btn not-btn-small btn-success',
					'title'	=> $words['thread']['buttonApprove']
				] );
		}
		if( $userCanEdit && $userCanChange && !$post->type ){
			$buttons[]	= HtmlTag::create( 'button', $iconEdit, [
				'onclick'	=> 'InfoForum.preparePostEditor('.$post->postId.')',
				'class'		=> 'btn not-btn-small',
				'title'		=> $words['thread']['buttonEdit']
			] );
		}
		if( $userCanRemove && $userCanChange ){
			$buttons[]	= HtmlTag::create( 'a', $iconRemove, [
				'onclick'	=> 'if(!confirm(\'Wirklich ?\')) return false;',
				'href'		=> './info/forum/removePost/'.$post->postId,
				'class'		=> 'btn not-btn-small btn-danger',
				'title'		=> $words['thread']['buttonRemove']
			] );
		}
		$user	= '-';
		if( $post->author ){
			$gravatar	= 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $post->author->email ) ) ).'?s=32&d=mm&r=g';
			$gravatar	= HtmlTag::create( 'img', NULL, ['src' => $gravatar, 'class' => 'avatar'] );
			$nrPosts	= HtmlTag::create( 'small', ' ('.$userPosts[$post->author->userId].')', ['class' => 'muted'] );
			$datetime	= HtmlTag::create( 'small', date( "d.m.Y H:i", $post->createdAt ), ['class' => 'muted'] );
			$username	= HtmlTag::create( 'div', $post->author->username.$nrPosts, ['class' => 'username'] );
			$user		= $gravatar.$username.$datetime;
		}
		$buttons		= HtmlTag::create( 'div', $buttons, ['class' => 'btn-group pull-right'] );
		$content		= nl2br( $post->content, TRUE );
		if( $post->type == 1 ){
			$parts		= explode( "\n", $post->content );
			$title		= $parts[1] ? TextTrimmer::trim( $parts[1], 100 ) : '';
			$caption	= $title ? HtmlTag::create( 'figcaption', htmlentities( $parts[1], ENT_QUOTES, 'UTF-8') ) : '';
			$image		= HtmlTag::create( 'img', NULL, [
				'src'	=> 'contents/forum/'.$parts[0],
				'title'	=> htmlentities( $title, ENT_QUOTES, 'UTF-8')
			] );
			$content	= HtmlTag::create( 'figure', $image.$caption );
		}
		if( $post->modifiedAt ){
			$modifiedAt		= sprintf( $words['thread']['modifiedAt'], date( "d.m.Y H:i", $post->createdAt ) );
			$content		.= HtmlTag::create( 'div', $modifiedAt, ['class' => 'modified muted'] );
		}
		$cells	= [
			HtmlTag::create( 'td', $user ),
			HtmlTag::create( 'td', $content, ['class' => 'content'] ),
			HtmlTag::create( 'td', $buttons ),
		];
		$rows[]	= HtmlTag::create( 'tr', $cells, [
			'id'	=> 'post-'.$post->postId,
			'class'	=> 'post-type-'.$post->type
		] );
		$lastPostId	= $post->postId;
	}
	$colgroup	= HtmlElements::ColumnGroup( '20%', '65%', '15%' );
	$heads		= HtmlElements::TableHeads( [] );
	$thead		= HtmlTag::create( 'thead', $heads );
	$tbody		= HtmlTag::create( 'tbody', $rows );
	$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-striped'] );
}
$panelList	= '
<div class="content-panel">
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

$panelAdd	= $view->loadTemplateFile( 'info/forum/thread.add.php' );

$iconHome	= new BootstrapIcon( 'home' );
$iconFolder	= new BootstrapIcon( 'folder-open' );
$iconFile	= new BootstrapIcon( 'file', TRUE );
$url		= './info/forum/';
$buttons	= [
	new BootstrapLinkButton( $url, $iconHome ),
	new BootstrapLinkButton( $url.'topic/'.$topic->topicId, $topic->title, NULL, $iconFolder ),
	new BootstrapButton( $thread->title, 'btn-inverse disabled', $iconFile, TRUE ),
];
$position	= new BootstrapButtonGroup( $buttons );
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
	InfoForum.pollForUpdates('.$thread->threadId.', '.$lastPostId.')
});
</script>
'.$textBottom;

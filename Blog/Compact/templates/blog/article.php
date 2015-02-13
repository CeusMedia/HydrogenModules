<?php
$articleIndex	= array();
$articleList	= array();
foreach( $articles as $item ){
	$articleIndex[]	= $item->articleId;
	$articleList[$item->articleId]	= $item;
}
$index		= array_search( $articleId, $articleIndex );
$icon		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-comment fa-fw' ) ).'&nbsp;';
$linkNext	= '';
$linkPrev	= '';
if( isset( $articleIndex[$index-1] ) ){
	$next		= $articleList[$articleIndex[$index-1]];
	$label		= Alg_Text_Unicoder::convertToUnicode( $next->title );
	$url		= './blog/article/'.$next->articleId;
	if( $config->get( 'niceURLs' ) )
		$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $next );
	$linkNext	= UI_HTML_Elements::Link( $url, $icon.$label, 'not-icon-label not-link-blog' );
	$linkNext	= 'nächster: '.$linkNext;
}
if( isset( $articleIndex[$index+1] ) ){
	$previous	= $articleList[$articleIndex[$index+1]];
	$label		= Alg_Text_Unicoder::convertToUnicode( $previous->title );
	$url		= './blog/article/'.$previous->articleId;
	if( $config->get( 'niceURLs' ) )
		$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $previous );
	$linkPrev	= UI_HTML_Elements::Link( $url, $icon.$label, 'not-icon-label not-link-blog' );
	$linkPrev	= 'vorheriger: '.$linkPrev;
}

$listVersions	= '';
$list	= array();
if( $article->versions ){
	foreach( $article->versions as $nr => $articleVersion ){
		$label	= ++$nr;
		$url	= './blog/article/'.$article->articleId.'/'.$nr;
		if( $env->getConfig()->get( 'module.blog_compact.niceURLs' ) )
			$url	.= '/'.View_Helper_Blog::getArticleTitleUrlLabel( $articleVersion );
		$class	= 'icon-label link-blog version'.( $version == $nr ? ' current' : NULL );
		$list[]	= UI_HTML_Elements::Link( $url, $label, $class );
	}
	$label	= ( count( $article->versions ) + 1 );
	$class	= 'icon-label link-blog version latest'.( $article->version == $version  ? ' current' : NULL );
	$url	= './blog/article/'.$article->articleId;
	if( $env->getConfig()->get( 'module.blog_compact.niceURLs' ) )
		$url	.= '/'.View_Helper_Blog::getArticleTitleUrlLabel( $article );
	$list[]	= UI_HTML_Elements::Link( $url, $label, $class );
	$listVersions	= UI_HTML_Tag::create( 'span', join( '&nbsp;', $list ), array( 'class' => 'not-editor-list versions' ) );
}
else
	$listVersions	= '<b>1</b>';


if( $version > 0 && $version < $article->version ){
	$nr	= $version - 1;
	$article->title			= $article->versions[$nr]->title;
	$article->content		= $article->versions[$nr]->content;
	$article->createdAt		= $article->versions[$nr]->createdAt;
	$article->modifiedAt	= $article->versions[$nr]->modifiedAt;
	$article->version		= $version;
}

$title		= Alg_Text_Unicoder::convertToUnicode( $article->title );
$text		= View_Helper_ContentConverter::render( $env, $article->content );

$authorList	= View_Blog::renderAuthorList( $env, $authors, !TRUE );
$tagList	= View_Blog::renderTagList( $env, $tags );

$roleId		= $this->env->getSession()->get( 'roleId');
$canEdit	= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'edit' );
$url		= './blog/edit/'.$article->articleId;
$label		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/pencil.png', 'Eintrag bearbeiten' );
$linkEdit	= $canEdit ? UI_HTML_Elements::Link( $url, $label, 'link-edit button' ) : '';

$date	= 'unbekannt';
if( $article->createdAt ){
	$date	= date( 'd.m.Y', $article->createdAt );
	$time	= date( 'H:i', $article->createdAt );
}

return '
<style>
.icon-label.date {
	background-image: url(http://img.int1a.net/famfamfam/silk/date.png);
	}
.icon-label.time {
	background-image: url(http://img.int1a.net/famfamfam/silk/time.png);
	}
.link-blog.version {
	border: 1px solid transparent;
	padding: 0px 5px 0px 21px;
	}
.link-blog.version.current {
	font-weight: bold;
	color: #444;
	border: 1px solid rgba(0,0,0,0.5);
	}
</style>
<script>
$(window).keydown(function(event){
	if(event.ctrlKey && event.which == 69){										//  ctrl+e is pressed
		event.preventDefault();													//  prevent default browser behaviour
		document.location.href = $("a.link-edit.button").attr("href");			//  redirect to article edit mode
		return false;
	}
});
</script>
<div id="blog">
	<div class="blog-article">
		<div class="blog-article-navi top">
			<span style="float: right">
				'.$linkNext.'
			</span>
			<span style="float: left">
				'.$linkPrev.'
			</span>
			<div style="clear: both"></div>
		</div>
		<br/>
		<h3>'.$title.$linkEdit.'</h3>
		<div id="blog-article-info">
			<div class="column-right-33">
				<div>
					Zeitpunkt:
					<span class="icon-label date">'.$date.'</span> <span class="icon-label time">'.$time.'</span>
				</div>
				<div class="blog-article-version-list">
					Version(en):
					'.$listVersions.'
				</div>
			</div>
			<div class="column-left-66">
				<div class="blog-article-author-list">
					Autor(en):
					'.$authorList.'
				</div>
				<div class="blog-article-tag-list">
					Schlagwörter:
					'.$tagList.'
				</div>
			</div>
			<div class="column-clear"></div>
		</div>
		<div class="blog-article-content">
			'.$text.'
			<div style="clear: both"></div>
		</div>
		<br/>
		<div class="blog-article-navi bottom">
			<span style="float: right">
				'.$linkNext.'
			</span>
			<span style="float: left">
				'.$linkPrev.'
			</span>
			<div style="clear: both"></div>
		</div>
	</div>
</div>';
?>

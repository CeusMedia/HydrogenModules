<?php
$articleIndex	= array();
$articleList	= array();
foreach( $articles as $item ){
	$articleIndex[]	= $item->articleId;
	$articleList[$item->articleId]	= $item;
}
$index		= array_search( $articleId, $articleIndex );
$linkNext	= '';
$linkPrev	= '';
if( isset( $articleIndex[$index-1] ) ){
	$next		= $articleList[$articleIndex[$index-1]];
	$label		= Alg_Text_Unicoder::convertToUnicode( $next->title );
	$url		= './blog/article/'.$next->articleId;
	if( $config->get( 'niceURLs' ) )
		$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $next );
	$linkNext	= UI_HTML_Elements::Link( $url, $label.'&nbsp;&raquo;' );
}
if( isset( $articleIndex[$index+1] ) ){
	$previous	= $articleList[$articleIndex[$index+1]];
	$label		= Alg_Text_Unicoder::convertToUnicode( $previous->title );
	$url		= './blog/article/'.$previous->articleId;
	if( $config->get( 'niceURLs' ) )
		$url	.= '-'.View_Helper_Blog::getArticleTitleUrlLabel( $previous );
	$linkPrev	= UI_HTML_Elements::Link( $url, '&laquo;&nbsp;'.$label );
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
if( $article->createdAt )
	$date	= date( 'd.m.Y', $article->createdAt );

return '
<div id="blog">
	<div class="blog-article">
		<div class="article-navi">
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
		<div class="blog-article-author-list">
			Autor(en): '.$authorList.'
		</div>
		<div>
			Zeitpunkt: '.$date.'
		</div>
		<div class="blog-article-tag-list">
			Schlagwörter: '.$tagList.'
		</div>
		<div class="blog-article-content">
			'.$text.'
			<div style="clear: both"></div>
		</div>
		<br/>
		<div class="blog-article-navi">
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

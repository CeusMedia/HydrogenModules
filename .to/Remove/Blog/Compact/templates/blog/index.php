<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$roleId			= $this->env->getSession()->get( 'roleId');
$canAdd			= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
$url			= './blog/add';

$icon			= HtmlTag::create( 'b', '', ['class' => 'fa fa-plus fa-fw'] );
$linkAdd		= $canAdd ? '&nbsp;'.HtmlTag::create( 'a', $icon, [
	'href'		=> $url,
	'class'		=> 'btn btn-mini',
	'title'		=> 'neuer Eintrag',
] ) : '';

$articleList	= HtmlTag::create( 'em', 'Keine Artikel gefunden.' );
if( $articles )
	$articleList	= $this->renderArticleAbstractList( $articles, !FALSE, FALSE, !FALSE, FALSE );

#$heading		= HtmlElements::Heading( 'Artikel', 3 );
$heading		= HtmlTag::create( 'h3', 'Blog-Einträge'.$linkAdd );

$helper			= new View_Helper_Pagination();
$pageList		= $helper->render( './blog/index', $number, $limit, $page );

$topTags		= View_Helper_Blog::renderTopTags( $env, 10, 0, $states );
$flopTags		= View_Helper_Blog::renderFlopTags( $env, 5, 0, $states );
$listTopTags	= $topTags ? '<h4>Häufige Schlüsselwörter</h4>'.$topTags : '';
$listFlopTags	= $flopTags ? '<h4>Seltenste Schlüsselwörter</h4>'.$flopTags : '';

$iconPublic		= HtmlTag::create( 'b', '', ['class' => 'fa fa-check fa-fw'] ).'&nbsp;';
$iconWork		= HtmlTag::create( 'b', '', ['class' => 'fa fa-pencil fa-fw'] ).'&nbsp;';
$iconTrash		= HtmlTag::create( 'b', '', ['class' => 'fa fa-trash fa-fw'] ).'&nbsp;';

$listStates		= '
	<h4>Artikel-Zustände</h4>
	<label class="checkbox">
		<input type="checkbox" name="states" value="1" '.( in_array( 1, $states ) ? 'checked="checked"' : '').'>
		'.$iconPublic.$words['states']['1'].'
	</label>
	<label class="checkbox">
		<input type="checkbox" name="states" value="0" '.( in_array( 0, $states ) ? 'checked="checked"' : '').'>
		'.$iconWork.$words['states']['0'].'
	</label>
	<label class="checkbox">
		<input type="checkbox" name="states" value="-1" '.( in_array( -1, $states ) ? 'checked="checked"' : '').'>
		'.$iconTrash.$words['states']['-1'].'
	</label>
	<script>
$(document).ready(function(){
	Blog.initIndex();
});
	</script>
	';
if( !$isEditor )
	$listStates	= '';

$feedUrl	= View_Helper_Blog::getFeedUrl( $env );

return '
<div id="blog" class="row-fluid">
	<div class="not-column-left-70 span9">
		'.$heading.'
		'.$articleList.'
		'.$pageList.'
	</div>
	<div class="notcolumn-right-25 span3">
		<div style="float: right"><a href="'.$feedUrl.'" class="not-link-feed"><b class="fa fa-rss fa-fw"></b>&nbsp;RSS Feed</a></div>
		<br/>
		<br/>
		'.$listStates.'
		'.$listTopTags.'
		'.$listFlopTags.'
	</div>
<!--	<div class="column-clear"></div>-->
</div>';
?>

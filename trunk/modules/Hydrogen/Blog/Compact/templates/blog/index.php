<?php

$roleId			= $this->env->getSession()->get( 'roleId');
$canAdd			= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
$url			= './blog/add';
$label			= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/add.png', 'neuer Eintrag' );
$linkAdd		= $canAdd ? UI_HTML_Elements::Link( $url, $label, 'button link-add' ) : '';

$articleList	= UI_HTML_Tag::create( 'em', 'Keine Artikel gefunden.' );
if( $articles )
	$articleList	= $this->renderArticleAbstractList( $articles, FALSE, FALSE, FALSE );

#$heading		= UI_HTML_Elements::Heading( 'Artikel', 3 );
$heading		= UI_HTML_Tag::create( 'h3', 'Blog-Einträge'.$linkAdd );

$helper			= new View_Helper_Pagination();
$pageList		= $helper->render( './blog/index/', $number, $limit, $page );

$topTags		= View_Helper_Blog::renderTopTags( $env, 10, 0, $states );
$listTopTags	= $topTags ? '<h4>Häufige Schlüsselwörter</h4>'.$topTags : '';

$listStates		= '
	<h4>Artikel-Typen</h4>
	<label>
		<input type="checkbox" name="states" value="1" '.( in_array( 1, $states ) ? 'checked="checked"' : '').'>
		<span class="article-status status1">'.$words['states']['1'].'</span>
	</label><br/>
	<label>
		<input type="checkbox" name="states" value="0" '.( in_array( 0, $states ) ? 'checked="checked"' : '').'>
		<span class="article-status status0">'.$words['states']['0'].'</span>
	</label><br/>
	<label>
		<input type="checkbox" name="states" value="-1" '.( in_array( -1, $states ) ? 'checked="checked"' : '').'>
		<span class="article-status status-1">'.$words['states']['-1'].'</span>
	</label><br/>
	<br/>
		
	<script>
$("#blog input[name=states]").bind("change",function(){
	$(this).parent().children("span").addClass("loading");
	$.ajax({
		url: "./blog/setFilter",
		data: {
			name: "states",
			mode: $(this).is(":checked") ? "add" : "remove",
			value: $(this).attr("value")
		},
		type: "post",
		success: function(){
			document.location.href = "./blog";
		}
	});
});		
	</script>
	';
if( !$isEditor )
	$listStates	= '';

$feedUrl	= View_Helper_Blog::getFeedUrl( $env );

return '
<div id="blog">
	<div class="column-left-70">
		'.$heading.'
		'.$articleList.'
		'.$pageList.'
	</div>
	<div class="column-right-25">
		<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
		<br/>
		<br/>
		'.$listStates.'
		'.$listTopTags.'
	</div>
	<div class="column-clear"></div>
</div>';
?>

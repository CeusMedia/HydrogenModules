<?php

$roleId		= $this->env->getSession()->get( 'roleId');
$canAdd		= $roleId && $this->env->getAcl()->hasRight( $roleId, 'blog', 'add' );
$url		= './blog/add';
$label		= UI_HTML_Elements::Image( 'http://img.int1a.net/famfamfam/silk/add.png', 'neuer Eintrag' );
$linkAdd	= $canAdd ? UI_HTML_Elements::Link( $url, $label, 'button link-add' ) : '';

$articleList	= $this->renderArticleAbstractList( $articles, FALSE, FALSE, FALSE );
#$heading	= UI_HTML_Elements::Heading( 'Artikel', 3 );
$heading	= UI_HTML_Tag::create( 'h3', 'Blog-Einträge'.$linkAdd );

$helper		= new View_Helper_Pagination();
$pageList	= $helper->render( './blog/index/', $number, $limit, $page );


$list	= array();
foreach( $topTags as $relation ){
	$url	= './blog/tag/'.urlencode( urlencode( $relation->title ) );
	$nr		= UI_HTML_Tag::create( 'span', $relation->nr, array( 'class' => 'number-indicator' ) );
	$link	= UI_HTML_Tag::create( 'a', $relation->title, array( 'href' => $url, 'class' => 'link-tag' ) );
	$list[]	= UI_HTML_Tag::create( 'li', $nr.$link );
} 
$listTopTags	= '<h4>Häufige Schlüsselwörter</h4><ul class="top-tags">'.join( $list ).'</ul>';

$filters		= '
	<div style="float: right; top: 0px; right: 0px;">
		<label><input type="checkbox" name="states" value="0" '.( in_array( 0, $states ) ? 'checked="checked"' : '').'>versteckte</label>
		<label><input type="checkbox" name="states" value="1" '.( in_array( 1, $states ) ? 'checked="checked"' : '').'>öffentliche</label>
	</div>
	<script>
$("#blog input[name=states]").bind("change",function(){
//	console.log($(this).is(":checked") ? "add" : "remove");
	$.ajax({
		url: "./blog/setFilter",
		data: {
			name: "states",
			mode: $(this).is(":checked") ? "add" : "remove",
			value: $(this).attr("value")
		},
		type: "post",
		success: function(){
			document.location.reload();
		}
	});
});		
	</script>
	';
if( !$isEditor )
	$filters	= '';

$feedUrl	= View_Helper_Blog::getFeedUrl( $env );

return '
<div id="blog">
	<div class="column-left-70">
		'.$filters.'
		'.$heading.'
		'.$articleList.'
		'.$pageList.'
	</div>
	<div class="column-right-25">
		<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
		<br/>
		<br/>
		'.$listTopTags.'
	</div>
	<div class="column-clear"></div>
</div>';
?>

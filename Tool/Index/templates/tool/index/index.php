<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$tools	= [];
foreach( $list as $item ){
	$description	= isset( $labels[$item]['description'] ) ? "<br/>".$labels[$item]['description'] : "";
	$tags			= "";
	if( isset( $labels[$item]['tags'] ) ){
		$tagList	= explode( ",", $labels[$item]['tags'] );
		foreach( $tagList as $id => $tag )
			$tagList[$id]	= '<span class="tag">'.$tag.'</span>';
		$tags	= implode( ", ", $tagList );
		$tags	= '<br/><span class="tags">Tags: '.$tags.'</span>';
	}
	$link	= HtmlTag::create( "a", $item, ['href' => "./tools/".$item."/"] );
	$tools[$item]	= HtmlTag::create( "li", $link.$description.$tags );
}


$list	= HtmlTag::create( "ul", $tools );

return '
	<script src="javascripts/tool.index.js"></script>
	<link rel="stylesheet" href="themes/custom/css/site.tool.index.css"/>
	<div id="layout-index">
		<div id="tool-index-filter">
		  <label for="query">Filter</label><input type="text" name="query" id="query"></input>
		  <div id="clearer"></div>
		</div>
	    <h2>Tool Index</h2>
		'.$list.'
	</div>
';
?>

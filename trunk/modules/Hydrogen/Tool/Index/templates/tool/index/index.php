<?php
$list	= UI_HTML_Tag::create( "ul", implode( "\n", $list ) );

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

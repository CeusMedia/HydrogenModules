<?php

$data	= array( 0.2, 0.5, 0.8, 1 );
$graph	= View_EvolutionIndicator::render( $data, array( 'width' => 50, 'height' => 10 ) );


$l	= new View_RegionIndicator( 20 );
$l->addRegion( 1, 'green', '<h3>Test1</h3>'.date( 'c' ) );
$l->addRegion( 1, '#7FFF00', '<h4>Test 2</h4>'.time() );
$l->addRegion( 1, 'yellow', 'Test 3' );
$l->addRegion( 1, 'orange', 'Test 4' );
$l->addRegion( 1, 'red', 'Test 5' );
$g	= $l->render( 50, 50 );

return '
<h3>Evolution Graph</h3>
'.$graph.'
<br/>
<br/>
<script>
$(document).ready(function(){
	$(".region-bar").bind("click",function(){
		var bar = $(this);
		var wasSelected = bar.hasClass("selected");
		bar.parent().find(".region-bar.selected").removeClass("selected");
		if(!wasSelected)
			bar.addClass("selected");
	});
});
</script>

<style>
.region-content-pin {
	position: absolute;
	left: 4px;
	top: 5px;
	width: 7px;
	height: 13px;
	background-image: url(images/pin.png);
	background-repeat: no-repeat;
	}
.region-container {
	position: relative;
	width: 50px;
	border: 2px solid white;
	z-index: 1;
	box-shadow: 2px 2px 6px gray;
	}
.region-container .region-bar {
	position: absolute;
	left: 0px;
	width: 100%;
	z-index: 2;
	}
.region-container .region-bar:hover,
.region-container .region-bar.selected {
	margin-top: -2px;
	margin-left: -2px;
	border: 2px solid black;
	z-index: 3;
	box-shadow: 1px 1px 3px gray;
	}
.region-container .region-bar:hover {
	z-index: 4;
	}
.region-container .region-bar.selected {
	margin-top: -1px;
	margin-left: -1px;
	border: 1px solid black;
	}
.region-container .region-bar .region-content {
	display: none;
	position: absolute;
	width: 300px;
	}
.region-container .region-bar:hover .region-content,
.region-container .region-bar.selected .region-content {
	display: block;
	}
.region-container .region-bar .region-content-inner {
	border: 1px solid gray;
	background-color: rgba(255,255,255,1);
	border-radius: 4px;
	box-shadow: 1px 1px 3px #CCC;
	margin-left: 10px;
	margin-top: -10px;
	padding: 4px 10px;
	min-height: 35px;
	}

</style>
<h3>Ladder Graph</h3>
'.$g.'
';

?>
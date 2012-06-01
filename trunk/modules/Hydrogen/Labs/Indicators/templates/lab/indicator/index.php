<?php

$data	= array( 0.15, 0.5, 0.75, 1 );
$graph	= View_EvolutionIndicator::render( $data, array( 'width' => 150, 'height' => 10 ) );


$list	= array();
$number	= rand( 5, 1000 );
for( $i=0; $i<$number; $i++ )
	$list[]	= round( rand( 25, 100 ) / 100, 2 );
$graph2	= View_EvolutionIndicator::render( $list, array( 'width' => 150, 'height' => 10 ) );



$l	= new View_RegionIndicator( 20 );
$l->addRegion( 1, 'green', '<h3>Test1</h3>'.date( 'c' ) );
$l->addRegion( 1, '#7FFF00', '<h4>Test 2</h4>'.time() );
$l->addRegion( 1, 'yellow', 'Test 3' );
$l->addRegion( 1, 'orange', 'Test 4' );
$l->addRegion( 1, 'red', 'Test 5' );
$g	= $l->render( 50, 150 );

return '
<h3>Evolution Graph</h3>
An evolution graph is a two dimensional indicator. It shows several results (of whatever) as a line of colors.<br/>

The aim is to be able to see the history 
<br/>
<h4>Example</h4>
In this example we got 4 values. Imagine these values to be results of tests or something like that.<br/>
Each test result value is a number between 0 and 1, representing the floating point number if a percentage value, like 75%.
While 75% is represented as 0.75, other values are also added to a list (<cite>$data</cite>).<br/>
<br/>
<xmp class="php">$data	= array( 0.15, 0.5, 0.75, 1 );
$conf	= array( "width" => 150, "height" => 10 );
$graph	= View_EvolutionIndicator::render( $data, $conf );
</xmp>
Together with some output settings (<cite>$conf</cite>) the list of values is rendered by the <cite>Evolution Indicator View</cite>, which will return HTML markup.<br/>
Now here is the example indicator:<br/>
<div>'.$graph.'</div><br/>
So this indicator shows that the test results were bad in the beginning (red, orange), got better (yellow) and finally are ok.<br/>
<br/>
<h4>Demo: random values</h4>
The second example indicator shows a randomly generated list of values.<br/>
<br/>
<div>'.$graph2.'</div><br/>
The indicator can diplay as much values as its width in pixels (150 pixels in this example).<br/>
If more values are given, class <cite>Math_Extrapolation</cite> is applied to shorten the value list.<br/>
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
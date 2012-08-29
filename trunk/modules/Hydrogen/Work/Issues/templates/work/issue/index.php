<?php

$layout	= "control";				//  layout: control|top

$list	= $numberFilters ? 'list' : 'graphs';
$list	= require_once 'templates/work/issue/index.'.$list.'.php';

if( $layout == "control" ){
	$filter	= require 'templates/work/issue/index.filter.control.php';
	$layout	= HTML::DivClass( 'column-left-20', $filter ).HTML::DivClass( 'column-right-80', $list );
}
else{
	$filter	= require_once 'templates/work/issue/index.filter.php';
	$layout	= $filter.$list;
}
return '<div>'.$layout.'<div class="column-clear"></div></div>';
?>

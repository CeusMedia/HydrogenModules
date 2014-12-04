<?php

$layout	= "control";				//  layout: control|top

$list	= 1 || $numberFilters ? 'list' : 'graphs';
$list	= require_once 'templates/work/issue/index.'.$list.'.php';

if( $layout == "control" ){
	$filter	= require 'templates/work/issue/index.filter.control.php';
	$layout	= '<div class="row-fluid"><div class="span3">'.$filter.'</div><div class="span9">'.$list.'</div></div>';
}
else{
	$filter	= require_once 'templates/work/issue/index.filter.php';
	$layout	= $filter.$list;
}
return '<div>'.$layout.'<div class="column-clear"></div></div>';
?>

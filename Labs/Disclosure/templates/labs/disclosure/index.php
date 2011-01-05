<?php
$script	= '
$(document).ready(function(){
	$("#controllers").cmLadder();
});
';

$html	= '
<h2>Labor</h2>
';

$ext	= 'php5';

$classes	= array();
$path		= 'classes/Controller/';
$path		= realpath( $path );
$index		= new File_RecursiveRegexFilter( $path, '/^[A-Z][A-Za-z0-9]+\.'.$ext.'$/U' );
foreach( $index as $entry ){
	$fileName	= preg_replace( '@^'.$path.'/(.+)\.'.$ext.'$@', '\\1', $entry->getPathname() );
	$className	= 'Controller_'.str_replace( '/', '_', $fileName );
	$classes[$className]	= new ReflectionClass( $className );
}
ksort( $classes );

$ladder	= new UI_HTML_Ladder( 'controllers' );
foreach( $classes as $className => $classReflection ){
	$methods	= array();
	foreach( $classReflection->getMethods( ReflectionMethod::IS_PUBLIC ) as $methodReflection ){
		$parameters	= array();
		foreach( $methodReflection->getParameters() as $key => $parameterReflection )
			$parameters[]	= $parameterReflection->name;
		$parameters	= ' <small class="parameters">('.join( ', ', $parameters ).')</small>';
		$methodName	= '<span class="method method-name">'.$methodReflection->name.'</span>';
		if( $methodReflection->class === $className )
		{
			$label	= '<b>'.$methodName.'</b>'.$parameters;
		}
		else
		{
			$type	= '&nbsp;<small>from '.$methodReflection->class.'</small>';
			$label	= '<small>'.$methodName.$parameters.$type.'</small>';
		}
		$label	= UI_HTML_Elements::ListItem( $label );
		$methods[$methodReflection->name]	= $label;
	}
	ksort( $methods );
	$methods	= '<b>Actions</b>'.UI_HTML_Elements::unorderedList( $methods );
	$ladder->addStep( preg_replace( '/^Controller_/', '', $className ), $methods );
}
$html	.= '<h3>Controllers & Actions</h3>'.$ladder->buildHtml();

$this->env->page->js->addUrl( 'http://js.ceusmedia.de/jquery/cmLadder/0.2.js' );
$this->env->page->js->addScript( $script );
$this->env->page->css->addUrl( $config->get( 'path.themes' ).'custom/css/all/site.labs.info.css' );
$this->env->page->css->addUrl( 'http://js.ceusmedia.de/jquery/cmLadder/0.2.css' );
return $html;
?>
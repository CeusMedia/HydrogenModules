<?php

$script	= '
$(document).ready(function(){
//	$("#tabs-disclosure").tabs();
	$("#controllers").cmLadder();
});
';

$ext		= 'php5';
$path		= 'classes/Controller/';
$path		= realpath( $path );
$regexClass	= '/^[A-Z][A-Za-z0-9]+\.'.$ext.'$/U';

$classes	= array();
$index		= new FS_File_RecursiveRegexFilter( $path, $regexClass );
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

$this->env->page->js->addScript( $script );

return '
<h2>Labor</h2>
<h3>Controllers & Actions</h3>
'.$ladder->buildHtml();
?>

<?php
function ButtonLink( $url, $label, $class = NULL, $confirm = NULL, $disabled = NULL ){
	$label		= UI_HTML_Tag::create( 'span', $label );
	$attributes	= array( 'href' => $url, 'class' => $class );
	$tagName	= 'a';
	if( $disabled ){
		if( is_string( $disabled ) )
			$attributes['alt']	= $attributes['title']	= $disabled;
		$attributes['class']	.= ' disabled';
		$attributes['disabled']	= 'disabled';
		unset( $attributes['href'] );
	}
	else if( $confirm )
		$attributes['onclick']	= "return confirm('".$confirm."');";
	return UI_HTML_Tag::create( $tagName, $label, $attributes );
}

$types	= array(
	'add',
	'edit',
	'save',
	'delete',
	'remove',
	'reset',
	'cancel',

	'activate',
	'deactivate',
	'lock',
	'unlock',
	'up',
	'down',
	'open',
	'close',

	'filter',
	'search',
	'list',
	'info',
	'exit',
	'run',
);
$url	= './lab?t='.time();
$r		= array();
foreach( $types as $nr => $type ){
	$class	= 'button '.$type;
	$a	= ButtonLink( $url, $type, $class.' icon', 'Really?' );
	$b	= ButtonLink( $url, $type, $class.' icon', 'Really?', TRUE );
	$c	= UI_HTML_Elements::LinkButton( $url, $type, $class, 'Really?' );
	$d	= UI_HTML_Elements::LinkButton( $url, $type, $class, 'Really?', TRUE );
	$r[]= '<tr id="type-'.$nr.'" style="clear: left"><td>'.$a.'</td><td>'.$b.'</td><td>'.$c.'</td><td>'.$d.'</td></tr>';
}
return '<h3>Buttons</h3><table style="width: 300px">'.join( $r ).'</table>';
?>

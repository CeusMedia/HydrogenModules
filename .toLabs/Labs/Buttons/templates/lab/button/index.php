<?php

function IconButtonLink( $url, $title, $class = NULL, $confirm = NULL, $disabled = NULL ){
	$label		= UI_HTML_Tag::create( 'span', '&nbsp;' );
	$attributes	= array( 'href' => $url, 'class' => $class, 'title' => $title );
	if( $disabled ){
		if( is_string( $disabled ) )
			$attributes['alt']	= $attributes['title']	= $disabled;
		$attributes['class']	.= ' disabled';
		$attributes['disabled']	= 'disabled';
		unset( $attributes['href'] );
	}
	else if( $confirm )
		$attributes['onclick']	= "return confirm('".$confirm."');";
	return UI_HTML_Tag::create( 'a', $label, $attributes );
}

function ButtonLink( $url, $label, $class = NULL, $confirm = NULL, $disabled = NULL ){
	$label		= UI_HTML_Tag::create( 'span', $label );
	$attributes	= array( 'href' => $url, 'class' => $class );
	if( $disabled ){
		if( is_string( $disabled ) )
			$attributes['alt']	= $attributes['title']	= $disabled;
		$attributes['class']	.= ' disabled';
		$attributes['disabled']	= 'disabled';
		unset( $attributes['href'] );
	}
	else if( $confirm )
		$attributes['onclick']	= "return confirm('".$confirm."');";
	return UI_HTML_Tag::create( 'a', $label, $attributes );
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
	$class	= 'button icon '.$type;
	$a	= ButtonLink( $url, $type, $class, 'Really?' );
	$b	= ButtonLink( $url, $type, $class, 'Really?', TRUE );
	$c	= UI_HTML_Elements::LinkButton( $url, $type, $class, 'Really?' );
	$d	= UI_HTML_Elements::LinkButton( $url, $type, $class, 'Really?', TRUE );
	$e	= IconButtonLink( $url, $type, $class.' icon-only', 'Really?' );
	$f	= IconButtonLink( $url, $type, $class.' icon-only', 'Really?', TRUE );
	$g	= IconButtonLink( $url, $type, $class.' icon-only tiny', 'Really?' );
	$h	= IconButtonLink( $url, $type, $class.' icon-only tiny', 'Really?', TRUE );
	$r[]= '<tr id="type-'.$nr.'" style="clear: left"><td>'.$a.'</td><td>'.$b.'</td><td>'.$c.'</td><td>'.$d.'</td><td>'.$e.' '.$f.'</td><td>'.$g.' '.$h.'</td></tr>';
}
$optStyle	= array();
foreach( $styles as $item )
	$optStyle[$item]	= $item;
$optStyle	= UI_HTML_Elements::Options( $optStyle, $style );

return '<h3>Buttons</h3>
<div class="column-left-20">
	<fieldset>
		<legend>Styles</legend>
		<form action="./lab/button" method="get">
			<ul class="input">
				<li>
					<label for="input_style">&nbsp;&nbsp;Style File</label><br/>
					<select name="style" id="input_style" class="max" onchange="this.form.submit()">'.$optStyle.'</select>
				</li>
			</ul>
		</form>
	</fieldset>
</div>
<div class="column-left-80">
	<fieldset>
		<legend>Buttons</legend>
		<table style="" id="buttons">
			<colgroup>
				<col width="15%"/>
				<col width="15%"/>
				<col width="15%"/>
				<col width="15%"/>
				<col width="15%"/>
				<col width="15%"/>
			<colgroup>
			<tr>
				<th>Link</th>
				<th>Link disabled</th>
				<th>Button</th>
				<th>Button disabled</th>
				<th>Icon / disabled</th>
				<th>Tiny / disabled</th>
			</tr>
			<tbody>
				'.join( $r ).'
			</tbody>
		</table>
	</fieldset>
</div>
';
?>

<?php
/*
$list	= array();
foreach( $config as $key => $value ){
	$list[]	= UI_HTML_Tag::create( 'li', '<b>'.$key.': </b>'.$value );
}
$list	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'not-unstyled' ) );
return $words['index']['heading'].'
'.$list.'
';
*/

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock', 'title' => 'protected' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock', 'title' => 'unprotected' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user', 'title' => 'configurable by user' ) );

$list	= array();
foreach( $config as $moduleId => $module ){
	if( !$module->config )
		continue;
	$rows	= array();
	foreach( $module->config as $item ){
		$isNumeric		= in_array( $item->type, array( "integer", "float" ) ) || preg_match( "/^[0-9\.]+$/", $item->value );
		if( $item->values ){
			$values		= array_combine( $item->values, $item->values );
			$options	= UI_HTML_Elements::Options( $values, $item->value );
			$class		= $isNumeric ? "span3" : "span12";
			$input		= new UI_HTML_Tag( 'select', $options, array(
				'name'	=> $moduleId.'|'.$item->key,
				'class'	=> $class,
			) );
		}
		else if( $item->type === "boolean" ){
			$inputYes	= UI_HTML_Tag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'yes',
				'checked'	=> !!$item->value ? 'checked' : NULL,
			) ).'&nbsp;yes';
			$inputNo	= UI_HTML_Tag::create( 'input', NULL, array(
				'name'		=> $moduleId.'|'.$item->key,
				'type'		=> 'radio',
				'value'		=> 'no',
				'checked'	=> !$item->value ? 'checked' : NULL,
			) ).'&nbsp;no';
			$inputYes		= UI_HTML_Tag::create( 'label', $inputYes, array( 'class' => 'checkbox inline' ) );
			$inputNo		= UI_HTML_Tag::create( 'label', $inputNo, array( 'class' => 'checkbox inline' ) );
			$input			= $inputYes.$inputNo;

		}
		else{
			if( preg_match( "/,/", $item->value ) ){
				$value		= str_replace( ",", "\n", htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ) );
				$input		= new UI_HTML_Tag( 'textarea', $value, array(
					'name'			=> $moduleId.'|'.$item->key,
					'class'			=> 'span6',
					'rows'			=> count( explode( ",", $item->value ) ),
				) );
			}
			else{
				$class		= $isNumeric ? "span3" : "span12";
				$input		= new UI_HTML_Tag( 'input', NULL, array(
					'type'			=> 'text',
					'name'			=> $moduleId.'|'.$item->key,
					'class'			=> $class,
					'value'			=> htmlentities( $item->value, ENT_QUOTES, 'UTF-8' ),
					'placeholder'	=> $item->title,
				) );
			}
		}

		$protection	= $iconUnlock;
		if( $item->protected === "user" )
			$protection	= $iconUser;
		if( $item->protected === "yes" )
			$protection	= $iconLock;

		$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;
		$key	= $item->title ? '<abbr title="'.$item->title.'">'.$key.'</abbr>' : $key;
		$type	= '<small class="muted">'.$item->type.'</small>';
		$rows[$moduleId.'|'.$item->key]	= new UI_HTML_Tag( 'tr', array(
			new UI_HTML_Tag( 'td', $protection ),
			new UI_HTML_Tag( 'td', $key, array( 'class' => 'autocut' ) ),
			new UI_HTML_Tag( 'td', $type ),
			new UI_HTML_Tag( 'td', $input ),
		) );
		ksort( $rows );
	}
	$buttonRestore	= '';
	if( isset( $versions[$moduleId] ) && $versions[$moduleId] ){
		$buttonRestore	= UI_HTML_Tag::create( 'a', 'restore', array(
			'href'	=> './admin/config/restore/'.$moduleId,
			'class'	=> 'btn btn-inverse btn-mini'
		) );
	}
	$heading	=
	$cols	= UI_HTML_Elements::ColumnGroup( "2.5%", "37%", "7.5%", "53%" );
	$tbody	= new UI_HTML_Tag( 'tbody', $rows );
	$table	= new UI_HTML_Tag( 'table', $cols.$tbody, array( 'class' => 'table table-condensed table-striped', 'style' => 'table-layout: fixed' ) );
	$list[]	= new UI_HTML_Tag( 'h4', $module->title.'&nbsp;'.$buttonRestore ).$table;
}

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/' ) );

return $textTop.'<h3>'.$words['index']['heading'].'</h3>
<form action="./admin/config" method="post">
	<div class="row-fluid">
		<div class="span12">
			'.join( $list ).'
		</div>
	</div>
	<div class="buttonbar">
		<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> save</button>
	</div>
</form>
<style>
body.moduleAdminConfig table input,
body.moduleAdminConfig table select {
	margin: 0px;
	}
body.moduleAdminConfig table input,
body.moduleAdminConfig table select {
	}
</style>
'.$textBottom;

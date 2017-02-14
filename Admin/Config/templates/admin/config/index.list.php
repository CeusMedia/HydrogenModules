<?php

if( empty( $foundModules ) )
	return;

$w	= (object) $words['index-list'];

$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user' ) );
$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconEdit		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );
$iconRestore	= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-repeat icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconLock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconUnlock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
	$iconUser		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-user' ) );
	$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-save' ) );
	$iconEdit		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
	$iconRestore	= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-backward' ) );
}

if( count( $foundModules ) > 1 ){
	$list	= array();
	foreach( $foundModules as $module ){
		if( !count( $module->config ) )
			continue;
		$link	= UI_HTML_Tag::create( 'a', $module->title.' <small class="muted">('.count( $module->config ).')</small>', array(
			'href'	=> './admin/config/filter?category='.$module->category.'&moduleId='.$module->id
		) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$list	= UI_HTML_Tag::create( 'ul', $list );

	return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. ' <small class="muted">('.count( $modules ).')</small></h3>
	<div class="content-panel-inner">
		'.$list.'
	</div>
</div>';
}

$list	= array();
foreach( $foundModules as $moduleId => $module ){
	if( !$module->config )
		continue;
	$rows	= array();
	foreach( $module->config as $item ){
		$isNumeric		= in_array( $item->type, array( "integer", "float" ) ) || preg_match( "/^[0-9\.]+$/", $item->value );
		if( $item->type === "boolean" )
			$value	= $item->value ? 'yes' : 'no';
		else{
			$value		= htmlentities( $item->value, ENT_QUOTES, 'UTF-8' );
		}

		$protection	= UI_HTML_Tag::create( 'abbr', $iconUnlock, array( 'title' => 'public - öffentlich (bekannt im Browser)' ) );
		if( $item->protected === "user" )
			$protection	= UI_HTML_Tag::create( 'abbr', $iconUser, array( 'title' => 'user - durch Benutzer konfigurierbar' ) );
		if( $item->protected === "yes" )
			$protection	= UI_HTML_Tag::create( 'abbr', $iconLock, array( 'title' => 'protected - nicht öffentlich (nur auf Server bekannt)' ) );

		$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;
		$key	= $item->title ? '<abbr title="'.$item->title.'">'.$key.'</abbr>' : $key;
		$type	= '<small class="muted">'.$item->type.'</small>';
		$rows[$moduleId.'|'.$item->key]	= new UI_HTML_Tag( 'tr', array(
			new UI_HTML_Tag( 'td', $protection, array( 'class' => 'cell-protection' ) ),
			new UI_HTML_Tag( 'td', $key, array( 'class' => 'cell-key autocut' ) ),
			new UI_HTML_Tag( 'td', $type, array( 'class' => 'cell-type' ) ),
			new UI_HTML_Tag( 'td', $value, array( 'class' => 'cell-value autocut' ) ),
		) );
		ksort( $rows );
	}
	$buttonEdit		= UI_HTML_Tag::create( 'a', $iconEdit.'&nbsp;'.$w->buttonEdit, array(
		'class'		=> 'btn btn-primary',
		'href'		=> './admin/config/edit/'.$moduleId
	) );
	$buttonRestore	= '';
	if( isset( $versions[$moduleId] ) && $versions[$moduleId] ){
		$buttonRestore	= UI_HTML_Tag::create( 'a', $iconRestore.'&nbsp;'.$w->buttonRestore.'&nbsp;<small>('.$versions[$moduleId].')</small>', array(
			'href'	=> './admin/config/restore/'.$moduleId,
			'class'	=> 'btn btn-inverse btn-mini'
		) );
	}
	$cols	= UI_HTML_Elements::ColumnGroup( "24px", "37%", "80px", "" );
	$tbody	= new UI_HTML_Tag( 'tbody', $rows );
	$table	= new UI_HTML_Tag( 'table', $cols.$tbody, array( 'class' => 'table not-table-condensed table-striped', 'style' => 'table-layout: fixed' ) );
	$list[]	= new UI_HTML_Tag( 'h4', $module->title ).$table;
}

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. '</h3>
	<div class="content-panel-inner">
		'.join( $list ).'
		<div class="buttonbar">
			'.$buttonEdit.'
			'.$buttonRestore.'
		</div>
	</div>
</div>';

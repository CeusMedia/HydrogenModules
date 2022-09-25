<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['view'];

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconList		= HtmlTag::create( 'i', '', array( 'class' => 'icon-list' ) );
$iconLock		= HtmlTag::create( 'i', '', array( 'class' => 'icon-lock' ) );
$iconUnlock		= HtmlTag::create( 'i', '', array( 'class' => 'icon-unlock' ) );
$iconUser		= HtmlTag::create( 'i', '', array( 'class' => 'icon-user' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconEdit		= HtmlTag::create( 'i', '', array( 'class' => 'icon-pencil icon-white' ) );
$iconRestore	= HtmlTag::create( 'i', '', array( 'class' => 'icon-repeat icon-white' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconList		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconLock		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconUnlock		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
	$iconUser		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-user' ) );
	$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-save' ) );
	$iconEdit		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
	$iconRestore	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-backward' ) );
}

$rows	= [];
foreach( $module->config as $item ){
	$isNumeric		= in_array( $item->type, array( "integer", "float" ) ) || preg_match( "/^[0-9\.]+$/", $item->value );
	if( preg_match( '/password/', $item->key ) )
		$value	= HtmlTag::create( 'em', 'versteckt', array( 'class' => 'muted') );
	else if( $item->type === "boolean" )
		$value	= $item->value ? 'yes' : 'no';
	else
		$value		= htmlentities( $item->value, ENT_QUOTES, 'UTF-8' );

	$protection	= HtmlTag::create( 'abbr', $iconUnlock, array( 'title' => 'public - öffentlich (bekannt im Browser)' ) );
	if( $item->protected === "user" )
		$protection	= HtmlTag::create( 'abbr', $iconUser, array( 'title' => 'user - durch Benutzer konfigurierbar' ) );
	if( $item->protected === "yes" )
		$protection	= HtmlTag::create( 'abbr', $iconLock, array( 'title' => 'protected - nicht öffentlich (nur auf Server bekannt)' ) );

	$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;
	$key	= $item->title ? '<abbr title="'.$item->title.'">'.$key.'</abbr>' : $key;
	$type	= '<small class="muted">'.$item->type.'</small>';
	$rows[$moduleId.'|'.$item->key]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $protection, array( 'class' => 'cell-protection' ) ),
		HtmlTag::create( 'td', $key, array( 'class' => 'cell-key autocut' ) ),
		HtmlTag::create( 'td', $type, array( 'class' => 'cell-type' ) ),
		HtmlTag::create( 'td', $value, array( 'class' => 'cell-value autocut' ) ),
	) );
//	ksort( $rows );
}
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'class'		=> 'btn',
	'href'		=> './admin/config',
) );
$buttonList		= HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonList, array(
	'class'		=> 'btn',
	'href'		=> './admin/config',
) );
$buttonEdit		= HtmlTag::create( 'a', $iconEdit.'&nbsp;'.$w->buttonEdit, array(
	'class'		=> 'btn btn-primary',
	'href'		=> './admin/config/edit/'.$moduleId
) );
$buttonRestore	= '';
if( isset( $versions[$moduleId] ) && $versions[$moduleId] ){
	$buttonRestore	= HtmlTag::create( 'a', $iconRestore.'&nbsp;'.$w->buttonRestore.'&nbsp;<small>('.$versions[$moduleId].')</small>', array(
		'href'	=> './admin/config/restore/'.$moduleId,
		'class'	=> 'btn btn-inverse btn-mini'
	) );
}
$cols	= HtmlElements::ColumnGroup( "24px", "37%", "80px", "" );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', array( $cols, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );

return '
<div class="content-panel content-panel-form content-panel-filter">
	<h3>'.$w->heading. '</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.HtmlTag::create( 'h4', $module->title ).'
				'.$table.'
				<div class="buttonbar">
		<!--			'.$buttonCancel.'-->
					'.$buttonList.'
					'.$buttonEdit.'
					'.$buttonRestore.'
				</div>
			</div>
		</div>
	</div>
</div>';

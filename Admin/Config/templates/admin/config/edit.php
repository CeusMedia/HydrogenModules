<?php

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/edit/' ) );

$iconCancel		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconList		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-list' ) );
$iconLock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-lock' ) );
$iconUnlock		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-unlock' ) );
$iconUser		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-user' ) );
$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconCancel		= new UI_HTML_Tag( 'i', '', array( 'class' => 'icon-arrow-left' ) );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconList		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconLock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-lock' ) );
	$iconUnlock		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-unlock' ) );
	$iconUser		= new UI_HTML_Tag( 'b', '', array( 'class' => 'fa fa-fw fa-user' ) );
	$iconSave		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconCancel		= new UI_HTML_Tag( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
}

$rows	= [];
foreach( $module->config as $item ){
	$input	= $view->renderConfigInput( $moduleId, $item );

	$protection	= UI_HTML_Tag::create( 'abbr', $iconUnlock, array( 'title' => 'public - öffentlich (bekannt im Browser)' ) );
	if( $item->protected === "user" )
		$protection	= UI_HTML_Tag::create( 'abbr', $iconUser, array( 'title' => 'user - durch Benutzer konfigurierbar' ) );
	if( $item->protected === "yes" )
		$protection	= UI_HTML_Tag::create( 'abbr', $iconLock, array( 'title' => 'protected - nicht öffentlich (nur auf Server bekannt)' ) );

	$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;
	$key	= $item->title ? '<abbr title="'.$item->title.'">'.$key.'</abbr>' : $key;
	$type	= '<small class="muted">'.$item->type.'</small>';
	$rows[$item->key]	= new UI_HTML_Tag( 'tr', array(
		new UI_HTML_Tag( 'td', $protection, array( 'class' => 'cell-protection' ) ),
		new UI_HTML_Tag( 'td', $key, array( 'class' => 'cell-key autocut' ) ),
		new UI_HTML_Tag( 'td', $type, array( 'class' => 'cell-type' ) ),
		new UI_HTML_Tag( 'td', $input, array( 'class' => 'cell-value' ) ),
	) );
//	ksort( $rows );
}

$cols	= UI_HTML_Elements::ColumnGroup( "24px", "37%", "80px", "" );
$tbody	= UI_HTML_Tag::create( 'tbody', $rows );
$table	= UI_HTML_Tag::create( 'table', array( $cols, $tbody ), array( 'class' => 'table table-striped table-fixed' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/config/edit/' ) );

$w	= (object) $words['edit'];

$buttonRestore	= '';
if( $versions ){
	$buttonRestore	= UI_HTML_Tag::create( 'a', $w->buttonRestore.' <small class="not-muted">('.$versions.')</small>', array(
		'href'	=> './admin/config/restore/'.$moduleId,
		'class'	=> 'btn btn-inverse btn-mini'
	) );
}
$buttonCancel	= UI_HTML_Tag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'href'	=> './admin/config/view/'.$moduleId,
	'class'	=> 'btn btn-small',
) );
$buttonList		= UI_HTML_Tag::create( 'a', $iconList.'&nbsp;'.$w->buttonList, array(
	'href'	=> './admin/config',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= UI_HTML_Tag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
) );

return $textTop.'
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/config/edit/'.$module->id.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span12">
					'.UI_HTML_Tag::create( 'h4', $module->title ).'
					'.$table.'
					<div class="buttonbar">
						'.$buttonList.'
						'.$buttonCancel.'
						'.$buttonSave.'
						'.$buttonRestore.'
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
'.$textBottom;

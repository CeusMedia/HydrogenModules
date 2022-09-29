<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object $module */
/** @var string $moduleId */

[$textTop, $textBottom] = $view->populateTexts( ['top', 'bottom'], 'html/admin/config/edit/' );

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
$iconList		= HtmlTag::create( 'i', '', ['class' => 'icon-list'] );
$iconLock		= HtmlTag::create( 'i', '', ['class' => 'icon-lock'] );
$iconUnlock		= HtmlTag::create( 'i', '', ['class' => 'icon-unlock'] );
$iconUser		= HtmlTag::create( 'i', '', ['class' => 'icon-user'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'icon-ok icon-white'] );
$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );
if( $env->getModules()->has( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconList		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list'] );
	$iconLock		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-lock'] );
	$iconUnlock		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-unlock'] );
	$iconUser		= HtmlTag::create( 'b', '', ['class' => 'fa fa-fw fa-user'] );
	$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
	$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
}

$rows	= [];
foreach( $module->config as $item ){
	$input	= $view->renderConfigInput( $moduleId, $item );

	$protection	= HtmlTag::create( 'abbr', $iconUnlock, array( 'title' => 'public - öffentlich (bekannt im Browser)' ) );
	if( $item->protected === "user" )
		$protection	= HtmlTag::create( 'abbr', $iconUser, ['title' => 'user - durch Benutzer konfigurierbar'] );
	if( $item->protected === "yes" )
		$protection	= HtmlTag::create( 'abbr', $iconLock, array( 'title' => 'protected - nicht öffentlich (nur auf Server bekannt)' ) );

	$key	= $item->mandatory ? '<b>'.$item->key.'</b>' : $item->key;
	$key	= $item->title ? '<abbr title="'.$item->title.'">'.$key.'</abbr>' : $key;
	$type	= '<small class="muted">'.$item->type.'</small>';
	$rows[$item->key]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $protection, ['class' => 'cell-protection'] ),
		HtmlTag::create( 'td', $key, ['class' => 'cell-key autocut'] ),
		HtmlTag::create( 'td', $type, ['class' => 'cell-type'] ),
		HtmlTag::create( 'td', $input, ['class' => 'cell-value'] ),
	) );
//	ksort( $rows );
}

$cols	= HtmlElements::ColumnGroup( "24px", "37%", "80px", "" );
$tbody	= HtmlTag::create( 'tbody', $rows );
$table	= HtmlTag::create( 'table', [$cols, $tbody], ['class' => 'table table-striped table-fixed'] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/admin/config/edit/' ) );

$w	= (object) $words['edit'];

$buttonRestore	= '';
if( $versions ){
	$buttonRestore	= HtmlTag::create( 'a', $w->buttonRestore.' <small class="not-muted">('.$versions.')</small>', array(
		'href'	=> './admin/config/restore/'.$moduleId,
		'class'	=> 'btn btn-inverse btn-mini'
	) );
}
$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, array(
	'href'	=> './admin/config/view/'.$moduleId,
	'class'	=> 'btn btn-small',
) );
$buttonList		= HtmlTag::create( 'a', $iconList.'&nbsp;'.$w->buttonList, array(
	'href'	=> './admin/config',
	'class'	=> 'btn btn-small',
) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, array(
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
					'.HtmlTag::create( 'h4', $module->title ).'
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

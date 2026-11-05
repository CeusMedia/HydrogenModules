<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var string $appBrand */
/** @var string $appIcon */
/** @var string $appLogo */
/** @var string $appTitle */

$w		= (object) $words['index'];
$wTitle	= (object) $words['index.title'];
$wBrand	= (object) $words['index.brand'];
$wLogo	= (object) $words['index.logo'];
$wIcon	= (object) $words['index.icon'];

$iconFile	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-folder'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$panelTitle	= '<div class="content-panel">
	<h3>'.$wTitle->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setTitle" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$wTitle->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $appTitle, ENT_QUOTES, 'UTF-8' ).'"/>
					<p><small class="not-muted">'.$wTitle->hintTitle.'</small></p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

$panelBrand	= '<div class="content-panel">
	<h3>'.$wBrand->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setBrand" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">'.$wBrand->labelBrand.'</label>
					<input type="text" name="brand" id="input_title" class="span12" value="'.htmlentities( $appBrand, ENT_QUOTES, 'UTF-8' ).'"/>
					<p><small class="not-muted">'.$wBrand->hintBrand.'</small></p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;'.$w->buttonSave.'</button>
			</div>
		</form>
	</div>
</div>';

$helperUploadLogo	= new View_Helper_Input_File( $env );
$helperUploadLogo->setName( 'logo' );
$helperUploadLogo->setLabel( $iconFile );

$currentLogo	= '<em class="muted">'.$w->labelCurrent.'<br/>'.$wLogo->labelDefault.'</em>';
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'class'		=> 'btn btn-primary',
	'type'		=> 'submit',
] );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'class'		=> 'btn btn-mini btn-inverse',
	'disabled'	=> 'disabled',
	'type'		=> 'button',
] );
if( $appLogo ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
		'href'	=> './admin/app/removeLogo',
		'class'	=> 'btn btn-small btn-inverse',
	] );
	$currentLogo	= HtmlTag::create( 'img', NULL, [
		'src'		=> $appLogo,
		'class'		=> 'thumbnail',
	] );
}

$panelLogo	= '
<div class="content-panel">
	<h3>'.$wLogo->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setLogo" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_logo">'.$wLogo->labelLogo.'</label>
					'.$helperUploadLogo->render().'
					<p><small class="not-muted">'.$wLogo->hintLogo.'</small></p>
				</div>
				<div class="span3 offset1">
					'.$currentLogo.'
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonSave.'
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>';

$helperUploadIcon	= new View_Helper_Input_File( $env );
$helperUploadIcon->setName( 'icon' );
$helperUploadIcon->setLabel( $iconFile );

$currentIcon	= '<em class="muted">'.$w->labelCurrent.'<br/>'.$wIcon->labelDefault.'</em>';
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, [
	'class'		=> 'btn btn-primary',
	'type'		=> 'submit',
] );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.'&nbsp;'.$w->buttonRemove, [
	'class'		=> 'btn btn-mini btn-inverse',
	'disabled'	=> 'disabled',
	'type'		=> 'button',
] );
if( $appIcon ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;'.$w->buttonRemove, [
		'href'	=> './admin/app/removeIcon',
		'class'	=> 'btn btn-small btn-inverse',
	] );
	$currentIcon	= HtmlTag::create( 'img', NULL, [
		'src'		=> $appIcon,
		'class'		=> 'thumbnail',
	] );
}

$panelIcon	= '
<div class="content-panel">
	<h3>'.$wIcon->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setIcon" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_icon">'.$wIcon->labelIcon.'</label>
					'.$helperUploadIcon->render().'
					<p><small class="not-muted">'.$wIcon->hintIcon.'</small></p>
				</div>
				<div class="span3 offset1">
					'.$currentIcon.'
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonSave.'
				'.$buttonRemove.'
			</div>
		</form>
	</div>
</div>';

return '
<h2>'.$w->heading.'</h2>
<div class="row-fluid">
	<div class="span5">
		'.$panelBrand.'
	</div>
	<div class="span7">
		'.$panelLogo.'
	</div>
</div>
<div class="row-fluid">
	<div class="span5">
		'.$panelTitle.'
	</div>
	<div class="span7">
		'.$panelIcon.'
	</div>
</div>';

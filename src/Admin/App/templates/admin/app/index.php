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

$iconFile	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-folder'] );
$iconSave	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconRemove	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] );

$panelTitle	= '<div class="content-panel">
	<h3>Titel</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setTitle" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Titel als einzeiliger Reintext</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $appTitle, ENT_QUOTES, 'UTF-8' ).'"/>
					<p><small class="not-muted">Dieser Titel erscheint an allen Stellen, wo die Anwendung benannt werden soll, ohne HTML zu verwenden. Z.B. die Bezeichnung im Browser-Reiter oder in Text-E-Mails.</small></p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$panelBrand	= '<div class="content-panel">
	<h3>Markenname</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setBrand" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title">Markenname</label>
					<input type="text" name="brand" id="input_title" class="span12" value="'.htmlentities( $appBrand, ENT_QUOTES, 'UTF-8' ).'"/>
					<p><small class="not-muted">Der Markenname erscheint an allen Stellen, wo die Anwendung benannt werden soll und dabei HTML verwenden darf. Z.B. die Bezeichnung in der Kopfleiste oder in HTML-E-Mails.</small></p>
				</div>
			</div>
			<div class="buttonbar">
				<button type="submit" name="save" class="btn btn-primary"><i class="fa fa-fw fa-check"></i>&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$helperUploadLogo	= new View_Helper_Input_File( $env );
$helperUploadLogo->setName( 'logo' );
$helperUploadLogo->setLabel( $iconFile );

$currentLogo	= '<em class="muted">Derzeit:<br/>Standard-Logo</em>';
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
	'class'		=> 'btn btn-primary',
	'type'		=> 'submit',
) );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.'&nbsp;entfernen', array(
	'class'		=> 'btn btn-mini btn-inverse',
	'disabled'	=> 'disabled',
	'type'		=> 'button',
) );
if( $appLogo ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'	=> './admin/app/removeLogo',
		'class'	=> 'btn btn-small btn-inverse',
	) );
	$currentLogo	= HtmlTag::create( 'img', NULL, array(
		'src'		=> $appLogo,
		'class'		=> 'thumbnail',
	) );
}

$panelLogo	= '
<div class="content-panel">
	<h3>Markenlogo</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setLogo" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_logo">Brand-Logo (png oder svg)</label>
					'.$helperUploadLogo->render().'
					<p><small class="not-muted">Das Logo erscheint z.B. in der Kopfleister der Anwendung und in HTML-E-Mails. Empfohlenes Format: SVG. Ansonsten empfohlene Auflösung: 64 oder 128 Pixel. </small></p>
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

$currentIcon	= '<em class="muted">Derzeit:<br/>Standard-Icon</em>';
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
	'class'		=> 'btn btn-primary',
	'type'		=> 'submit',
) );
$buttonRemove	= HtmlTag::create( 'button', $iconRemove.'&nbsp;entfernen', array(
	'class'		=> 'btn btn-mini btn-inverse',
	'disabled'	=> 'disabled',
	'type'		=> 'button',
) );
if( $appIcon ){
	$buttonRemove	= HtmlTag::create( 'a', $iconRemove.'&nbsp;entfernen', array(
		'href'	=> './admin/app/removeIcon',
		'class'	=> 'btn btn-small btn-inverse',
	) );
	$currentIcon	= HtmlTag::create( 'img', NULL, array(
		'src'		=> $appIcon,
		'class'		=> 'thumbnail',
	) );
}

$panelIcon	= '
<div class="content-panel">
	<h3>Icon</h3>
	<div class="content-panel-inner">
		<form action="./admin/app/setIcon" method="post" enctype="multipart/form-data">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_icon">Launcher-Icon (png oder ico)</label>
					'.$helperUploadIcon->render().'
					<p><small class="not-muted">Das Icon erscheint z.B. im Browser-Reiter oder im Launcher.</small></p>
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
<h2>App-Anpassungen</h2>
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

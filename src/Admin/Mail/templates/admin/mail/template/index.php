<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as Html;

$modelMail		= new Model_Mail( $env );

$iconAdd		= Html::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );
$iconImport		= Html::create( 'i', '', ['class' => 'fa fa-fw fa-upload'] );
$iconCancel		= Html::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );

$rows	= [];
foreach( $templates as $template ){
	$title		= $template->title;
	$rowClass	= '';
	if( $template->mailTemplateId == $moduleTemplateId ){
		$title		= $template->title.'&nbsp;<small class="muted">(Standard)</small>';
		$rowClass	= 'success';
	}
	$title	= Html::create( 'a', $title, array(
		'href'	=> './admin/mail/template/edit/'.$template->mailTemplateId,
		'class'	=> 'autocut',
	) );
	switch( $template->status ){
		default:
			$badgeClass	= 'badge';
		case Model_Mail_Template::STATUS_NEW:
		case Model_Mail_Template::STATUS_IMPORTED:
			$badgeClass	= 'badge badge-warning';
			break;
		case Model_Mail_Template::STATUS_USABLE:
			$badgeClass	= 'badge badge-info';
			break;
		case Model_Mail_Template::STATUS_ACTIVE:
			$badgeClass	= 'badge badge-success';
			break;
	}
	$badgeStatus	= Html::create( 'span', $words['status'][$template->status], ['class' => $badgeClass] );
	$rows[]	= Html::create( 'tr', array(
		Html::create( 'td', $title ),
		Html::create( 'td', $badgeStatus ),
		Html::create( 'td', sprintf( $words['index']['valueUsedInMail'], $template->used ) ),
		Html::create( 'td', date( 'd.m.Y H:i', $template->createdAt ) ),
		Html::create( 'td', date( 'd.m.Y H:i', $template->modifiedAt ) ),
	), ['class' => $rowClass] );
}
$tableHeads	= HtmlElements::tableHeads( array(
	$words['index']['headTitle'],
	$words['index']['headStatus'],
	$words['index']['headUsed'],
	$words['index']['headCreated'],
	$words['index']['headModified']
) );

$table	= Html::create( 'table', array(
	HtmlElements::ColumnGroup( ['', '120', '120', '140', '140'] ),
	Html::create( 'thead', $tableHeads ),
	Html::create( 'tbody', $rows ),
), ['class' => 'table table-fixed'] );

$buttonAdd	= Html::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './admin/mail/template/add',
	'class'	=> 'btn btn-success',
) );
$buttonImport	= Html::create( 'a', $iconImport.'&nbsp;'.$words['index']['buttonImport'], array(
	'href'	=> './admin/mail/template/import',
	'class'	=> 'btn',
) );

$iconList		= Html::create( 'b', '', ['class' => 'icon-list'] );
$iconCancel		= Html::create( 'b', '', ['class' => 'icon-arrow-left'] );
$iconFile		= Html::create( 'i', '', ['class' => 'icon-folder-open icon-white'] );
$iconSave		= Html::create( 'b', '', ['class' => 'icon-ok icon-white'] );
if( $env->hasModule( 'UI_Font_FontAwesome' ) ){
	$iconList		= Html::create( 'b', '', ['class' => 'fa fa-fw fa-list'] );
	$iconCancel		= Html::create( 'b', '', ['class' => 'fa fa-fw fa-arrow-left'] );
	$iconFile		= Html::create( 'i', '', ['class' => 'fa fa-fw fa-folder-open'] );
	$iconSave		= Html::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
}

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setButtonClass( 'btn-success' );
$helperUpload->setLabel( $iconFile );
$helperUpload->setName( 'template' );
$helperUpload->setRequired( TRUE );

extract( $view->populateTexts( ['top', 'bottom'], 'html/admin/mail/template/import/', array(
	'labelMaxFileSize'		=> $words['import']['labelMaxFileSize'],
	'valueMaxFileSize'		=> Alg_UnitFormater::formatBytes( $env->getLogic()->upload->getMaxUploadSize() ),
) ) );

$wordsImport		= (object) $words['import'];
$modalImportBody	= $textTop.'
	<div class="row-fluid">
		<div class="span12">
			<label for="input_template">Mail-Template-Datei</label>
			'.$helperUpload->render().'
		</div>
	</div>'.$textBottom;
$modalImport	= new BootstrapModalDialog( 'modal-mail-template-upload' );
$modalImport->setBody( $modalImportBody );
$modalImport->setHeading( 'Import eines Mail-Templates <small class="muted">(aus einer Datei)</small>' );
$modalImport->setFormAction( './admin/mail/template/import' );
$modalImport->setFormIsUpload();
$modalImport->setSubmitButtonLabel( $iconImport.'&nbsp;hochladen' );
$modalImport->setSubmitButtonClass( 'btn btn-primary' );
$modalImport->setCloseButtonLabel( $iconCancel.'&nbsp;abbrechen' );

$modalImportTrigger	= new BootstrapModalTrigger( 'modal-mail-template-upload-trigger' );
$modalImportTrigger->setModalId( 'modal-mail-template-upload' );
$modalImportTrigger->setLabel( $iconImport.'&nbsp;Vorlage importieren' );

return '
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$words['index']['heading'].'</h3>
			<div class="content-panel-inner">
				'.$table.'
				<div class="buttonbar">
					'.$buttonAdd.'
					'.$modalImportTrigger.'
				</div>
			</div>
		</div>
	</div>
</div>'.$modalImport;

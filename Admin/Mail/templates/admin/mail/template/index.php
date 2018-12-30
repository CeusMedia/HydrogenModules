<?php
$modelMail		= new Model_Mail( $env );

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconImport		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-upload' ) );
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );

$rows	= array();
foreach( $templates as $template ){
	$title	= $template->title;
	$title	= UI_HTML_Tag::create( 'a', $template->title, array(
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
	$badgeStatus	= UI_HTML_Tag::create( 'span', $words['status'][$template->status], array( 'class' => $badgeClass ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $title ),
		UI_HTML_Tag::create( 'td', $badgeStatus ),
		UI_HTML_Tag::create( 'td', sprintf( $words['index']['valueUsedInMail'], $modelMail->countByIndex( 'templateId', $template->mailTemplateId ) ) ),
		UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $template->createdAt ) ),
		UI_HTML_Tag::create( 'td', date( 'd.m.Y H:i', $template->modifiedAt ) ),
	) );
}
$tableHeads	= UI_HTML_Elements::tableHeads( array(
	$words['index']['headTitle'],
	$words['index']['headStatus'],
	$words['index']['headUsed'],
	$words['index']['headCreated'],
	$words['index']['headModified']
) );

$table	= UI_HTML_Tag::create( 'table', array(
	UI_HTML_Elements::ColumnGroup( array( '', '120', '120', '140', '140' ) ),
	UI_HTML_Tag::create( 'thead', $tableHeads ),
	UI_HTML_Tag::create( 'tbody', $rows ),
), array( 'class' => 'table table-fixed' ) );

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.'&nbsp;'.$words['index']['buttonAdd'], array(
	'href'	=> './admin/mail/template/add',
	'class'	=> 'btn btn-success',
) );
$buttonImport	= UI_HTML_Tag::create( 'a', $iconImport.'&nbsp;'.$words['index']['buttonImport'], array(
	'href'	=> './admin/mail/template/import',
	'class'	=> 'btn',
) );

$iconList		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'icon-list' ) );
$iconCancel		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'icon-arrow-left' ) );
$iconFile		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );
$iconSave		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->hasModule( 'UI_Font_FontAwesome' ) ){
	$iconList		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconCancel		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconFile		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-folder-open' ) );
	$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setButtonClass( 'btn-success' );
$helperUpload->setLabel( $iconFile );
$helperUpload->setName( 'template' );
$helperUpload->setRequired( TRUE );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/admin/mail/template/import/', array(
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
$modalImport		= new \CeusMedia\Bootstrap\Modal( 'modal-mail-template-upload' );
$modalImport->setBody( $modalImportBody );
$modalImport->setHeading( 'Import eines Mail-Templates <small class="muted">(aus einer Datei)</small>' );
$modalImport->setFormAction( './admin/mail/template/import' );
$modalImport->setFormIsUpload();
$modalImport->setSubmitButtonLabel( $iconImport.'&nbsp;hochladen' );
$modalImport->setSubmitButtonClass( 'btn btn-primary' );
$modalImport->setCloseButtonLabel( $iconCancel.'&nbsp;abbrechen' );

$modalImportTrigger	= new \CeusMedia\Bootstrap\Modal\Trigger( 'modal-mail-template-upload-trigger' );
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

?>

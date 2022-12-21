<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array $groups */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconFile		= HtmlTag::create( 'i', '', ['class' => 'fa fa-folder-open'] );

$optStatus	= $words->states;
unset( $optStatus[-1] );
$optStatus	= HtmlElements::Options( $optStatus );

$w	= (object) $words->add;

$optGroupId	= [];
foreach( $groups as $group )
	$optGroupId[$group->newsletterGroupId]	= $group->title;
$optGroupId	= HtmlElements::Options( $optGroupId );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'upload' );
$helperUpload->setLabel( $iconFile );
$helperUpload->setRequired( TRUE );

return '
<form action="./work/newsletter/reader/import/csv" method="post" enctype="multipart/form-data">
	<div id="modalImportCsv" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">CSV-Exportdatei importieren</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span5">
					<label for="input_groupId">In Gruppe</label>
					<select name="groupId" id="input_groupId" class="span12">'.$optGroupId.'</select>
				</div>
				<div class="span7">
					<label for="input_upload">CSV-Exportdatei</label>
					'.$helperUpload->render().'
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">'.$iconCancel.'abbrechen</button>
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'importieren</button>
		</div>
	</div>
</form>';

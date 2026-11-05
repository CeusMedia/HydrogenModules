<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\View;

/** @var Environment $env */
/** @var View $view */
/** @var object $words */
/** @var array $groups */
/** @var int|string|NULL $groupId */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] ).'&nbsp;';
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
$iconFile		= HtmlTag::create( 'i', '', ['class' => 'fa fa-folder-open'] );

/*$optStatus	= $words->states;
unset( $optStatus[-1] );
$optStatus	= HtmlElements::Options( $optStatus );*/

$w	= (object) $words['import'];

$optGroupId	= [];
foreach( $groups as $group )
	$optGroupId[$group->newsletterGroupId]	= $group->title;
$optGroupId	= HtmlElements::Options( $optGroupId );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'upload' );
$helperUpload->setLabel( $iconFile );
$helperUpload->setRequired( TRUE );

return '
<form action="./work/newsletter/group/import" method="post" enctype="multipart/form-data">
	<input type="hidden" name="groupId" value="'.( $groupId ?? 0 ).'"/>
	<div id="modalImportCsv" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">'.$w->heading.'</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span12">
					<div class="control-group">
						<label class="control-label"><strong>'.$w->labelMode.'</strong></label>
						<div class="controls radio-group">
							<label class="radio radio-block">
								<input type="radio" name="import_mode" value="'.Controller_Work_Newsletter_Group::IMPORT_MODE_ADDITIONAL.'" checked="checked">
								<span>'.$w->labelModeAdditional.'</span><br>
								<span class="muted">'.$w->descModeAdditional.'</span>
							</label>
							<label class="radio radio-block">
								<input type="radio" name="import_mode" value="'.Controller_Work_Newsletter_Group::IMPORT_MODE_FRESH.'">
								<span>'.$w->labelModeFresh.'</span><br>
								<span class="muted">'.$w->descModeFresh.'</span>
							</label>
						</div>
					</div>
					<div class="alert alert-info">
						'.$w->hintExisting.'
					</div>
  				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $group->title, ENT_QUOTES, 'UTF-8' ).'"required/>
  				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_format" class="mandatory">'.$w->labelFormat.'</label>
					<select name="format" id="input_format" class="span12">
						<option value="'.Controller_Work_Newsletter_Group::IMPORT_FORMAT_CSV_DEFAULT.'">'.$w->optionFormatCsvDefault.'</option>
						<option value="'.Controller_Work_Newsletter_Group::IMPORT_FORMAT_CSV_SEMCO.'">'.$w->optionFormatCsvSemco.'</option>
						<option value="'.Controller_Work_Newsletter_Group::IMPORT_FORMAT_XLS_SEMCO.'">'.$w->optionFormatXlsSemco.'</option>
					</select>
  				</div>
				<div class="span7">
					<label for="input_upload">'.$w->labelUpload.'</label>
					'.$helperUpload->render().'
				</div>
			</div>
<!--			<div class="row-fluid">
				<div class="span5">
					<label for="input_groupId">In Empfängerliste</label>
					<select name="groupId" id="input_groupId" class="span12">'.$optGroupId.'</select>
				</div>
				<div class="span7">
					<label for="input_upload">CSV-Exportdatei</label>
					'.$helperUpload->render().'
				</div>
			</div>-->
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">'.$iconCancel.$w->buttonCancel.'</button>
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.$w->buttonSave.'</button>
		</div>
	</div>
</form>';

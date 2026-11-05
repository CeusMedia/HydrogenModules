<?php /** @noinspection DuplicatedCode */

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var array $connectorMap */

/** @var array<string,array<string,string>> $words */

$w				= (object) $words['add'];

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;'.$w->buttonCancel, ['class' => 'btn btn-small', 'href' => './manage/import'] );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;'.$w->buttonSave, ['type' => 'submit', 'class' => 'btn btn-primary'] );

$statuses	= $words['states'];
$authTypes	= $words['authTypes'];

$optStatus		= $statuses;
$optStatus		= HtmlElements::Options( $optStatus );

$optConnector	= [];
foreach( $connectorMap as $connector )
	$optConnector[$connector->importConnectorId]	= $connector->title;
$optConnector	= HtmlElements::Options( $optConnector );

$optAuthType	= $authTypes;
$optAuthType	= HtmlElements::Options( $optAuthType );

return '<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form action="./manage/import/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">'.$w->labelTitle.'</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span6">
					<label for="input_importConnectorId">'.$w->labelConnector.'</label>
					<select name="importConnectorId" id="input_importConnectorId" class="span12">'.$optConnector.'</select>
				</div>
				<div class="span2">
					<label for="input_status">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_hostName" class="mandatory">'.$w->labelHostName.' <small class="muted">'.$w->labelHostName_suffix.'</small></label>
					<input type="text" name="hostName" id="input_hostName" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_hostPort" class="mandatory">'.$w->labelHostPort.'</label>
					<input type="text" name="hostPort" id="input_hostPort" class="span12" required="required"/>
				</div>
				<div class="span5">
					<label for="input_hostPath" class="mandatory">'.$w->labelHostPath.' <small class="muted">'.$w->labelHostPath_suffix.'</small></label>
					<input type="text" name="hostPath" id="input_hostPath" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_authType">'.$w->labelAuthType.'</label>
					<select name="authType" id="input_authType" class="span12 has-optionals">'.$optAuthType.'</select>
				</div>
				<div class="span4 optional authType authType-1">
					<label for="input_authUsername" class="mandatory">'.$w->labelAuthUsername.'</label>
					<input type="text" name="authUsername" id="input_authUsername" class="span12" required="required"/>
				</div>
				<div class="span4 optional authType authType-1">
					<label for="input_authPassword" class="mandatory">'.$w->labelAuthPassword.'</label>
					<input type="password" name="authPassword" id="input_authPassword" class="span12" required="required"/>
				</div>
				<div class="span8 optional authType authType-2">
					<label for="input_authKey" class="mandatory">'.$w->labelAuthKey.'</label>
					<textarea name="authKey" id="input_authKey" rows="3" class="span12" required="required"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">'.$w->labelDescription.'</label>
					<textarea name="description" id="input_description" rows="4" class="span12"></textarea>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';

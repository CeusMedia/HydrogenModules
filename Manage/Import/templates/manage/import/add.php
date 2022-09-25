<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$buttonCancel	= HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', array( 'class' => 'btn btn-small', 'href' => './manage/import' ) );
$buttonSave		= HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array( 'type' => 'submit', 'class' => 'btn btn-primary' ) );

$statuses	= [
	0	=> 'deaktiviert',
	1	=> 'aktiviert',
];
$authTypes	= [
	0	=> 'keine',
	1	=> 'per Login',
	2	=> 'mit Schlüssel',
];

$optStatus		= $statuses;
$optStatus		= HtmlElements::Options( $optStatus );

$optConnector	= [];
foreach( $connectorMap as $connector )
	$optConnector[$connector->importConnectorId]	= $connector->title;
$optConnector	= HtmlElements::Options( $optConnector );

$optAuthType	= $authTypes;
$optAuthType	= HtmlElements::Options( $optAuthType );

return '<div class="content-panel">
	<h3>Neue Importverbindung</h3>
	<div class="content-panel-inner">
		<form action="./manage/import/add" method="post">
			<div class="row-fluid">
				<div class="span4">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span6">
					<label for="input_importConnectorId">Connector</label>
					<select name="importConnectorId" id="input_importConnectorId" class="span12">'.$optConnector.'</select>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_hostName" class="mandatory">Server-Adresse <small class="muted">(Host Name)</small></label>
					<input type="text" name="hostName" id="input_hostName" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_hostPort" class="mandatory">Service-Port</label>
					<input type="text" name="hostPort" id="input_hostPort" class="span12" required="required"/>
				</div>
				<div class="span5">
					<label for="input_hostPath" class="mandatory">Pfad <small class="muted">(absolut)</small></label>
					<input type="text" name="hostPath" id="input_hostPath" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span4">
					<label for="input_authType">Authentifikation</label>
					<select name="authType" id="input_authType" class="span12 has-optionals">'.$optAuthType.'</select>
				</div>
				<div class="span4 optional authType authType-1">
					<label for="input_authUsername" class="mandatory">Benutzername</label>
					<input type="text" name="authUsername" id="input_authUsername" class="span12" required="required"/>
				</div>
				<div class="span4 optional authType authType-1">
					<label for="input_authPassword" class="mandatory">Passwort</label>
					<input type="password" name="authPassword" id="input_authPassword" class="span12" required="required"/>
				</div>
				<div class="span8 optional authType authType-2">
					<label for="input_authKey" class="mandatory">Schlüssel</label>
					<textarea name="authKey" id="input_authKey" rows="3" class="span12" required="required"></textarea>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung</label>
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

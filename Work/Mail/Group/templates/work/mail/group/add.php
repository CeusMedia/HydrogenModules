<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$optManagerId	= array();
foreach( $users as $user )
	$optManagerId[$user->userId]	= $user->username;
$optManagerId	= UI_HTML_Elements::Options( $optManagerId );

$optStatus		= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus		= UI_HTML_Elements::Options( $optStatus, 0 );

$optRoleId	= array();
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= UI_HTML_Elements::Options( $optRoleId );

$optType		= UI_HTML_Elements::Options( $words['group-types'], @$data->type );
$optVisibility	= UI_HTML_Elements::Options( $words['group-visibilities'], @$data->visibility );


$panelAdd	= '
<div class="content-panel">
	<h3>Neue E-Mail-Gruppe</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/add" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12" readonly="readonly">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_address" class="mandatory">E-Mail-Adresse</label>
					<input type="email" name="address" id="input_address" class="span12" required="required"/>
				</div>
				<div class="span4">
					<label for="input_password" class="mandatory">Passwort</label>
					<input type="password" name="password" id="input_password" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span6">
					<label for="input_roleId">Standard-Rolle</label>
					<select name="roleId" id="input_roleId" class="span12">'.$optRoleId.'</select>
				</div>
				<div class="span6">
					<label for="input_managerId">Administrator</label>
					<select name="managerId" id="input_managerId" class="span12">'.$optManagerId.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span7">
					<label for="input_type">Beitritt</label>
					<select name="type" id="input_type" class="span12">'.$optType.'</select>
				</div>
				<div class="span5">
					<label for="input_visibility">Sichtbarkeit</label>
					<select name="visibility" id="input_visibility" class="span12">'.$optVisibility.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_description">Beschreibung der Gruppe</label>
					<textarea name="description" id="input_description" rows="6" class="span12">'.@$data->description.'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';

$tabs	= $view->renderTabs( $env, 0 );

return $tabs.'<div class="row-fluid"><div class="span6">'.$panelAdd.'</div></div>';

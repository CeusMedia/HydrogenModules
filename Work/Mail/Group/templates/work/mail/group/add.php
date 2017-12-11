<?php

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$optAdminId		= array();
foreach( $users as $user )
	$optAdminId[$user->userId]	= $user->username;
$optAdminId		= UI_HTML_Elements::Options( $optAdminId );

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

$panelAdd	= '
<div class="content-panel">
	<h3>Neue E-Mail-Gruppe</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/add" method="post">
			<div class="row-fluid">
				<div class="span3">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required"/>
				</div>
				<div class="span3">
					<label for="input_address" class="mandatory">E-Mail-Adresse</label>
					<input type="email" name="address" id="input_address" class="span12" required="required"/>
				</div>
				<div class="span2">
					<label for="input_adminId">Administrator</label>
					<select name="adminId" id="input_adminId" class="span12">'.$optAdminId.'</select>
				</div>
				<div class="span2">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12" readonly="readonly">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_roleId">Standard-Rolle</label>
					<select name="roleId" id="input_roleId" class="span12">'.$optRoleId.'</select>
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

return $tabs.$panelAdd;

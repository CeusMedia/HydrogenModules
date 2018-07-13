<?php

$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );

$iconActivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconDeactivate	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );


$optManagerId	= array();
foreach( $users as $user )
	$optManagerId[$user->userId]	= $user->username;
$optManagerId	= UI_HTML_Elements::Options( $optManagerId, $group->managerId );

$optStatus		= $words['group-statuses'];
$optStatus		= UI_HTML_Elements::Options( $optStatus, $group->status );

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->mailGroupRoleId]	= $role->title;

$statusClasses	= array(
	-2	=> 'label-inverse',
	-1	=> 'label-inverse',
	0	=> 'label-info',
	1	=> 'label-warning',
	2	=> 'label-success',
);

$optType		= UI_HTML_Elements::Options( $words['group-types'], $group->type );
$optVisibility	= UI_HTML_Elements::Options( $words['group-visibilities'], $group->visibility );

$optRoleId	= array();
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= UI_HTML_Elements::Options( $optRoleId, $group->defaultRoleId );

$panelEdit	= '
<div class="content-panel">
	<h3>E-Mail-Gruppe bearbeiten</h3>
	<div class="content-panel-inner">
		<form action="./work/mail/group/edit/'.$group->mailGroupId.'" method="post">
			<div class="row-fluid">
				<div class="span8">
					<label for="input_title" class="mandatory">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" required="required" value="'.htmlentities( $group->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span8">
					<label for="input_address" class="mandatory">E-Mail-Adresse</label>
					<input type="email" name="address" id="input_address" class="span12" required="required" value="'.htmlentities( $group->address, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span4">
					<label for="input_password" class="mandatory">Passwort</label>
					<input type="password" name="password" id="input_password" class="span12"/>
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
					<textarea name="description" id="input_description" rows="6" class="span12">'.$group->description.'</textarea>
				</div>
			</div>
			<div class="buttonbar">
				<a href="./work/mail/group" class="btn">'.$iconCancel.'&nbsp;zurück</a>
				<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
			</div>
		</form>
	</div>
</div>';


//  --  MEMBERS LIST  --  //

$list	= UI_HTML_Tag::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $members ){
	$list	= array();
	foreach( $members as $member ){
		$buttonActivate	= UI_HTML_Tag::create( 'a', $iconActivate, array(
			'href'	=> './work/mail/group/setMemberStatus/'.$group->mailGroupId.'/'.$member->mailGroupMemberId.'/2',
			'class'	=> 'btn btn-success btn-mini',
			'title'	=> 'aktivieren',
		) );
		$buttonDeactivate	= UI_HTML_Tag::create( 'a', $iconDeactivate, array(
			'href'	=> './work/mail/group/setMemberStatus/'.$group->mailGroupId.'/'.$member->mailGroupMemberId.'/-2',
			'class'	=> 'btn btn-inverse btn-mini',
			'title'	=> 'deaktivieren',
		) );
		$buttonRemove	= UI_HTML_Tag::create( 'a', $iconRemove, array(
			'href'	=> './work/mail/group/removeMember/'.$group->mailGroupId.'/'.$member->mailGroupMemberId,
			'class'	=> 'btn btn-danger btn-mini',
			'title'	=> 'entfernen',
		) );
		if( $member->status == Model_Mail_Group_Member::STATUS_ACTIVATED ){
			$buttonActivate	= '';
			$buttonRemove	= '';
		}
		if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED ){
			$buttonDeactivate	= '';
		}
		if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED ){
			$buttonActivate		= '';
			$buttonDeactivate	= '';
		}
		$buttons	= UI_HTML_Tag::create( 'div', array( $buttonActivate, $buttonDeactivate, $buttonRemove ), array( 'class' => 'btn-group' ) );
		$address	= UI_HTML_Tag::create( 'span', $member->address, array( 'class' => 'muted' ) );
		$name		= UI_HTML_Tag::create( 'small', $member->title, array( 'class' => '' ) );
		$status		= UI_HTML_Tag::create( 'span', $words['member-statuses'][$member->status], array( 'class' => 'label '.$statusClasses[$member->status] ) );
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $name.'<br/>'.$address, array() ),
			UI_HTML_Tag::create( 'td', $roleMap[$member->roleId].'<br/>'.$status, array() ),
			UI_HTML_Tag::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '20%', '100px' );
	$thead	= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Name & E-Mail-Adresse',
		'Rolle',
		'',
	) ) );
	$tbody	= UI_HTML_Tag::create( 'tbody', $list );
	$list	= UI_HTML_Tag::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}

$panelMembers	= '
<div class="content-panel">
	<h3>Mitglieder der Gruppe</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span12">
				'.$list.'
			</div>
		</div>
		<div class="buttonbar">
			<a href="#modalWorkMailGroupMemberAdd" role="button" class="btn btn-success" data-toggle="modal">'.$iconAdd.'&nbsp;hinzufügen</a>
		</div>
	</div>
</div>';

//  --  MODAL: ADD  --  //

$optRoleId	= array();
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= UI_HTML_Elements::Options( $optRoleId, $group->defaultRoleId );

$optStatus		= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus		= UI_HTML_Elements::Options( $optStatus, $group->status );

$modalMemberAdd	= '
<form action="./work/mail/group/addMember/'.$group->mailGroupId.'" method="post">
	<div id="modalWorkMailGroupMemberAdd" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Mitglied hinzufügen</h3>
		</div>
		<div class="modal-body">
			<div class="row-fluid">
				<div class="span5">
					<label for="input_member_title" class="mandatory">Name</label>
					<input type="text" name="title" id="input_member_title" class="span12" required="required"/>
				</div>
				<div class="span7">
					<label for="input_member_address" class="mandatory">E-Mail-Adresse</label>
					<input type="email" name="address" id="input_member_address" class="span12" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span5">
					<label for="input_member_roleId">Rolle</label>
					<select name="roleId" id="input_member_roleId" class="span12">'.$optRoleId.'</select>
				</div>
<!--				<div class="span4">
					<label for="input_status">Zustand</label>
					<select name="status" id="input_status" class="span12">'.$optStatus.'</select>
				</div>-->
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label class="checkbox">
						<input type="checkbox" name="invite" value="1" checked="checked" class="has-optionals" data-animation="slide"/>Einladen - muss durch Mitglied bestätigt werden.
					</label>
				</div>
			</div>
			<div class="row-fluid optional invite invite-false">
				<div class="span12">
					<label class="checkbox">
						<input type="checkbox" name="quiet" value="1"/>Die anderen Mitglieder nicht über den Zugang informieren.
					</label>
					<div class="alert alert-info">
						Der Teilnehmer bekommt trotzdem eine E-Mail mit Informationen über seinen Beitritt, Nutzungsbedingungen und Datenschutzbestimmungen sowie den Abmeldelink.
					</div>
				</div>
			</div>
			<div class="row-fluid optional invite invite-false">
				<div class="span12">
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">'.$iconCancel.'&nbsp;abbrechen</button>
			<button type="submit" name="save" class="btn btn-primary">'.$iconSave.'&nbsp;speichern</button>
		</div>
	</div>
</form>';

$tabs	= $view->renderTabs( $env );

return $tabs.'<div class="row-fluid">
	<div class="span6">
		'.$panelEdit.'
	</div>
	<div class="span6">
		'.$panelMembers.'
	</div>
</div>'.$modalMemberAdd;

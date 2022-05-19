<?php
use CeusMedia\Bootstrap\Modal\Dialog as BootstrapModalDialog;
use CeusMedia\Bootstrap\Modal\Trigger as BootstrapModalTrigger;
use UI_HTML_Tag as Html;

$iconAdd		= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );
$iconEdit		= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-pencil' ) );
$iconCancel		= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
$iconSave		= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconRemove		= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-trash' ) );
$iconActivate	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconDeactivate	= Html::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );

$roleMap	= [];
foreach( $roles as $role )
	$roleMap[$role->mailGroupRoleId]	= $role->title;

$statusClasses	= array(
	-3	=> 'label-inverse',
	-2	=> 'label-inverse',
	-1	=> 'label-inverse',
	0	=> 'label-info',
	1	=> 'label-warning',
	2	=> 'label-success',
);

//  --  MODAL: ADD  --  //

$optRoleId	= [];
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= UI_HTML_Elements::Options( $optRoleId, $group->defaultRoleId );

$optStatus		= UI_HTML_Elements::Options( array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
), $group->status );

$body	= '
<div class="row-fluid">
	<div class="span8">
		<label for="input_member_title" class="mandatory">Name</label>
		<input type="text" name="title" id="input_member_title" class="span12" required="required"/>
	</div>
	<div class="span4">
		<label for="input_member_roleId">Rolle</label>
		<select name="roleId" id="input_member_roleId" class="span12">'.$optRoleId.'</select>
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<label for="input_member_address" class="mandatory">E-Mail-Adresse</label>
		<input type="email" name="address" id="input_member_address" class="span12" required="required"/>
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
</div>';

$modalMemberAdd	= new BootstrapModalDialog( 'modalWorkMailGroupMemberAdd' );
$modalMemberAdd->setHeading( 'Mitglied hinzufügen' );
$modalMemberAdd->setBody( $body );
$modalMemberAdd->setCloseButtonLabel( $iconCancel.'&nbsp;abbrechen' );
$modalMemberAdd->setSubmitButtonLabel( $iconSave.'&nbsp;speichern' );
$modalMemberAdd->setSubmitButtonClass( 'btn btn-primary' );
$modalMemberAdd->setFormAction( './work/mail/group/addMember/'.$group->mailGroupId );

$modalMemberAddTrigger	= new BootstrapModalTrigger( 'modalWorkMailGroupMemberAddTrigger' );
$modalMemberAddTrigger->setModalId( 'modalWorkMailGroupMemberAdd' );
$modalMemberAddTrigger->setLabel( $iconAdd.'&nbsp;hinzufügen' );
$modalMemberAddTrigger->setAttributes( array( 'class' => 'btn btn-success' ) );

//  --  MEMBERS LIST  --  //

$list	= Html::create( 'div', 'Keine vorhanden.', array( 'class' => 'alert alert-info' ) );
if( $members ){
	$list	= [];
	foreach( $members as $member ){
		$buttonEdit	= new BootstrapModalTrigger( 'modal-trigger-edit-'.$member->mailGroupMemberId );
		$buttonEdit->setModalId( 'modal-edit-'.$member->mailGroupMemberId );
		$buttonEdit->setLabel( $iconEdit );
		$buttonEdit->setAttributes( array( 'class' => 'btn btn-mini' ) );
		$buttonActivate	= Html::create( 'a', $iconActivate, array(
			'href'	=> './work/mail/group/setMemberStatus/'.$group->mailGroupId.'/'.$member->mailGroupMemberId.'/2',
			'class'	=> 'btn btn-success btn-mini',
			'title'	=> 'aktivieren',
		) );
		$buttonDeactivate	= Html::create( 'a', $iconDeactivate, array(
			'href'	=> './work/mail/group/setMemberStatus/'.$group->mailGroupId.'/'.$member->mailGroupMemberId.'/-3',
			'class'	=> 'btn btn-inverse btn-mini',
			'title'	=> 'deaktivieren',
		) );
		$buttonReject	= Html::create( 'a', $iconDeactivate, array(
			'href'	=> './work/mail/group/setMemberStatus/'.$group->mailGroupId.'/'.$member->mailGroupMemberId.'/-2',
			'class'	=> 'btn btn-inverse btn-mini',
			'title'	=> 'ablehnen',
		) );
		$buttonRemove	= Html::create( 'a', $iconRemove, array(
			'href'	=> './work/mail/group/removeMember/'.$group->mailGroupId.'/'.$member->mailGroupMemberId,
			'class'	=> 'btn btn-danger btn-mini',
			'title'	=> 'entfernen',
		) );
		if( $member->status == Model_Mail_Group_Member::STATUS_REJECTED )
			$buttons	= array( $buttonEdit, $buttonActivate, $buttonRemove );
		if( $member->status == Model_Mail_Group_Member::STATUS_ACTIVATED )
			$buttons	= array( $buttonEdit, $buttonDeactivate );
		if( $member->status == Model_Mail_Group_Member::STATUS_DEACTIVATED )
			$buttons	= array( $buttonEdit, $buttonActivate, $buttonRemove );
		if( $member->status == Model_Mail_Group_Member::STATUS_REGISTERED ){
			$buttons	= array( $buttonEdit, $buttonReject );
//			if( $group->type == Model_Mail_Group::TYPE_JOIN )
//				$buttons	= [];
		}
		if( $member->status == Model_Mail_Group_Member::STATUS_CONFIRMED )
			$buttons	= array( $buttonEdit, $buttonActivate, $buttonReject );
		if( $member->status == Model_Mail_Group_Member::STATUS_UNREGISTERED )
			$buttons	= array( $buttonEdit, $buttonRemove );
		$buttons	= Html::create( 'div', $buttons, array( 'class' => 'btn-group' ) );
		$address	= Html::create( 'span', $member->address, array( 'class' => 'muted' ) );
		$name		= Html::create( 'small', $member->title, array( 'class' => '' ) );
		$status		= Html::create( 'span', $words['member-statuses'][$member->status], array( 'class' => 'label '.$statusClasses[$member->status] ) );
		$list[]	= Html::create( 'tr', array(
			Html::create( 'td', $name.'<br/>'.$address, array() ),
			Html::create( 'td', $roleMap[$member->roleId].'<br/>'.$status, array() ),
			Html::create( 'td', $buttons, array( 'style' => 'text-align: right' ) ),
		) );

		$optRoleId	= [];
		foreach( $roles as $role )
			$optRoleId[$role->mailGroupRoleId]	= $role->title;
		$optRoleId	= UI_HTML_Elements::Options( $optRoleId, $member->roleId );

		$modal	= new BootstrapModalDialog( 'modal-edit-'.$member->mailGroupMemberId );
		$modal->setHeading( 'Mitglied bearbeiten' );
		$modal->setSubmitButtonClass( 'btn btn-primary' );
		$modal->setFormAction( './work/mail/group/editMember/'.$group->mailGroupId.'/'.$member->mailGroupMemberId );
		$modal->setSubmitButtonLabel( $iconSave.' speichern' );
		$modal->setCloseButtonLabel( $iconCancel.' abbrechen' );
		$modal->setBody( array(
			Html::create( 'div', array(
				Html::create( 'div', array(
					Html::create( 'label', 'Name', array( 'for' => 'input_title' ) ),
					Html::create( 'input', NULL, array(
						'type'	=> 'text',
						'id'	=> 'input_title',
						'name'	=> 'title',
						'class'	=> 'span12',
						'value'	=> $member->title,
					) ),
				), array( 'class' => 'span8' ) ),
				Html::create( 'div', array(
					Html::create( 'label', 'Rolle', array( 'for' => 'input_' ) ),
					Html::create( 'select', $optRoleId, array(
						'id'	=> 'input_roleId',
						'name'	=> 'roleId',
						'class'	=> 'span12',
					) ),
				), array( 'class' => 'span4' ) ),
			), array( 'class' => 'row-fluid' ) ),
			Html::create( 'div', array(
				Html::create( 'div', array(
					Html::create( 'label', 'E-Mail-Adresse', array( 'for' => 'input_address' ) ),
					Html::create( 'input', NULL, array(
						'type'	=> 'text',
						'id'	=> 'input_address',
						'name'	=> 'address',
						'class'	=> 'span12',
						'value'	=> $member->address,
					) ),
				), array( 'class' => 'span12' ) ),
			), array( 'class' => 'row-fluid' ) ),
		) );
		$modals[]	= $modal;
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( '', '20%', '100px' );
	$thead		= Html::create( 'thead', UI_HTML_Elements::TableHeads( array(
		'Name & E-Mail-Adresse',
		'Rolle',
		'',
	) ) );
	$tbody		= Html::create( 'tbody', $list );
	$list		= Html::create( 'table', array( $colgroup, $thead, $tbody ), array( 'class' => 'table table-fixed' ) );
}

return Html::create( 'div', array(
	Html::create( 'h3', 'Mitglieder der Gruppe' ),
	Html::create( 'div', array(
		Html::create( 'div', array(
			Html::create( 'div', array(
				$list
			), array( 'class' => 'span12' ) ),
		), array( 'class' => 'row-fluid' ) ),
		Html::create( 'div', array(
			$modalMemberAddTrigger
		), array( 'class' => 'buttonbar' ) ),
	), array( 'class' => 'content-panel-inner' ) )
), array( 'class' => 'content-panel' ) ).$modalMemberAdd.join( $modals );

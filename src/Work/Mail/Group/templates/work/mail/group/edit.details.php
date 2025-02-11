<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var object[] $users */
/** @var object[] $servers */
/** @var object[] $roles */
/** @var array $words */
/** @var object $group */

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$optManagerId	= [];
foreach( $users as $user )
	$optManagerId[$user->userId]	= $user->username;
$optManagerId	= HtmlElements::Options( $optManagerId, $group->managerId );

$optStatus		= $words['group-statuses'];
unset( $optStatus[Model_Mail_Group::STATUS_WORKING] );
$optStatus		= HtmlElements::Options( $optStatus, $group->status );

$optType		= HtmlElements::Options( $words['group-types'], $group->type );
$optVisibility	= HtmlElements::Options( $words['group-visibilities'], $group->visibility );

$optRoleId	= [];
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= HtmlElements::Options( $optRoleId, $group->defaultRoleId );

$optServerId		= [];
foreach( $servers as $server )
	$optServerId[$server->mailGroupServerId]	= $server->title;
$optServerId		= HtmlElements::Options( $optServerId, @$data->mailGroupServerId );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', 'E-Mail-Gruppe bearbeiten' ),
	HtmlTag::create( 'div', [
		HtmlTag::create( 'form', [
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Title', [
						'for'	=> 'input_title',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'input', NULL, [
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'class'		=> 'span12',
						'required'	=> 'required',
						'value'		=> htmlentities( $group->title, ENT_QUOTES, 'UTF-8' ),
					] ),
				], ['class' => 'span7'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Server', [
						'for'	=> 'input_mailGroupServerId',
					] ),
					HtmlTag::create( 'select', $optServerId, [
						'name'		=> 'mailGroupServerId',
						'id'		=> 'input_mailGroupServerId',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span5'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'E-Mail-Adresse', [
						'for'	=> 'input_address',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'input', NULL, [
						'type'			=> 'email',
						'name'			=> 'address',
						'id'			=> 'input_address',
						'class'			=> 'span12',
						'required'		=> 'required',
						'value'			=> htmlentities( $group->address, ENT_QUOTES, 'UTF-8' ),
						'autocomplete'	=> 'off',
					] ),
				], ['class' => 'span5'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Passwort', [
						'for'	=> 'input_password',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'input', NULL, [
						'type'			=> 'password',
						'name'			=> 'password',
						'id'			=> 'input_password',
						'class'			=> 'span12',
						'autocomplete'	=> 'off',
					] ),
				], ['class' => 'span3'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Zustand', [
						'for'	=> 'input_status',
					] ),
					HtmlTag::create( 'select', $optStatus, [
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span4'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Administrator', [
						'for'	=> 'input_managerId',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'select', $optManagerId, [
						'name'		=> 'managerId',
						'id'		=> 'input_managerId',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span6'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Standard-Rolle', [
						'for'	=> 'input_roleId',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'select', $optRoleId, [
						'name'		=> 'roleId',
						'id'		=> 'input_roleId',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span6'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Beitritt', [
						'for'	=> 'input_type',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'select', $optType, [
						'name'		=> 'type',
						'id'		=> 'input_type',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span7'] ),
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Sichtbarkeit', [
						'for'	=> 'input_visibility',
						'class'	=> 'mandatory',
					] ),
					HtmlTag::create( 'select', $optVisibility, [
						'name'		=> 'visibility',
						'id'		=> 'input_visibility',
						'class'		=> 'span12',
					] ),
				], ['class' => 'span5'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', [
				HtmlTag::create( 'div', [
					HtmlTag::create( 'label', 'Beschreibung der Gruppe', [
						'for'	=> 'input_description',
					] ),
					HtmlTag::create( 'textarea', htmlentities( $group->description, ENT_QUOTES, 'UTF-8' ), [
						'name'	=> 'description',
						'id'	=> 'input_description',
						'rows'	=> 6,
						'class'	=> 'span12',
					] ),
				], ['class' => 'span12'] ),
			], ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', [
				HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', [
					'href'	=> './work/mail/group',
					'class'	=> 'btn',
				] ),
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', [
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				] ),
			] ), ['class' => 'buttonbar'] ),
		], [
			'action'		=> './work/mail/group/edit/'.$group->mailGroupId,
			'method'		=> 'post',
			'autocomplete'	=> 'off',
		] ),
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );

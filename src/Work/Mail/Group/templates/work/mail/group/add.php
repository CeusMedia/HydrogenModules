<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-arrow-left'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );

$optManagerId	= [];
foreach( $users as $user )
	$optManagerId[$user->userId]	= $user->username;
$optManagerId	= HtmlElements::Options( $optManagerId );

$optStatus		= array(
	-1		=> 'deaktiviert',
	0		=> 'in Arbeit',
	1		=> 'aktiviert',
);
$optStatus		= HtmlElements::Options( $optStatus, 0 );

$optRoleId	= [];
foreach( $roles as $role )
	$optRoleId[$role->mailGroupRoleId]	= $role->title;
$optRoleId	= HtmlElements::Options( $optRoleId );

$optType		= HtmlElements::Options( $words['group-types'], @$data->type );
$optVisibility	= HtmlElements::Options( $words['group-visibilities'], @$data->visibility );

//print_m( $servers );die;

$optServerId		= [];
foreach( $servers as $server )
	$optServerId[$server->mailGroupServerId]	= $server->title;
$optServerId		= HtmlElements::Options( $optServerId, @$data->mailGroupServerId );

$panelAdd	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', 'Neue E-Mail-Gruppe' ),
	HtmlTag::create( 'div', array(
		HtmlTag::create( 'form', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Title', array(
						'for'	=> 'input_title',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'text',
						'name'		=> 'title',
						'id'		=> 'input_title',
						'class'		=> 'span12',
						'required'	=> 'required',
						'value'		=> htmlentities( @$data->title, ENT_QUOTES, 'UTF-8' ),
					) ),
				), ['class' => 'span7'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Server', array(
						'for'	=> 'input_mailGroupServerId',
					) ),
					HtmlTag::create( 'select', $optServerId, array(
						'name'		=> 'mailGroupServerId',
						'id'		=> 'input_mailGroupServerId',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span5'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'E-Mail-Adresse', array(
						'for'	=> 'input_address',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'email',
						'name'		=> 'address',
						'id'		=> 'input_address',
						'class'		=> 'span12',
						'required'	=> 'required',
						'value'		=> htmlentities( @$data->address, ENT_QUOTES, 'UTF-8' ),
					) ),
				), ['class' => 'span5'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Passwort', array(
						'for'	=> 'input_password',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'input', NULL, array(
						'type'		=> 'password',
						'name'		=> 'password',
						'id'		=> 'input_password',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span3'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Zustand', array(
						'for'	=> 'input_status',
					) ),
					HtmlTag::create( 'select', $optStatus, array(
						'name'		=> 'status',
						'id'		=> 'input_status',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span4'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Administrator', array(
						'for'	=> 'input_managerId',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'select', $optManagerId, array(
						'name'		=> 'managerId',
						'id'		=> 'input_managerId',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span6'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Standard-Rolle', array(
						'for'	=> 'input_roleId',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'select', $optRoleId, array(
						'name'		=> 'roleId',
						'id'		=> 'input_roleId',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span6'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Beitritt', array(
						'for'	=> 'input_type',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'select', $optType, array(
						'name'		=> 'type',
						'id'		=> 'input_type',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span7'] ),
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Sichtbarkeit', array(
						'for'	=> 'input_visibility',
						'class'	=> 'mandatory',
					) ),
					HtmlTag::create( 'select', $optVisibility, array(
						'name'		=> 'visibility',
						'id'		=> 'input_visibility',
						'class'		=> 'span12',
					) ),
				), ['class' => 'span5'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'label', 'Beschreibung der Gruppe', array(
						'for'	=> 'input_description',
					) ),
					HtmlTag::create( 'textarea', htmlentities( @$data->description, ENT_QUOTES, 'UTF-8' ), array(
						'name'	=> 'description',
						'id'	=> 'input_description',
						'rows'	=> 6,
						'class'	=> 'span12',
					) ),
				), ['class' => 'span12'] ),
			), ['class' => 'row-fluid'] ),
			HtmlTag::create( 'div', join( ' ', array(
				HtmlTag::create( 'a', $iconCancel.'&nbsp;zurück', array(
					'href'	=> './work/mail/group',
					'class'	=> 'btn',
				) ),
				HtmlTag::create( 'button', $iconSave.'&nbsp;speichern', array(
					'type'	=> 'submit',
					'name'	=> 'save',
					'class'	=> 'btn btn-primary',
				) ) ),
			), ['class' => 'buttonbar'] ),
		), array(
			'action'	=> './work/mail/group/add',
			'method'	=> 'post'
		) ),
	), ['class' => 'content-panel-inner'] ),
), ['class' => 'content-panel'] );

$tabs	= $view->renderTabs( $env );
$layout	= HtmlTag::create( 'div', array(
	HtmlTag::create( 'div', $panelAdd, ['class' => 'span6'] ),
), ['class' => 'row-fluid'] );

return $tabs.$layout;

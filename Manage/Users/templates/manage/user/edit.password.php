<?php

$pathJsLib	= $env->getConfig()->get( 'path.scripts.lib' );
$env->page->js->addUrl( $pathJsLib.'jquery/pstrength/2.1.0.min.js' );

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['editPassword'];

$script		= '
function matchPasswords(){
	var inputPassword = jQuery("#input_passwordNew");
	var inputConfirm = jQuery("#input_passwordConfirm");
	inputConfirm.removeClass("invalid valid");
	inputConfirm[0].setCustomValidity("");
	if(inputConfirm.val().length){
		var match = inputPassword.val() === inputConfirm.val();
		inputConfirm.addClass(match ? "valid" : "invalid");
		if(!match)
			inputConfirm[0].setCustomValidity("Password must match");
	}
}
$(document).ready(function(){
	if('.$pwdMinLength.'||'.$pwdMinStrength.'){
		var inputPassword = jQuery("#input_passwordNew");
		var inputConfirm = jQuery("#input_passwordConfirm");
		inputPassword.pstrength({
			minChar: '.$pwdMinLength.',
			displayMinChar: false,//'.$pwdMinLength.',
			minCharText:  "'.$words['pstrength']['mininumLength'].'",
			verdicts:	[
				"'.$words['pstrength']['verdict-1'].'",
				"'.$words['pstrength']['verdict-2'].'",
				"'.$words['pstrength']['verdict-3'].'",
				"'.$words['pstrength']['verdict-4'].'",
				"'.$words['pstrength']['verdict-5'].'"
			],
			colors: ["#f00", "#f60", "#cc0", "#3f0", "#3c0"]
		});
		inputPassword.bind("input", matchPasswords);
		inputConfirm.bind("input", matchPasswords);
	}
});';
$env->page->js->addScript( $script );

$atLeastOne		= TRUE;
$history		= '';
if( !$atLeastOne || count( $passwords ) > 1 ){
	$passwordCryptTypes = array_flip( ADT_Constant::getAll( 'PASSWORD_' ) );

	$rows	= array();
	foreach( $passwords as $password ){
		$rowClass	= 'info';
		if( $password->status == Model_User_Password::STATUS_NEW )
			$rowClass	= 'warning';
		if( $password->status == Model_User_Password::STATUS_ACTIVE )
			$rowClass	= 'success';
		$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
		$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
		$labelStatus	= $words['password-statuses'][$password->status];
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			UI_HTML_Tag::create( 'td', $labelStatus ),
//			UI_HTML_Tag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[$password->algo] ) ),
//			UI_HTML_Tag::create( 'td', $password->failsTotal ),
		), array( 'class' => $rowClass ) );
	}
	$heads	= UI_HTML_Elements::tableHeads( array(
		'erstellt',
		'zuletzt genutzt',
		'Zustand',
//		'Kryptografie',
//		'gescheiterte Login',
	) );

	$table	= UI_HTML_Tag::create( 'table', array(
		UI_HTML_Elements::ColumnGroup( array(
			'120px',
			'120px',
			'100px',
//					'',
//					'',
		) ),
		UI_HTML_Tag::create( 'thead', $heads ),
		UI_HTML_Tag::create( 'tbody', $rows )
	), array( 'class' => 'table table-condensed table-fixed table-bordered' ) );
	$history	= HTML::DivClass( 'collapsable-block', array(
//		UI_HTML_Tag::create( 'h4', 'Historie', array( 'class' => 'collapsable-block-trigger' ) ),
		HTML::DivClass( 'collapsable-block-content', $table ),
	) );
}

$panelPassword	= HTML::DivClass( 'content-panel content-panel-form', array(
	UI_HTML_Tag::create( 'h3', $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/user/password/'.$userId, 'manage_user_password', array(
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'span6', array(
					HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory', sprintf( $w->labelPasswordNew_title, $pwdMinLength ) ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordNew",
						'id'			=> "input_passwordNew",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'minlength'		=> $pwdMinLength,
						'value'			=> "",
						'autocomplete'	=> "new-password"
					) ),
				) ),
				HTML::DivClass( 'span6', array(
					HTML::Label( 'passwordConfirm', $w->labelPasswordConfirm, 'mandatory', $w->labelPasswordConfirm_title ),
					UI_HTML_Tag::create( 'input', NULL, array(
						'type'			=> "password",
						'name'			=> "passwordConfirm",
						'id'			=> "input_passwordConfirm",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'minlength'		=> $pwdMinLength,
						'value'			=> "",
						'autocomplete'	=> "new-password"
					) ),
				) )
			) ),
			HTML::DivClass( 'row-fluid password-history', array(
				HTML::DivClass( 'span12', array(
					HTML::Label( '', 'Passwörter bisher' ),
					$history,
				) )
			) ),
//			HTML::BR,
//			$history,
			HTML::DivClass( 'buttonbar', array(
				UI_HTML_Elements::Button( 'savePassword', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
			) ),
		), array( 'autocomplete' => 'off' ) )
	) )
);

/*
$panelPasswords		= '';
$atLeastOne			= TRUE;
if( !$atLeastOne || count( $passwords ) > 1 ){
	$passwordCryptTypes = array_flip( ADT_Constant::getAll( 'PASSWORD_' ) );

	$rows	= array();
	foreach( $passwords as $password ){
		$rowClass	= 'info';
		if( $password->status == 0 )
			$rowClass	= 'warning';
		if( $password->status == 1 )
			$rowClass	= 'success';
		$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
		$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
		$labelStatus	= $words['password-statuses'][$password->status];
		$rows[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			UI_HTML_Tag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			UI_HTML_Tag::create( 'td', $labelStatus ),
//			UI_HTML_Tag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[(int) $password->algo] ) ),
//			UI_HTML_Tag::create( 'td', $password->failsTotal ),
		), array( 'class' => $rowClass ) );
	}
	$panelPasswords	= HTML::DivClass( 'content-panel content-panel-form', array(
		UI_HTML_Tag::create( 'h4', 'Passwörter' ),
		HTML::DivClass( 'content-panel-inner', array(
			UI_HTML_Tag::create( 'table', array(
				UI_HTML_Elements::ColumnGroup( array(
					'120px',
					'120px',
					'100px',
//					'',
//					'',
				) ),
				UI_HTML_Tag::create( 'thead', UI_HTML_Elements::tableHeads( array(
					'erstellt',
					'zuletzt genutzt',
					'Zustand',
//					'Kryptografie',
//					'gescheiterte Login',
				) ) ),
				UI_HTML_Tag::create( 'tbody', $rows )
			), array( 'class' => 'table table-condensed table-fixed' ) )
		) ),
	) );
}*/
return $panelPassword/*.$panelPasswords*/;

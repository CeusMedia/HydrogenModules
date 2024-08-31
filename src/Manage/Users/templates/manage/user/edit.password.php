<?php
use CeusMedia\Common\ADT\Constant as Constants;
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env  */
/** @var array $words */
/** @var object[] $passwords */
/** @var int $userId */
/** @var int $pwdMinLength */
/** @var int $pwdMinStrength */
/** @var bool $atLeastOne */

$pathJsLib	= $env->getConfig()->get( 'path.scripts.lib' );
$env->getPage()->js->addUrl( $pathJsLib.'jquery/pstrength/2.1.0.min.js' );

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
			minCharText:  "'.$words['pstrength']['minimumLength'].'",
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
$env->getPage()->js->addScript( $script );

$atLeastOne		= TRUE;
$history		= '';
if( !$atLeastOne || count( $passwords ) > 1 ){
	$passwordCryptTypes = array_flip( Constants::getAll( 'PASSWORD_' ) );

	$rows	= [];
	foreach( $passwords as $password ){
		$rowClass	= 'info';
		if( $password->status == Model_User_Password::STATUS_NEW )
			$rowClass	= 'warning';
		if( $password->status == Model_User_Password::STATUS_ACTIVE )
			$rowClass	= 'success';
		$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
		$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
		$labelStatus	= $words['password-statuses'][$password->status];
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			HtmlTag::create( 'td', $labelStatus ),
//			HtmlTag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[$password->algo] ) ),
//			HtmlTag::create( 'td', $password->failsTotal ),
		], ['class' => $rowClass] );
	}
	$heads	= HtmlElements::tableHeads( [
		'erstellt',
		'zuletzt genutzt',
		'Zustand',
//		'Kryptografie',
//		'gescheiterte Login',
	] );

	$table	= HtmlTag::create( 'table', [
		HtmlElements::ColumnGroup( [
			'120px',
			'120px',
			'100px',
//					'',
//					'',
		] ),
		HtmlTag::create( 'thead', $heads ),
		HtmlTag::create( 'tbody', $rows )
	], ['class' => 'table table-condensed table-fixed table-bordered'] );
	$history	= HTML::DivClass( 'collapsable-block', [
//		HtmlTag::create( 'h4', 'Historie', ['class' => 'collapsable-block-trigger'] ),
		HTML::DivClass( 'collapsable-block-content', $table ),
	] );
}

$panelPassword	= HTML::DivClass( 'content-panel content-panel-form', [
	HtmlTag::create( 'h3', $w->heading ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/user/password/'.$userId, 'manage_user_password', [
			HTML::DivClass( 'row-fluid', [
				HTML::DivClass( 'span6', [
					HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory', sprintf( $w->labelPasswordNew_title, $pwdMinLength ) ),
					HtmlTag::create( 'input', NULL, [
						'type'			=> "password",
						'name'			=> "passwordNew",
						'id'			=> "input_passwordNew",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'minlength'		=> $pwdMinLength,
						'value'			=> "",
						'autocomplete'	=> "new-password"
					] ),
				] ),
				HTML::DivClass( 'span6', [
					HTML::Label( 'passwordConfirm', $w->labelPasswordConfirm, 'mandatory', $w->labelPasswordConfirm_title ),
					HtmlTag::create( 'input', NULL, [
						'type'			=> "password",
						'name'			=> "passwordConfirm",
						'id'			=> "input_passwordConfirm",
						'class'			=> "span11 mandatory",
						'required'		=> 'required',
						'minlength'		=> $pwdMinLength,
						'value'			=> "",
						'autocomplete'	=> "new-password"
					] ),
				] )
			] ),
			HTML::DivClass( 'row-fluid password-history', [
				HTML::DivClass( 'span12', [
					HTML::Label( '', 'Passwörter bisher' ),
					$history,
				] )
			] ),
//			HTML::BR,
//			$history,
			HTML::DivClass( 'buttonbar', [
				HtmlElements::Button( 'savePassword', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
			] ),
		], ['autocomplete' => 'off'] )
	)
] );

/*
$panelPasswords		= '';
$atLeastOne			= TRUE;
if( !$atLeastOne || count( $passwords ) > 1 ){
	$passwordCryptTypes = array_flip( Constants::getAll( 'PASSWORD_' ) );

	$rows	= [];
	foreach( $passwords as $password ){
		$rowClass	= 'info';
		if( $password->status == 0 )
			$rowClass	= 'warning';
		if( $password->status == 1 )
			$rowClass	= 'success';
		$dateCreated	= date( 'd.m.Y', $password->createdAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->createdAt ).'</small>';
		$dateUsed		= $password->usedAt ? date( 'd.m.Y', $password->usedAt ).'&nbsp;<span class="muted">'.date( 'H:i', $password->usedAt ).'</small>' : '-';
		$labelStatus	= $words['password-statuses'][$password->status];
		$rows[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateCreated.'</small>' ),
			HtmlTag::create( 'td', '<small class="not-muted">'.$dateUsed.'</small>' ),
			HtmlTag::create( 'td', $labelStatus ),
//			HtmlTag::create( 'td', preg_replace( '/^PASSWORD_/', '', $passwordCryptTypes[(int) $password->algo] ) ),
//			HtmlTag::create( 'td', $password->failsTotal ),
		], ['class' => $rowClass] );
	}
	$panelPasswords	= HTML::DivClass( 'content-panel content-panel-form', [
		HtmlTag::create( 'h4', 'Passwörter' ),
		HTML::DivClass( 'content-panel-inner', [
			HtmlTag::create( 'table', [
				HtmlElements::ColumnGroup( [
					'120px',
					'120px',
					'100px',
//					'',
//					'',
				] ),
				HtmlTag::create( 'thead', HtmlElements::tableHeads( [
					'erstellt',
					'zuletzt genutzt',
					'Zustand',
//					'Kryptografie',
//					'gescheiterte Login',
				] ) ),
				HtmlTag::create( 'tbody', $rows )
			], ['class' => 'table table-condensed table-fixed'] )
		] ),
	] );
}*/
return $panelPassword/*.$panelPasswords*/;

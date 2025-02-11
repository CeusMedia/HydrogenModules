<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\View;

/** @var WebEnvironment $env */
/** @var View $view */
/** @var array<string,array<string|int,string|int>> $words */
/** @var int $pwdMinLength */
/** @var int $pwdMinStrength */

$pathJsLib	= $env->getConfig()->get( 'path.scripts.lib' );
$env->getPage()->js->addUrl( $pathJsLib.'jquery/pstrength/2.1.0.min.js' );

//  --  PANEL: PASSWORD  --  //
$w	= (object) $words['password'];

$script		= '
$(document).ready(function(){
	if('.$pwdMinLength.'||'.$pwdMinStrength.'){
		$("#input_passwordNew").pstrength({
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
			colors: ["#f00", "#f60", "#cc0", "#3c0", "#3f0"]
		});
	}
});';
$env->getPage()->js->addScript( $script );

extract( $view->populateTexts( [
	'panel.password.top',
	'panel.password.above',
	'panel.password.info',
	'panel.password.below',
	'panel.password.bottom',
], 'html/manage/my/user/', ['pwdMinLength' => $pwdMinLength] ) );

return HTML::DivClass( 'content-panel content-panel-form', array(
	HtmlTag::create( 'h4', $w->legend ),
	HTML::DivClass( 'content-panel-inner',
		HTML::Form( './manage/my/user/password', 'my_user_password', array(
			$textPanelPasswordAbove ? HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $textPanelPasswordAbove ) ) : '',
			HTML::DivClass( 'row-fluid', array(
				HTML::DivClass( 'span6', array(
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'passwordNew', $w->labelPasswordNew, 'mandatory', sprintf( $w->labelPasswordNew_title, $pwdMinLength ) ),
							HtmlTag::create( 'input', NULL, [
								'type'			=> "password",
								'name'			=> "passwordNew",
								'id'			=> "input_passwordNew",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> "",
								'autocomplete'	=> "new-password"
							] ),
						) )
					),
					HTML::DivClass( 'row-fluid',
						HTML::DivClass( 'span12', array(
							HTML::Label( 'passwordConfirm', $w->labelPasswordConfirm, 'mandatory', $w->labelPasswordConfirm_title ),
							HtmlTag::create( 'input', NULL, [
								'type'			=> "password",
								'name'			=> "passwordConfirm",
								'id'			=> "input_passwordConfirm",
								'class'			=> "span11 mandatory",
								'required'		=> 'required',
								'value'			=> "",
								'autocomplete'	=> "new-password"
							] ),
						) )
					),
				) ),
				HTML::DivClass( 'span6', $textPanelPasswordInfo ),
				$textPanelPasswordBelow ? HTML::DivClass( 'row-fluid', HTML::DivClass( 'span12', $textPanelPasswordBelow ) ) : '',
			) ),
			HTML::Buttons( array(
				HtmlTag::create( 'small', $w->labelPasswordCurrent_title, ['class' => 'not-muted'] ),
				HTML::DivClass( 'row-fluid',
					HTML::DivClass( 'span6', array(
						HTML::DivClass( 'input-prepend input-append',
							HTML::SpanClass( 'add-on', '<i class="fa fa-fw fa-lock"></i>' ).
							HtmlTag::create( 'input', '', [
								'type'			=> 'password',
								'name'			=> 'passwordOld',
								'id'			=> 'input_passwordOld',
								'class'			=> 'span7',
								'required'		=> 'required',
								'autocomplete'	=> 'current-password',
								'placeholder'	=> $w->labelPasswordCurrent,
							] ).
							HtmlElements::Button( 'saveUser', '<i class="fa fa-fw fa-check"></i> '.$w->buttonSave, 'btn btn-primary' )
						)
					) )
				)
			) )
		), ['autocomplete' => 'off'] )
	) )
).'
<style>
#password-strength {
	width: 90% !important;
	margin-top: 0;
	margin-bottom: 0.5em;
	background-color: #EFEFEF;
	border: 1px solid #CFCFCF;
	font-size: 0.9em;
	overflow: hidden;
	min-height: 1em;
	border-radius: 0.6em;
	}
#password-strength .password-strength-bar {
	width: 100% !important;
	padding-top: 0.1em;
	text-align: center;
	font-size: 0.9em;
	color: rgba( 0,0,0,0.75);
	opacity: 1 !important;
	}

</style>
';

<?php
$w			= (object) $words['index'];

if( !class_exists( "XHTML" ) )
	new CMF_Hydrogen_View_Helper_HTML();

$optGender	= HTML::Options( $words['gender'], $user->gender );

$env->page->js->addUrl( 'http://js.ceusmedia.de/jquery/pstrength/2.1.0.min.js' );

$script		= '
$(document).ready(function(){
	if('.$pwdMinLength.'||'.$pwdMinStrength.'){
		$("form :input#password").pstrength({
			minChar: '.$pwdMinLength.',
			displayMinChar: '.$pwdMinLength.',
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
});
';
$env->page->js->addScript( $script );

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

//  --  PANEL: INFO  --  //
$loggedAt		= CMF_Hydrogen_View_Helper_Timestamp::statePhrase( $user->loggedAt, $env, TRUE );
$activeAt		= CMF_Hydrogen_View_Helper_Timestamp::statePhrase( $user->activeAt, $env, TRUE );
$createdAt		= CMF_Hydrogen_View_Helper_Timestamp::statePhrase( $user->createdAt, $env, TRUE );

$mapInfo	= array();
if( $config->get( 'module.roles' ) )
	$mapInfo['Rolle']	= '<span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span>';
if( !empty( $user->company ) ){
	$link	= HTML::Link( './manage/my/company', $user->company->title );
	$mapInfo['Unternehmen']	= '<span class="company">'.$link.'</span>';
}
$mapInfo['Status']	= '<span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span>';

$listInfo	= array();
foreach( $mapInfo as $term => $definition )
	$listInfo[]	= UI_HTML_Tag::create( 'dt', $term ).UI_HTML_Tag::create( 'dd', $definition );
$listInfo	= UI_HTML_Tag::create( 'dl', join( $listInfo ) );

$mapTimes	= array();
$mapTimes['registriert']		= $createdAt;
$mapTimes['zuletzt eingeloggt']	= $loggedAt;
$mapTimes['zuletzt aktiv']		= $activeAt;

$listTimes	= array();
foreach( $mapTimes as $term => $definition )
	$listTimes[]	= UI_HTML_Tag::create( 'dt', $term ).UI_HTML_Tag::create( 'dd', $definition );
$listTimes	= UI_HTML_Tag::create( 'dl', join( $listTimes ) );

$panelInfo		= HTML::Fields(
	HTML::Legend( 'Kontoinformationen', 'info' ).
	$listInfo.
	'<hr/>'.
	$listTimes
);

//  --  PANEL: PASSWORD  --  //
$panelPassword	= HTML::Form( './manage/my/user/password', 'my_user_password',
	HTML::Fields(
		HTML::Legend( $words['password']['legend'], 'edit my user password' ).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span6', 
				HTML::Label( 'passwordOld', $words['password']['labelPassword'], 'mandatory' ).
				HTML::Password( 'passwordOld', 'span12 mandatory' )
			).
			HTML::DivClass( 'span6',
				HTML::Label( 'passwordNew', $words['password']['labelPasswordNew'], 'mandatory' ).
				HTML::Input( 'passwordNew', NULL, 'span12 mandatory' )
			)
		).
		HTML::Buttons(
			HTML::Button( 'savePassword', '<i class="icon-ok icon-white"></i> '.$words['password']['buttonSave'], 'btn btn-small btn-success' )
		)
	)
);


$panelEdit	= HTML::Form( './manage/my/user/edit', 'my_user_edit',
	HTML::Fields(
		HTML::Legend( $w->legend, 'edit my user' ).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span4',
				HTML::Label( 'username', $w->labelUsername, 'mandatory' ).
				HTML::DivClass( 'input-prepend',
					HTML::SpanClass( 'add-on', '<i class="icon-user"></i>' ).
					HTML::Input( 'username', $user->username, 'span11 mandatory' )
				)
			).
			HTML::DivClass( 'span8',
				HTML::Label( 'email', $w->labelEmail, 'mandatory' ).
				HTML::DivClass( 'input-prepend',
					HTML::SpanClass( 'add-on', '<i class="icon-envelope"></i>' ).
					HTML::Input( 'email', $user->email, 'span11 mandatory' )
				)
			)
		).
		'<hr/>'.
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span2',
				HTML::Label( 'gender', $w->labelGender, '' ).
				HTML::Select( 'gender', $optGender, 'span12' )
			).
			HTML::DivClass( 'span2',
				HTML::Label( 'salutation', $w->labelSalutation, '' ).
				HTML::Input( 'salutation', $user->salutation, 'span12' )
			).
			HTML::DivClass( 'span4',
				HTML::Label( 'firstname', $w->labelFirstname, '' ).
				HTML::Input( 'firstname', $user->firstname, 'span12' )
			).
			HTML::DivClass( 'span4',
				HTML::Label( 'surname', $w->labelSurname, '' ).
				HTML::Input( 'surname', $user->surname, 'span12' )
			)
		).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span2',
				HTML::Label( 'postcode', $w->labelPostcode, '' ).
				HTML::Input( 'postcode', $user->postcode, 'span12 numeric' )
			).
			HTML::DivClass( 'span3',
				HTML::Label( 'city', $w->labelCity, '' ).
				HTML::Input( 'city', $user->city, 'span12' )
			).
			HTML::DivClass( 'span5',
				HTML::Label( 'street', $w->labelStreet, '' ).
				HTML::Input( 'street', $user->street, 'span12' )
			).
			HTML::DivClass( 'span2',
				HTML::Label( 'number', $w->labelNumber, '' ).
				HTML::Input( 'number', $user->number, 'span12 numeric' )
			)
		).
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span3',
				HTML::Label( 'phone', $w->labelPhone ).
				HTML::Input( 'phone', $user->phone, 'span12' )
			).
			HTML::DivClass( 'span3',
				HTML::Label( 'fax', $w->labelFax ).
				HTML::Input( 'fax', (string) $user->fax, 'span12' )
			)
		).
/*		'<hr/>'.
		HTML::DivClass( 'row-fluid',
			HTML::DivClass( 'span3',
				HTML::Label( 'password', $w->labelPassword, 'mandatory' ).
			)
		).
*/		HTML::Buttons(
			HTML::DivClass( 'row-fluid',
				HTML::DivClass( 'span6',
					HTML::DivClass( 'input-prepend input-append',
						HTML::SpanClass( 'add-on', '<i class="icon-lock"></i>' ).
						'<input type="password" name="password" id="input_password" class="span7" required placeholder="'.$w->labelPassword.'" value="" autocomplete="off"/>'.
//						HTML::Password( 'password', 'span11 mandatory' )
						HTML::Button( 'saveUser', '<i class="icon-ok icon-white"></i> '.$w->buttonSave, 'btn btn-success' )
					)
				)
			)
//			HTML::Button( 'saveUser', $w->buttonSave, 'button save' )
		)
	)
);

$panelSettings	= '';
if( $env->getModules()->has( 'Manage_My_User_Setting' ) ){
	$helper			= new View_Helper_UserModuleSettings( $env, './manage/my/user' );
	$panelSettings	= $helper->renderPanel( './manage/my/user' );
}

$panelAvatar	= '';
#if( 1 ){
#	$panelAvatar	= '';
#}

return /*UI_HTML_Tag::create( 'h2', $w->heading ).*/
HTML::DivClass( 'column-right-33',
	$panelInfo.
	$panelPassword.
	$panelAvatar
).
HTML::DivClass( 'column-left-66',
	$panelEdit.
	$panelSettings
).
HTML::DivClass( 'column-clear' );
?>

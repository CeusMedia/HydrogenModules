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
		HTML::UlClass( 'input',
			HTML::Li(
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'passwordOld', $words['password']['labelPassword'], 'mandatory' ).
					HTML::Password( 'passwordOld', 'max mandatory' )
				).
				HTML::DivClass( 'column-left-50',
					HTML::Label( 'passwordNew', $words['password']['labelPasswordNew'], 'mandatory' ).
					HTML::Input( 'passwordNew', NULL, 'max mandatory' )
				).
				HTML::DivClass( 'column-clear' )
			)
		).
		HTML::Buttons(
			HTML::Button( 'savePassword', $words['password']['buttonSave'], 'button save' )
		)
	)
);


$panelEdit	= HTML::Form( './manage/my/user/edit', 'my_user_edit',
	HTML::Fields(
		HTML::Legend( $w->legend, 'edit my user' ).
		HTML::UlClass( 'input',
			HTML::Li(
				HTML::DivClass( 'column-left-33',
					HTML::Label( 'username', $w->labelUsername, 'mandatory' ).HTML::BR.
					HTML::Input( 'username', $user->username, 'max mandatory' )
				).
				HTML::DivClass( 'column-left-66',
					HTML::Label( 'email', $w->labelEmail, 'mandatory' ).HTML::BR.
					HTML::Input( 'email', $user->email, 'max mandatory' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li( '<hr/>'
			).
			HTML::Li(
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'gender', $w->labelGender, '' ).HTML::BR.
					HTML::Select( 'gender', $optGender, 'max' )
				).
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'salutation', $w->labelSalutation, '' ).HTML::BR.
					HTML::Input( 'salutation', $user->salutation, 'max' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'firstname', $w->labelFirstname, '' ).HTML::BR.
					HTML::Input( 'firstname', $user->firstname, 'max' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'surname', $w->labelSurname, '' ).HTML::BR.
					HTML::Input( 'surname', $user->surname, 'max' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li(
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'postcode', $w->labelPostcode, '' ).HTML::BR.
					HTML::Input( 'postcode', $user->postcode, 'max' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'city', $w->labelCity, '' ).HTML::BR.
					HTML::Input( 'city', $user->city, 'max' )
				).
				HTML::DivClass( 'column-left-30',
					HTML::Label( 'street', $w->labelStreet, '' ).HTML::BR.
					HTML::Input( 'street', $user->street, 'max' )
				).
				HTML::DivClass( 'column-left-20',
					HTML::Label( 'number', $w->labelNumber, '' ).HTML::BR.
					HTML::Input( 'number', $user->number, 'max' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li(
				HTML::DivClass( 'column-left-25',
					HTML::Label( 'phone', $w->labelPhone ).HTML::BR.
					HTML::Input( 'phone', $user->phone, 'max' )
				).
				HTML::DivClass( 'column-left-25',
					HTML::Label( 'fax', $w->labelFax ).HTML::BR.
					HTML::Input( 'fax', $user->fax, 'max' )
				).
				HTML::DivClass( 'column-clear' )
			).
			HTML::Li( '<hr/>'
			).
			HTML::Li(
				HTML::Label( 'password', $w->labelPassword, 'mandatory' ).HTML::BR.
				HTML::Password( 'password', 'm mandatory' )
			)
		).
		HTML::Buttons(
			HTML::Button( 'saveUser', $w->buttonSave, 'button save' )
		)
	)
);

$panelSettings	= '';
if( $env->getModules()->has( 'Manage_My_User_Setting' ) ){
	$helper			= new View_Helper_UserModuleSettings( $env, './manage/my/user' );
	$panelSettings	= $helper->renderPanel( './manage/my/user' );
}

return /*UI_HTML_Tag::create( 'h2', $w->heading ).*/
HTML::DivClass( 'column-right-33',
	$panelInfo.
	$panelPassword
).
HTML::DivClass( 'column-left-66',
	$panelEdit.
	$panelSettings
).
HTML::DivClass( 'column-clear' );
?>

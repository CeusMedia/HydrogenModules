<?php
$w				= (object) $words['edit'];
//$helperAge		= new View_Helper_TimePhraser( $env );

$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconList		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-list' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-remove icon-white' ) );
$iconGroup		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-search' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconList		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconSave		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-fw fa-check' ) );
	$iconRemove		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-remove' ) );
	$iconGroup		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-users' ) );
}

/*
 *	@deprecated		not used. nice feature but no styling done.
 *	@todo			style and apply or remove
#$pwdMinLength		= 3;
#$pwdMinStrength	= 20;
$script		= '
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
	}';
$env->page->js->addUrl( 'http://js.ceusmedia.de/jquery/pstrength/2.1.0.min.js' );
$env->page->js->addScriptOnReady( $script );
*/

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role;

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$optRole	= array();
foreach( array_reverse( $roles, TRUE ) as $role )
	$optRole[]	= UI_HTML_Elements::Option( $role->roleId, $role->title, $role->roleId == $user->roleId, NULL, 'role role'.$role->roleId );
$optRole	= join( $optRole );

$optStatus  = array();
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]    = UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus  = join( $optStatus );

$optGender	= UI_HTML_Elements::Options( $words['gender'], $user->gender );

$buttonList			= UI_HTML_Elements::LinkButton( $from ? $from : './manage/user', $iconList.'&nbsp;'.$w->buttonList, 'btn not-btn-small' );
$buttonSave			= UI_HTML_Elements::Button( 'saveUser', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-primary' );

$buttonRemove		= '';
if( $env->getAcl()->has( 'manage/user', 'remove' ) ){
	$buttonRemove		= UI_HTML_Elements::LinkButton(
		'./manage/user/remove/'.$userId,
		$iconRemove.'&nbsp;'.$w->buttonRemove,
		'btn btn-mini btn-danger',
		$w->buttonRemoveConfirm
	);
}

$buttonRole	= '';
if( $env->getAcl()->has( 'manage/role', 'edit' ) ){
	$buttonRole	= UI_HTML_Tag::create( 'a', $iconGroup.'&nbsp;'.$w->buttonRole, array(
		'class'	=> 'btn btn-small btn-info',
		'href'	=> './manage/role/edit/'.$user->roleId
	) );
}

$panelEdit	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="editUser" action="./manage/user/edit/'.$userId.'" method="post">
			<input type="hidden" name="from" value="'.$from.'"/>
			<div class="bs2-row-fluid bs3-row bs4-row">
<!--			<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">-->
				<div class="bs2-span3 bs3-col-md-3 bs3-form-group bs4-col-md-3 bs4-form-group">
					<label for="input_username" class="mandatory">'.$w->labelUsername.'</label>
					<input type="text" name="username" id="input_username" class="bs2-span12 bs3-form-control bs4-form-control mandatory" autocomplete="off" value="'.htmlentities( $user->username, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
<!--				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_password" class="">'.$w->labelPassword.'</label>
					<input type="password" name="password" id="input_password" autocomplete="new-password" class="bs2-span12 bs3-form-control bs4-form-control"/>
				</div>-->
<!--				<div class="bs2-span4 bs3-col-md-4 bs3-form-group bs4-col-md-4 bs4-form-group">-->
				<div class="bs2-span6 bs3-col-md-6 bs3-form-group bs4-col-md-6 bs4-form-group">
					<label for="input_email" class="'.( $needsEmail ? 'mandatory' : '' ).'">'.$w->labelEmail.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> "text",
						'name'		=> "email",
						'id'		=> "input_email",
						'class'		=> "bs2-span12 bs3-form-control bs4-form-control ".( $needsEmail ? 'mandatory' : '' ),
						'value'		=> htmlentities( $user->email, ENT_QUOTES, 'UTF-8' ),
						'required'	=> $needsEmail ? "required" : NULL
					) ).'
				</div>
<!--				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="bs2-span12 bs3-form-control bs4-form-control mandatory">'.$optStatus.'</select>
				</div>-->
<!--				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">-->
				<div class="bs2-span3 bs3-col-md-3 bs3-form-group bs4-col-md-3 bs4-form-group">
					<label for="input_roleId" class="mandatory">'.$w->labelRole.'</label>
					<select name="roleId" id="input_roleId" class="bs2-span12 bs3-form-control bs4-form-control mandatory">'.$optRole.'</select>
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_gender" class="">'.$w->labelGender.'</label>
					<select name="gender" id="input_gender" class="bs2-span12 bs3-form-control bs4-form-control">'.$optGender.'"</select>
				</div>
				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_salutation" class="">'.$w->labelSalutation.'</label>
					<input type="text" name="salutation" id="input_salutation" class="bs2-span12 bs3-form-control bs4-form-control" value="'.$user->salutation.'"/>
				</div>
				<div class="bs2-span4 bs3-col-md-4 bs3-form-group bs4-col-md-4 bs4-form-group">
					<label for="input_firstname" class="'.( $needsFirstname ? 'mandatory' : '' ).'">'.$w->labelFirstname.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> "text",
						'name'		=> "firstname",
						'id'		=> "input_firstname",
						'class'		=> "bs2-span12 bs3-form-control bs4-form-control ".( $needsFirstname ? 'mandatory' : '' ),
						'value'		=> htmlentities( $user->firstname, ENT_QUOTES, 'UTF-8' ),
						'required'	=> $needsFirstname ? "required" : NULL
					) ).'
				</div>
				<div class="bs2-span4 bs3-col-md-4 bs3-form-group bs4-col-md-4 bs4-form-group">
					<label for="input_surname" class="'.( $needsSurname ? 'mandatory' : '' ).'">'.$w->labelSurname.'</label>
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> "text",
						'name'		=> "surname",
						'id'		=> "input_surname",
						'class'		=> "bs2-span12 bs3-form-control bs4-form-control ".( $needsSurname ? 'mandatory' : '' ),
						'value'		=> htmlentities( $user->surname, ENT_QUOTES, 'UTF-8' ),
						'required'	=> $needsSurname ? "required" : NULL
					) ).'
				</div>
			</div>
			<div class="bs2-row-fluid bs3-row bs4-row">
				<div class="bs2-span3 bs3-col-md-3 bs3-form-group bs4-col-md-3 bs4-form-group">
					<label for="input_country" class="">'.$w->labelCountry.'</label>
					<input type="text" name="country" id="input_country" class="bs2-span12 bs3-form-control bs4-form-control typeahead" data-provide="typeahead" autocomplete="off" value="'.$user->country.'"/>
				</div>
				<div class="bs2-span2 bs3-col-md-2 bs3-form-group bs4-col-md-2 bs4-form-group">
					<label for="input_postcode" class="">'.$w->labelPostcode.'</label>
					<input type="text" name="postcode" id="input_postcode" class="bs2-span12 bs3-form-control bs4-form-control" value="'.$user->postcode.'"/>
				</div>
				<div class="bs2-span3 bs3-col-md-3 bs3-form-group bs4-col-md-3 bs4-form-group">
					<label for="input_city" class="">'.$w->labelCity.'</label>
					<input type="text" name="city" id="input_city" class="bs2-span12 bs3-form-control bs4-form-control" value="'.$user->city.'"/>
				</div>
				<div class="bs2-span4 bs3-col-md-4 bs3-form-group bs4-col-md-4 bs4-form-group">
					<label for="input_street" class="">'.$w->labelStreet.'</label>
					<input type="text" name="street" id="input_street" class="bs2-span12 bs3-form-control bs4-form-control" value="'.$user->street.'"/>
				</div>
			</div>
			<div class="buttonbar">
				<div class="bs2-btn-toolbar bs3-btn-toolbar">
					'.$buttonList.'
					'.$buttonSave.'
					'.$buttonRole.'
					'.$buttonRemove.'
				</div>
			</div>
		</form>
	</div>
</div>';

$panelStatus	= $this->loadTemplateFile( 'manage/user/edit.status.php' );
$panelPassword	= $this->loadTemplateFile( 'manage/user/edit.password.php' );
$panelInfo		= $this->loadTemplateFile( 'manage/user/edit.info.php' );
$panelRights	= $this->loadTemplateFile( 'manage/user/edit.rights.php' );

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/user/' ) );

return $textIndexTop.'
<div class="bs2-row-fluid bs3-row bs4-row">
	<div class="bs2-span9 bs3-col-md-9 bs4-col-md-9">
		'.$panelEdit.'
		<div class="bs2-row-fluid bs3-row bs4-row">
			<div class="bs2-span6 bs3-col-md-6 bs4-col-md-6">
				'.$panelStatus.'
				'.$panelPassword.'
			</div>
			<div class="bs2-span6 bs3-col-md-6 bs4-col-md-6">
				'.$panelRights.'
			</div>
		</div>
	</div>
	<div class="bs2-bs2-span3 bs3-col-md-3 bs4-col-md-3">
		'.$panelInfo.'
	</div>
</div>
'.$textIndexBottom;

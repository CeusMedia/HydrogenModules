<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w				= (object) $words['add'];

$iconCancel		= HtmlTag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );
$iconList		= HtmlTag::create( 'i', '', array( 'class' => 'icon-list' ) );
$iconSave		= HtmlTag::create( 'i', '', array( 'class' => 'icon-ok icon-white' ) );
if( $env->getModules()->get( 'UI_Font_FontAwesome' ) ){
	$iconCancel		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-arrow-left' ) );
	$iconList		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-list' ) );
	$iconSave		= HtmlTag::create( 'b', '', array( 'class' => 'fa fa-fw fa-check' ) );
}

$roleMap	= [];
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role->title;

/*
 *	@deprecated		not used. nice feature but no styling done.
 *	@todo			style and apply or remove
#$pwdMinLength		= 3;
#$pwdMinStrength	= 20;
$script	= '
	if('.(int) $pwdMinLength.'||'.(int) $pwdMinStrength.'){
		$("#input_password").pstrength({
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
';

$page	= $env->getPage();
$page->js->addUrl( 'https://cdn.ceusmedia.de/js/jquery/pstrength/2.1.0.min.js', TRUE );
$page->js->addScriptOnReady( $script );
*/

$optStatus		= HtmlElements::Options( array_reverse( $words['status'], TRUE ), @$user->status );
$optRole		= HtmlElements::Options( array_reverse( $roleMap, TRUE ), @$user->roleId );
$optGender		= HtmlElements::Options( $words['gender'], $user->gender );

$buttonList		= HtmlElements::LinkButton( './manage/user', $iconCancel.'&nbsp;'.$w->buttonList, 'btn not-btn-small' );
$buttonSave		= HtmlElements::Button( 'saveUser', $iconSave.'&nbsp;'.$w->buttonSave, 'btn btn-primary' );

$panelAdd	= '
<div class="content-panel">
	<h3>'.$w->heading.'</h3>
	<div class="content-panel-inner">
		<form name="editUser" action="./manage/user/add" method="post" autocomplete="off">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_username" class="mandatory">'.$w->labelUsername.'</label>
					<input type="text" name="username" id="input_username" class="span12 mandatory" value="'.$user->username.'" autocomplete="off" required="required"/>
				</div>
				<div class="span2">
					<label for="input_password" class="mandatory">'.$w->labelPassword.'</label>
					<input type="password" name="password" id="input_password" class="span12" required="required" autocomplete="new-password"/>
				</div>
				<div class="span4">
					<label for="input_email" class="mandatory">'.$w->labelEmail.'</label>
					<input type="text" name="email" id="input_email" class="span12 mandatory" value="'.$user->email.'" required="required"/>
				</div>
				<div class="span2">
					<label for="input_status" class="mandatory">'.$w->labelStatus.'</label>
					<select name="status" id="input_status" class="span12 mandatory">'.$optStatus.'</select>
				</div>
				<div class="span2">
					<label for="input_roleId" class="mandatory">'.$w->labelRole.'</label>
					<select name="roleId" id="input_roleId" class="span12 mandatory">'.$optRole.'</select>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span2">
					<label for="input_gender" class="">'.$w->labelGender.'</label>
					<select name="gender" id="input_gender" class="span12">'.$optGender.'"</select>
				</div>
				<div class="span2">
					<label for="input_salutation" class="">'.$w->labelSalutation.'</label>
					<input type="text" name="salutation" id="input_salutation" class="span12" value="'.$user->salutation.'"/>
				</div>
				<div class="span4">
					<label for="input_firstname" class="mandatory">'.$w->labelFirstname.'</label>
					<input type="text" name="firstname" id="input_firstname" class="span12" value="'.$user->firstname.'" required="required"/>
				</div>
				<div class="span4">
					<label for="input_surname" class="mandatory">'.$w->labelSurname.'</label>
					<input type="text" name="surname" id="input_surname" class="span12" value="'.$user->surname.'" required="required"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_country" class="">'.$w->labelCountry.'</label>
					<input type="text" name="country" id="input_country" class="span12 typeahead" data-provide="typeahead" autocomplete="off" value="'.$user->country.'"/>
				</div>
				<div class="span2">
					<label for="input_postcode" class="">'.$w->labelPostcode.'</label>
					<input type="text" name="postcode" id="input_postcode" class="span12" value="'.$user->postcode.'"/>
				</div>
				<div class="span3">
					<label for="input_city" class="">'.$w->labelCity.'</label>
					<input type="text" name="city" id="input_city" class="span12" value="'.$user->city.'"/>
				</div>
				<div class="span4">
					<label for="input_street" class="">'.$w->labelStreet.'</label>
					<input type="text" name="street" id="input_street" class="span12" value="'.$user->street.'"/>
				</div>
			</div>
			<div class="buttonbar">
				'.$buttonList.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>
';

$panelInfo	= '';

extract( $view->populateTexts( array( 'index.top', 'index.bottom' ), 'html/manage/user/' ) );

return $textIndexTop.'
<div class="row-fluid">
	<div class="span9">
		'.$panelAdd.'
	</div>
	<div class="span3">
		'.$panelInfo.'
	</div>
</div>
'.$textIndexBottom;


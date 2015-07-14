 <?php

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

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role;

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$optStatus	= array();
foreach( array_reverse( $words['status'], TRUE ) as $key => $label )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );


$panelStatus	= '
<form name="editUserStates" action="./user/edit/'.$userId.'" method="post">
	<fieldset>
		<legend>'.$words['editStatus']['legend'].'</legend>
		<ul class="input">
<!--			<li>
				<label for="status">'.$words['editStatus']['labelStatus'].'</label><br/>
				'.UI_HTML_Elements::Select( 'status', $optStatus, 'm', TRUE ).'
			</li>-->
			<li>
				<label for="status">'.$words['editStatus']['labelStatus'].'</label><br/>
				'.UI_HTML_Elements::Input( 'status', $words['status'][$user->status], 'label m user-status status'.$user->status, TRUE ).'
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton(
				'./user/accept/'.$userId,
					$words['editStatus']['buttonAccept'],
				'button edit',
				$words['editStatus']['buttonAcceptConfirm'],
				$user->status == 1
			).'
			'.UI_HTML_Elements::LinkButton(
				'./user/ban/'.$userId,
				$words['editStatus']['buttonBan'],
				'button lock',
				$words['editStatus']['buttonBanConfirm'],
				$user->status != 1
			).'
			'.UI_HTML_Elements::LinkButton(
				'./user/disable/'.$userId,
				$words['editStatus']['buttonDisable'],
				'button remove',
				$words['editStatus']['buttonDisableConfirm'],
				$user->status == -2
			).'
		</div>
	</fieldset>
</form>';

$facts	= array();

$createdAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->createdAt );
$facts[]	= array(
	'label'	=> 'registriert',
	'value'	=> $createdAt->toPhrase( $env, TRUE )
);
if( $user->loggedAt ){
	$loggedAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->loggedAt );
	$facts[]	= array(
		'label'	=> 'zuletzt eingeloggt',
		'value'	=> $loggedAt->toPhrase( $env, TRUE )
	);
}
if( $user->activeAt ){
	$activeAt	= new CMF_Hydrogen_View_Helper_Timestamp( $user->activeAt );
	$facts[]	= array(
		'label'	=> 'zuletzt aktiv',
		'value'	=> $activeAt->toPhrase( $env, TRUE )
	);
}
if( !empty( $projects ) ){
	$list	= array();
	foreach( $projects as $project ){
		$url	= './manage/project/edit/'.$project->projectId;
		$link	= UI_HTML_Tag::create( 'a', $project->title, array( 'href' => $url, 'class' => 'project' ) );
		$list[]	= UI_HTML_Tag::create( 'li', $link );
	}
	$projects	= UI_HTML_Tag::create( 'ul', join( $list ), array( 'class' => 'projects' ) );
	$facts[]	= array(
		'label'	=> 'Projekte',
		'value'	=> $projects
	);
}

foreach( $facts as $nr => $fact )
	$facts[$nr]	= UI_HTML_Tag::create( 'dt', $fact['label'] ).UI_HTML_Tag::create( 'dd', $fact['value'] );
$facts	= UI_HTML_Tag::create( 'dl', join( $facts ) );

$panelInfo	= '
<fieldset>
	<legend class="info">Info: Konto</legend>
	<dl>
		<dt>Rolle</dt>
		<dd><span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span></dd>
		<dt>Status</dt>
		<dd><span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span></dd>
	</dl>
	<hr>
	'.$facts.'
</fieldset>
';

$w	= (object) $words['edit'];


$optRole	= array();
foreach( array_reverse( $roles, TRUE ) as $role )
	$optRole[]	= UI_HTML_Elements::Option( $role->roleId, $role->title, $role->roleId == $user->roleId, NULL, 'role role'.$role->roleId );
$optRole	= join( $optRole );

$optGender	= UI_HTML_Elements::Options( $words['gender'], $user->gender );

$panelEdit	= '
<form name="editUser" action="./admin/user/edit/'.$userId.'" method="post">
	<fieldset>
		<legend class="icon edit user-edit">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-50">
				<label for="email" class="mandatory">'.$w->labelEmail.'</label><br/>
				<input type="text" name="email" id="input_email" class="max mandatory" value="'.$user->email.'"/>
			</li>
			<li class="column-left-25">
				<label for="username" class="mandatory">'.$w->labelUsername.'</label><br/>
				<input type="text" name="username" id="input_username" class="max mandatory" value="'.$user->username.'"/>
			</li>
			<li class="column-left-25">
				<label for="password">'.$w->labelPassword.'</label><br/>
				<input type="password" name="password" id="input_password" class="max"/>
			</li>

			<li class="column-clear column-left-20">
				<label for="status" class="mandatory">'.$w->labelStatus.'</label><br/>
				<select name="status" id="input_status" class="max mandatory">'.$optStatus.'</select>
			</li>
			<li class="column-left-20">
				<label for="roleId" class="mandatory">'.$w->labelRole.'</label><br/>
				<select name="roleId" id="input_roleId" class="max mandatory">'.$optRole.'</select>
			</li>

			<li class="column-clear column-left-20">
				<label for="input_gender" class="">'.$w->labelGender.'</label><br/>
				<select name="gender" id="input_gender" class="max">'.$optGender.'"</select>
			</li>
			<li class="column-left-20">
				<label for="input_salutation" class="">'.$w->labelSalutation.'</label><br/>
				<input type="text" name="salutation" id="input_salutation" class="max" value="'.$user->salutation.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_firstname" class="">'.$w->labelFirstname.'</label><br/>
				<input type="text" name="firstname" id="input_firstname" class="max" value="'.$user->firstname.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_surname" class="">'.$w->labelSurname.'</label><br/>
				<input type="text" name="surname" id="input_surname" class="max" value="'.$user->surname.'"/>
			</li>

			<li class="column-clear column-left-20">
				<label for="input_postcode" class="">'.$w->labelPostcode.'</label><br/>
				<input type="text" name="postcode" id="input_postcode" class="max" value="'.$user->postcode.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_city" class="">'.$w->labelCity.'</label><br/>
				<input type="text" name="city" id="input_city" class="max" value="'.$user->city.'"/>
			</li>
			<li class="column-left-30">
				<label for="input_street" class="">'.$w->labelStreet.'</label><br/>
				<input type="text" name="street" id="input_street" class="max" value="'.$user->street.'"/>
			</li>
			<li class="column-left-20">
				<label for="input_number" class="">'.$w->labelNumber.'</label><br/>
				<input type="text" name="number" id="input_number" class="max" value="'.$user->number.'"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/user', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'saveUser', $w->buttonSave, 'button save' ).'
			&nbsp;&nbsp;|&nbsp;&nbsp;
			'.UI_HTML_Elements::LinkButton(
				'./admin/user/remove/'.$userId,
				$w->buttonRemove,
				'button remove',
				$w->buttonRemoveConfirm
			).'
		</div>
	</fieldset>
</form>
';

return '
<div class="column-control">
	'.$panelStatus.'
	'.$panelInfo.'
</div>
<div class="column-main">
	'.$panelEdit.'
</div>
<div style="clear: both"></div>
';
?>

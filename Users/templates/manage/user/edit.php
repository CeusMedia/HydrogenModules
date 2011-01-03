<?php
$optStatus	= array();
foreach( $words['status'] as $key => $label )
	$optStatus[]	= UI_HTML_Elements::Option( (string) $key, $label, $key == $user->status, NULL, 'user-status status'.$key );
$optStatus	= join( $optStatus );

$this->env->page->js->addUrl( 'http://js.ceusmedia.de/jquery/pstrength/2.1.0.min.js' );

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
$this->env->page->js->addScript( $script );

$roleMap	= array();
foreach( $roles as $role )
	$roleMap[$role->roleId] = $role;

$optRole	= array();
foreach( array_reverse( $roles ) as $role )
	$optRole[]	= UI_HTML_Elements::Option( $role->roleId, $role->title, $role->roleId == $user->roleId, NULL, 'role role'.$role->roleId );
$optRole	= join( $optRole );

/* TO BE USED LATER FOR STATUS INFO
$indicator	= new UI_HTML_Indicator();
$indicator->setIndicatorClass( 'indicator-small' );
$ind1		= $indicator->build( 75, 100 );
*/

$loggedAt		= new CMF_Hydrogen_View_Helper_Timestamp( $user->loggedAt );
$loggedAt		= $loggedAt->toPhrase( $this->env, TRUE );
$activeAt		= new CMF_Hydrogen_View_Helper_Timestamp( $user->activeAt );
$activeAt		= $activeAt->toPhrase( $this->env, TRUE );
$createdAt		= new CMF_Hydrogen_View_Helper_Timestamp( $user->createdAt );
$createdAt		= $createdAt->toPhrase( $this->env, TRUE );

return '
<div class="column-control">
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
	</form>
	<fieldset>
		<legend class="info">Info: Konto</legend>
		<dl>
			<dt>Rolle</dt>
			<dd><span class="role role'.$user->role->roleId.'">'.$user->role->title.'</span></dd>
			<dt>Status</dt>
			<dd><span class="user-status status'.$user->status.'">'.$words['status'][$user->status].'</span></dd>
		</dl>
		<hr>
		<dl>
			<dt>registriert</dt>
			<dd>'.$createdAt.'</dd>
			<dt>zuletzt eingeloggt</dt>
			<dd>'.$loggedAt.'</dd>
			<dt>zuletzt aktiv</dt>
			<dd>'.$activeAt.'</dd>
		</dl>
	</fieldset>
</div>
<div class="column-main">
	<form name="editUser" action="./manage/user/edit/'.$userId.'" method="post">
		<fieldset>
			<legend class="edit">'.$words['edit']['legend'].'</legend>
			<ul class="input">
				<li>
					<label for="username">'.$words['edit']['labelUsername'].'</label><br/>
					'.UI_HTML_Elements::Input( 'username', $user->username, 'm' ).'
				</li>
				<li>
					<label for="email">'.$words['edit']['labelEmail'].'</label><br/>
					'.UI_HTML_Elements::Input( 'email', $user->email, 'l' ).'
				</li>
				<li>
					<label for="password">'.$words['edit']['labelPassword'].'</label><br/>
					'.UI_HTML_Elements::Password( 'password', 'm' ).'
				</li>
				<li>
					<label for="status">'.$words['edit']['labelStatus'].'</label><br/>
					'.UI_HTML_Elements::Select( 'status', $optStatus, 'm' ).'
				</li>
				<li>
					<label for="roleId">'.$words['edit']['labelRole'].'</label><br/>
					'.UI_HTML_Elements::Select( 'roleId', $optRole, 'm' ).'
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './manage/user', $words['edit']['buttonCancel'], 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'saveUser', $words['edit']['buttonSave'], 'button save' ).'
				&nbsp;&nbsp;|&nbsp;&nbsp;
				'.UI_HTML_Elements::LinkButton(
					'./manage/user/remove',
					$words['edit']['buttonRemove'],
					'button remove',
					$words['edit']['buttonRemoveConfirm']
				).'
			</div>
		</fieldset>
	</form>
</div>
	';
?>

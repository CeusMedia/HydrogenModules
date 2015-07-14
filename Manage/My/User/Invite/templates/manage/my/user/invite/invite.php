<?php
$subject	= 'Einladung zu "%s"';
$subject	= sprintf( $subject, $config->get( 'app.name' ) );
if( $request->get( 'subject' ) )
	$subject	= $request->get( 'subject' );
$message	= 'Hallo!

Ich lade dich hiermit ein, bei "%1$s" teilzunehmen.
Du bekommst dazu einen meiner exklusiven Einladungs-Codes.
Klicke den unten stehenden Link, um zur Registrierung zu kommen.

Liebe Grüße,
%2$s';
if( $request->get( 'message' ) )
	$message	= $request->get( 'message' );
$userName	= $user->username;
if( !empty( $user->firstname ) && !empty( $user->surname ) )
	$userName	= $user->firstname.' '.$user->surname;
else if( !empty( $user->firstname ) )
	$userName	= $user->firstname;
$message	= sprintf( $message, $config->get( 'app.name' ), $userName );

if( $env->getModules()->has( 'Manage_Projects' ) ){
	$modelProject	= new Model_Project( $env );
	$projects		= $modelProject->getUserProjects( $env->getSession()->get( 'userId' ) );
	$optProject		= array();
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
	$optProject		= UI_HTML_Elements::Options( $optProject, $request->get( 'projectId' ) );
	
}

return '
<div class="column-left-70">
	<form action="./manage/my/user/invite/invite" method="post">
		<fieldset>
			<legend class="icon edit">Einladung</legend>
			<ul class="input">
				<li class="column-left-30">
					<label for="input_email" class="mandatory">E-Mail-Adresse</label><br/>
					<input type="text" name="email" id="input_email" class="max mandatory" value="'.htmlentities( $request->get( 'email' ), ENT_COMPAT, 'UTF-8' ).'"/>
				</li>
				<li class="column-left-40">
					<label for="input_subject" class="not-mandatory">Betreff</label><br/>
					<input type="text" name="subject" id="input_subject" class="max mandatory" value="'.htmlentities( $subject, ENT_COMPAT, 'UTF-8' ).'"/>
				</li>
				<li class="column-left-30">
					<label for="input_projectId" class="">Projekt</label><br/>
					<select name="projectId" id="input_projectId" class="max">'.$optProject.'</select>
				</li>
				<li>
					<label for="input_message" class="not-mandatory">Nachricht <small>(Link zur Registrierung wird automatisch eingetragen)</small></label><br/>
					<textarea name="message" id="input_message" class="max mandatory" rows="10">'.$message.'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.UI_HTML_Elements::LinkButton( './manage/my/user/invite', 'zurück', 'button cancel' ).'
				'.UI_HTML_Elements::Button( 'send', 'senden', 'button save' ).'
			</div>
		</fieldset>
	</form>
</div>
<div class="column-right-30">
<!--	<fieldset>
		<legend class="icon info">Informationen</legend>
		...
	</fieldset>
--></div>
<div class="column-clear"></div>
';
?>
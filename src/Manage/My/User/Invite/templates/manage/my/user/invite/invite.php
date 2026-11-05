<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;

$w			= (object) $words['invite'];

$subject	= $w->subject;
$subject	= sprintf( $subject, $config->get( 'app.name' ) );
if( $request->get( 'subject' ) )
	$subject	= $request->get( 'subject' );
$message	= join( "\n", [
	$w->messageGreeting,
	'',
	$w->messageText1,
	$w->messageText2,
	$w->messageText3,
	'',
	$w->messageClosing,
	'%2$s',
] );
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
	$projects		= $modelProject->getUserProjects( $env->getSession()->get( Logic_Authentication::$sessionKeyAuthUserId ) );
	$optProject		= [];
	foreach( $projects as $project )
		$optProject[$project->projectId]	= $project->title;
	$optProject		= HtmlElements::Options( $optProject, $request->get( 'projectId' ) );

}

return '
<div class="column-left-70">
	<form action="./manage/my/user/invite/invite" method="post">
		<fieldset>
			<legend class="icon edit">'.$w->legend.'</legend>
			<ul class="input">
				<li class="column-left-30">
					<label for="input_email" class="mandatory">'.$w->labelEmail.'</label><br/>
					<input type="text" name="email" id="input_email" class="max mandatory" value="'.htmlentities( $request->get( 'email' ), ENT_COMPAT, 'UTF-8' ).'"/>
				</li>
				<li class="column-left-40">
					<label for="input_subject" class="not-mandatory">'.$w->labelSubject.'</label><br/>
					<input type="text" name="subject" id="input_subject" class="max mandatory" value="'.htmlentities( $subject, ENT_COMPAT, 'UTF-8' ).'"/>
				</li>
				<li class="column-left-30">
					<label for="input_projectId" class="">'.$w->labelProject.'</label><br/>
					<select name="projectId" id="input_projectId" class="max">'.$optProject.'</select>
				</li>
				<li>
					<label for="input_message" class="not-mandatory">'.$w->labelMessage.' <small>'.$w->labelMessageHint.'</small></label><br/>
					<textarea name="message" id="input_message" class="max mandatory" rows="10">'.$message.'</textarea>
				</li>
			</ul>
			<div class="buttonbar">
				'.HtmlElements::LinkButton( './manage/my/user/invite', $w->buttonCancel, 'button cancel' ).'
				'.HtmlElements::Button( 'send', $w->buttonSend, 'button save' ).'
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

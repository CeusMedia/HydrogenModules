<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Work_Mail_Group extends CMF_Hydrogen_Hook
{
	public static function onRegisterTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/mail/group' );					//  load words
		$context->registerTab( '', $words->tabs['group'], 0 );									//  register main tab
	//	$context->registerTab( 'member', $words->tabs['members'], 1 );							//  register members tab
		$context->registerTab( 'server', $words->tabs['servers'], 2 );							//  register servers tab
		$context->registerTab( 'role', $words->tabs['roles'], 3 );								//  register roles tab
		$context->registerTab( 'message', $words->tabs['messages'], 4 );						//  register messages tab
	}
}

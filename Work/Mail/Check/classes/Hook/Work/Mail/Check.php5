<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Mail_Check extends Hook
{
	public static function onRegisterTab( Environment $env, $context, $module, $payload )
	{
		$words	= (object) $env->getLanguage()->getWords( 'work/mail/check' );						//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );										//  register main tab
		$context->registerTab( 'group', $words->tabs['group'], 1 );									//  register main tab
		$context->registerTab( 'import', $words->tabs['import'], 2 );								//  register main tab
		$context->registerTab( 'export', $words->tabs['export'], 3 );								//  register main tab
	}
}

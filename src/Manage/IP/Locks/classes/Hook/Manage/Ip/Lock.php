<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Manage_IP_Lock extends Hook
{
	public static function onRegisterTab( Environment $env, $context, $module, $data )
	{
		$words  = (object) $env->getLanguage()->getWords( 'manage/ip/lock' );							//  load words
		$context->registerTab( '', $words->tabs['index'], 0 );											//  register main tab
		$context->registerTab( 'filter', $words->tabs['filter'], 3 );									//  register filter tab
		$context->registerTab( 'reason', $words->tabs['reason'], 4 );									//  register reason tab
		$context->registerTab( 'transport', $words->tabs['transport'], 8 );								//  register transport tab
    }
}

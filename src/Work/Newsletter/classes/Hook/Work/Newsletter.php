<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Newsletter extends Hook
{
	public static function onRegisterHints( Environment $env, $context, $module, $payload = NULL )
	{
		$words	= $env->getLanguage()->getWords( 'work/newsletter' );
		View_Helper_Hint::registerHints( $words['hints'], 'Work_Newsletter' );
	}
}

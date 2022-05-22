<?php

use CeusMedia\HydrogenFramework\Environment;

class Hook_Work_Newsletter extends CMF_Hydrogen_Hook
{
	public static function onRegisterHints( Environment $env, $context, $module, $payload = NULL )
	{
		$words	= $env->getLanguage()->getWords( 'work/newsletter' );
		View_Helper_Hint::registerHints( $words['hints'], 'Work_Newsletter' );
	}
}

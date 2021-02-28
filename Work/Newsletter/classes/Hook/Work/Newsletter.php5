<?php
class Hook_Work_Newsletter extends CMF_Hydrogen_Hook
{
	public static function onRegisterHints( CMF_Hydrogen_Environment $env, $context, $module, $payload = NULL )
	{
		$words	= $env->getLanguage()->getWords( 'work/newsletter' );
		View_Helper_Hint::registerHints( $words['hints'], 'Work_Newsletter' );
	}
}

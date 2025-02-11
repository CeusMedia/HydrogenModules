<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Work_Newsletter extends Hook
{
	public function onRegisterHints(): void
	{
		$words	= $this->env->getLanguage()->getWords( 'work/newsletter' );
		if( class_exists( 'View_Helper_Hint' ) )
			View_Helper_Hint::registerHints( $words['hints'], 'Work_Newsletter' );
	}
}

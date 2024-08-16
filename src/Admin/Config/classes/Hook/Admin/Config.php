<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Admin_Config extends Hook
{
	/**
	 * @todo finish impl
	 */
	public function onAdminConfigRegisterTab(): void
	{
		$words	= (object) $this->env->getLanguage()->getWords( 'admin/config' );			//  load words
		$this->context->registerTab( '', $words->tabs['index'], 0 );							//  register index tab
//		$this->context->registerTab( 'module', $words->tabs['module'], 1 );						//  register module tab
//		$this->context->registerTab( 'direct', $words->tabs['direct'], 1 );						//  register direct tab
	}
}

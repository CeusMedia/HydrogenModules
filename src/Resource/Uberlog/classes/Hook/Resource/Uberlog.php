<?php

use CeusMedia\HydrogenFramework\Environment\Resource\Page;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Uberlog extends Hook
{
	public function onPageApplyModules(): void
	{
		/** @var Page $context */
		$context	= $this->context;
		$config		= $this->env->getConfig();

		$script1	= 'UberlogClient.uri = "'.$config->get( 'module.resource_uberlog.uri' ).'";';
		$script2	= 'UberlogClient.host = "'.getEnv( 'HTTP_HOST' ).'";';
		$context->js->addScript( $script1 );
		$context->js->addScript( $script2 );
	}
}

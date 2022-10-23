<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Server_Log extends Hook
{
	public static function onEnvLog( Environment $env, $context, $module, $data )
	{
		$resource	= new Resource_Server_Log( $env );
		$format		= isset( $data['format'] ) ? $data['format'] : NULL;
		return $resource->log( $data['type'], $data['message'], get_class( $context ), $format );
	}
}

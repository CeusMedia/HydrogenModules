<?php
class Hook_Server_Log/* extends CMF_Hydrogen_Hook*/{

	static public function onEnvLog( $env, $context, $module, $data ){
		$resource	= new Resource_Server_Log( $env );
		$format		= isset( $data['format'] ) ? $data['format'] : NULL;
		$resource->log( $data['type'], $data['message'], get_class( $context ), $format );
	}
}
?>

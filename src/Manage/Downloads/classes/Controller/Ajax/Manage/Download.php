<?php

class Controller_Ajax_Manage_Download extends \CeusMedia\HydrogenFramework\Controller\Ajax
{
	/**
	 *	@return		int
	 *	@throws		JsonException
	 *	@throws		ReflectionException
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function renameFolder(): int
	{
		$folderId	= $this->request->get( 'folderId', '' );
		$title		= $this->request->get( 'name', '' );
		if( '' === trim( $folderId ) )
			return $this->respondError( 'No folder ID given.' );

		if( '' === trim( $title ) )
			return $this->respondError( 'No title given.' );

		$frontend		= Logic_Frontend::getInstance( $this->env );
		$path			= $frontend->getModuleConfigValue( 'resource_downloads', 'path' );
		$logic			= new Logic_Download( $this->env, $path );
		$logic->renameFolder( $folderId, $title );
		return $this->respondData( $logic->getFolder( $folderId ) );
	}
}
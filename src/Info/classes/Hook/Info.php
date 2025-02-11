<?php

use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Info extends Hook
{
	public function onAppDispatch()
	{
		$path	= $this->env->getRequest()->get( '__path', '' );
		if( !str_starts_with( $path, 'info' ) )
			return;

		$path	= preg_replace( "/^info\//", "", $path );
		$view	= new View_Info( $this->env );

		$files	= [
			'html/info/'.$path.'.html',
			'html/info/'.$path.'.md',
		];
		foreach( $files as $file ){
			if( $view->hasContentFile( $file ) ){
				$request	= $this->env->getRequest();
				$request->set( '__controller', 'info' );
				$request->set( '__action', 'index' );
				$request->set( '__arguments', [$path] );
				return TRUE;
			}
		}
	}
}

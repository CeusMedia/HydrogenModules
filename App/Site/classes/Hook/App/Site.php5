<?php
class Hook_App_Site extends CMF_Hydrogen_Hook
{
	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onFrameworkDeprecation( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$entity		= 'UNKNOWN';
		$version	= '';
		$hint		= '';
		$note		= '';
		if( isset( $data['entity'] ) && strlen( trim( $data['entity'] ) ) )
			$entity		= trim( $data['entity'] );
		if( isset( $data['version'] ) && strlen( trim( $data['version'] ) ) )
		 	$version	= sprintf( ' (since version %s)', trim( $data['version'] ) );
		if( isset( $data['instead'] ) && strlen( trim( $data['instead'] ) ) )
			$hint		= sprintf( ' Please use "%s" instead!', trim( $data['instead'] ) );
		if( isset( $data['instead'] ) && strlen( trim( $data['instead'] ) ) )
			$hint		= sprintf( ' Please use "%s" instead!', trim( $data['instead'] ) );
		if( isset( $data['message'] ) && strlen( trim( $data['message'] ) ) )
			$note		= sprintf( ' Note: %s', trim( $data['message'] ) );
		switch( $data['type'] ){
			case 'class':
				$msg	= 'Class "%s" is deprecated';
				break;
			case 'class_inheritance':
				$msg	= 'Class "%s" is extending an deprecated class';
				break;
			case 'method':
				$msg	= 'Method "%s" is deprecated';
				break;
			case 'hook':
				$msg	= 'Hook "%s" is deprecated';
				break;
			default:
				$msg	= 'Deprecation detected on using "%s"';
		}
		$msg		= sprintf( $msg.'%s.%s%s', $entity, $version, $hint, $note );
		$msg		= date( 'c' ).' '.$msg."\n";
		$pathLogs	= $env->getConfig()->get( 'path.logs' );
		error_log( $msg, 3, $pathLogs.'deprecation.log' );
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onEnvConstructEnd( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		if( !$env->getModules()->has( 'Resource_Authentication' ) )
			return;
		if( !$env->getModules()->has( 'Info_Pages' ) )												//  module supporting pages not installed
			return;																					//  skip this hook
		if( !file_exists( 'config/pages.json' ) )													//  no page definition existing
			return;																					//  skip this hook
		$logic	=
		$acl	= $env->getAcl();
		$scopes	= json_decode( file_get_contents( 'config/pages.json' ) );
		foreach( $scopes as $scope => $pages ){
			foreach( $pages as $page ){
				//  @todo adding subpages is not stable because parent page could be not visible
				if( isset( $page->pages ) ){
					foreach( $page->pages as $subpage ){
						if( isset( $subpage->access ) ){
							$path	= isset( $subpage->link ) ? $subpage->link : $subpage->path;
							$path	= str_replace( '/', '_', $path );
							if( $subpage->access == "public" ){
								$acl->setPublicLinks( array( $path ), 'append' );
								$acl->setPublicLinks( array( $path.'_index' ), 'append' );
							}
							else if( $subpage->access == "outside" ){
								$acl->setPublicOutsideLinks( array( $path ), 'append' );
								$acl->setPublicOutsideLinks( array( $path.'_index' ), 'append' );
							}
						}
					}
				}
				if( isset( $page->access ) ){
					$path	= isset( $page->link ) ? $page->link : $page->path;
					$path	= str_replace( '/', '_', $path );
					if( $page->access == "public" ){
						$acl->setPublicLinks( array( $path ), 'append' );
						$acl->setPublicLinks( array( $path.'_index' ), 'append' );
					}
					else if( $page->access == "outside" ){
						$acl->setPublicOutsideLinks( array( $path ), 'append' );
						$acl->setPublicOutsideLinks( array( $path.'_index' ), 'append' );
					}
				}
			}
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$messenger	= $context->env->getMessenger();									//  shortcut messenger
		if( !file_exists( '.htaccess' ) ){												//  .htaccess file is not existing
			if( file_exists( '.htaccess.dist' ) && file_exists( '.htpasswd.dist' ) ){	//  but default files are existing
				if( !@copy( '.htaccess.dist', '.htaccess' ) )							//  try to install default .htaccess
					throw new RuntimeException( "Cannot create .htaccess from .htaccess.dist" );
				if( !@copy( '.htpasswd.dist', '.htpasswd' ) )							//  try to install default .htpasswd
					throw new RuntimeException( "Cannot create .htpasswd from .htpasswd.dist" );
				$messenger->noteSuccess( 'Created .htaccess and .htpasswd for authentication.' );
			}
		}
		if( !file_exists( 'robots.txt' ) ){												//  robots file is not existing
			if( file_exists( 'robots.txt.dist' ) ){										//  but default file is existing
				if( !@copy( 'robots.txt.dist', 'robots.txt' ) )							//  try to install default robots file
					throw new RuntimeException( "Cannot create robots.txt from robots.txt.dist" );
				$messenger->noteSuccess( 'Created empty robots file (robots.txt).' );
			}
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onPageInit( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$config = $env->getConfig();														//  shortcut configuration
		if( !$config->get( 'app.revision' ) ){												//  no revision set in base app configuration
			$version	= $config->get( 'module.app_site.version' );						//  get version from module App:Site
			if( version_compare( $version, 0 ) === 1 ){										//  a version (greater than 0) has been set
				$context->css->primer->setRevision( $version );								//  set version as revision on primer CSS collector
				$context->css->theme->setRevision( $version );								//  set version as revision on theme CSS collector
				$context->js->setRevision( $version );										//  set version as revision on JavaScript collector
			}
		}
	}

	/**
	 *	...
	 *	@static
	 *	@access		public
	 *	@param		CMF_Hydrogen_Environment	$env		Environment object
	 *	@param		object						$context	Object scope to apply hook within
	 *	@param		???							$module		???
	 *	@param		array|object				$data		Data array or object for hook event handler
	 *	@return		boolean|NULL				...
	 */
	static public function onTinyMCEGetImageList( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$moduleConfig		= $env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend			= Logic_Frontend::getInstance( $env );
		$remotePathThemes	= $frontend->getPath( 'themes' );
		$virtualPathThemes	= substr( $remotePathThemes, strlen( $frontend->getPath() ) );
		$words				= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes			= (object) $words['link-prefixes'];
		$list				= [];

		$extensions			= array( 'png', 'jpg', 'jpeg', 'jpe', 'svg' );
		if( 0 && $env->getModules()->has( 'Manage_Content_Images' ) ){
			$configKey	= 'module.manage_content_images.extensions';
			$extensions	= explode( ',', $env->getConfig()->get( $configKey ) );
		}

		$customTheme	= $frontend->getConfigValue( 'layout.theme' );
		$themes			= array( 'common', $customTheme );
		foreach( $themes as $theme ){
			$path	= $remotePathThemes.$theme.'/img/';
			if( !strlen( trim( $theme ) ) || !is_dir( $path ) )
				continue;
			$list	= [];
			$index	= new FS_File_RecursiveIterator( $path );
			foreach( $index as $item ){
				$extension	= pathinfo( $item->getFilename(), PATHINFO_EXTENSION );
				if( !in_array( strtolower( $extension ), $extensions ) )
					continue;
				$itemPath	= substr( $item->getPathname(), strlen( $remotePathThemes ) );
				$label		= substr( $itemPath, strlen( $theme.'/img/' ) );
				$key		= strtolower( str_replace( '/', '_', $label.'_'.uniqid() ) );
				$list[$key]	= (object) array(
					'title'		=> $label,
					'value'		=> $virtualPathThemes.$itemPath,
					'filesize'	=> filesize( $item->getPathname() ),
					'timestamp'	=> filemtime( $item->getPathname() ),
				);
			}
			if( !$list )
				continue;
			ksort( $list);
			$list   = array( (object) array(
				'title'	=> "Theme: ".ucFirst( $theme ),//$prefixes->image,
				'menu'	=> array_values( $list ),
			) );
			$context->list	= array_merge( $context->list, $list );
		}
	}

	/**
	 *	@deprecated		use Hook_App_Site::onEnvConstructEnd instead
	 */
	static public function ___onEnvConstructEnd( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$env->getCaptain()->callHook( 'Framework', 'deprecation', $env, array(
			'type'		=> 'hook',
			'entity'	=> 'Hook_App_Site::___onEnvConstructEnd',
			'message'	=> 'Hook method "___onEnvConstructEnd" has been renamed to "onEnvConstructEnd"',
			'instead'	=> 'Hook_App_Site::onEnvConstructEnd',
		) );
		return self::onEnvConstructEnd( $env, $context, $module, $data );
	}

	/**
	 *	@deprecated		use Hook_App_Site::onPageApplyModules instead
	 */
	static public function ___onPageApplyModules( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$env->getCaptain()->callHook( 'Framework', 'deprecation', $env, array(
			'type'		=> 'hook',
			'entity'	=> 'Hook_App_Site::___onPageApplyModules',
			'message'	=> 'Hook method "___onPageApplyModules" has been renamed to "onPageApplyModules"',
			'instead'	=> 'Hook_App_Site::onPageApplyModules',
		) );
		return self::onPageApplyModules( $env, $context, $module, $data );
	}

	/**
	 *	@deprecated		use Hook_App_Site::onPageInit instead
	 */
	static public function ___onPageInit( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$env->getCaptain()->callHook( 'Framework', 'deprecation', $env, array(
			'type'		=> 'hook',
			'entity'	=> 'Hook_App_Site::___onPageInit',
			'message'	=> 'Hook method "___onPageInit" has been renamed to "onPageInit"',
			'instead'	=> 'Hook_App_Site::onPageInit',
		) );
		return self::onPageInit( $env, $context, $module, $data );
	}

	/**
	 *	@deprecated		use Hook_App_Site::onTinyMCEGetImageList instead
	 */
	static public function ___onTinyMCE_getImageList( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$env->getCaptain()->callHook( 'Framework', 'deprecation', $env, array(
			'type'		=> 'hook',
			'entity'	=> 'Hook_App_Site::___onTinyMCE_getImageList',
			'message'	=> 'Hook method "___onTinyMCE_getImageList" has been renamed to "onTinyMCEGetImageList"',
			'instead'	=> 'Hook_App_Site::onTinyMCEGetImageList',
		) );
		return self::onTinyMCEGetImageList( $env, $context, $module, $data );
	}

	/**
	 *	@deprecated		use Hook_App_Site::onFrameworkDeprecation instead
	 */
	static public function ___onFrameworkDeprecation( CMF_Hydrogen_Environment $env, $context, $module, $data = [] )
	{
		$env->getCaptain()->callHook( 'Framework', 'deprecation', $env, array(
			'type'		=> 'hook',
			'entity'	=> 'Hook_App_Site::___onFrameworkDeprecation',
			'message'	=> 'Hook method "___onFrameworkDeprecation" has been renamed to "onFrameworkDeprecation"',
			'instead'	=> 'Hook_App_Site::onFrameworkDeprecation',
		) );
		return self::onFrameworkDeprecation( $env, $context, $module, $data );
	}
}

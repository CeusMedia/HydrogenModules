<?php
class Hook_App_Site{

	static public function ___onEnvConstructEnd( $env, $context, $module, $data = array() ){
		if( !$env->getModules()->has( 'Resource_Authentication' ) )
			return;
		if( !file_exists( 'config/pages.json' ) )
			return;
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

	static public function ___onPageApplyModules( $env, $context, $module, $data = array() ){
		$messenger	= $context->env->getMessenger();									//  shortcut messenger
		$config		= $module->config;													//  shortcut module configuration pairs
		if( !file_exists( '.htaccess' ) ){												//  .htaccess file is not existing
			if( file_exists( '.htaccess.dist' ) && file_exists( '.htpasswd.dist' ) ){	//  but default files are existing
				if( !@copy( '.htaccess.dist', '.htaccess' ) )							//  try to install default .htaccess
					throw new RuntimeException( "Cannot create .htaccess from .htaccess.dist" );
				if( !@copy( '.htpasswd.dist', '.htpasswd' ) )							//  try to install default .htpasswd
					throw new RuntimeException( "Cannot create .htpasswd from .htpasswd.dist" );
				$messenger->noteSuccess( 'Created .htaccess and .htpasswd for authentication.' );
			}
		}
	}

	static public function ___onPageInit( $env, $context, $module, $data = array() ){
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

	static public function ___onTinyMCE_getImageList( $env, $context, $module, $data = array() ){
		$moduleConfig		= $env->getConfig()->getAll( 'module.manage_galleries.', TRUE );
		$frontend			= Logic_Frontend::getInstance( $env );
		$remotePathThemes	= $frontend->getPath( 'themes' );
		$virtualPathThemes	= substr( $remotePathThemes, strlen( $frontend->getPath() ) );
		$words				= $env->getLanguage()->getWords( 'js/tinymce' );
		$prefixes			= (object) $words['link-prefixes'];
		$list				= array();

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
			$list	= array();
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
}

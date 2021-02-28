<?php
class Hook_Info_Relocation extends CMF_Hydrogen_Hook
{
	public static function onPageApplyModulesCheckShortcutRoute( CMF_Hydrogen_Environment $env, $module, $context, $payload = array() )
	{
		$config	= $env->getConfig()->getAll( 'module.info_relocation.', TRUE );	//  shortcut config
		if( $env->getModules()->has( 'Server_Router' ) ){						//  router module is installed
			if( $config->get( 'shortcut' ) ){									//  shortcut is enabled
				$model	= new Model_Route( $env );								//  get router module
				if( !$model->getByIndex( 'target', 'info/relocation/$1' ) ){	//  shortcut route is not set up yet
					$model->add( array(											//  add shortcut route
						'status'	=> 1,										//  ... as active
						'regex'		=> 1,										//  ... as regular expression
						'code'		=> $config->get( 'shortcut.code' ),			//  ... with HTTP code
						'source'	=> $config->get( 'shortcut.source' ),		//  ... with source pattern
						'target'	=> $config->get( 'shortcut.target' ),		//  ... with target pattern
						'createdAt'	=> time(),									//  ... and note creation time
					) );
				}
			}
		}
	}
}

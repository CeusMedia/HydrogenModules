<?php

use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Hook;

class Hook_Resource_Theme extends Hook
{
	public function onPageBuild(): void
	{
		foreach( $this->env->getModules()->getAll() as $module ){
			if( !preg_match( '/^(UI_)?Theme_/', $module->id ) )
				continue;
			if( !$module->config['active']->value )
				continue;

			$page	= $this->env->getPage();
			foreach( $module->files->styles as $styleFile )
				$page->addCommonStyle( $styleFile->file );

			if( isset( $module->config['style'] ) )
				$page->addBodyClass( sprintf( 'style-%s', $module->config['style']->value ) );
		}
	}
}

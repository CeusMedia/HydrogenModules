<?php

use CeusMedia\HydrogenFramework\Hook;

class Hook_Helper_Input_Resource extends Hook
{
	public function onPageInitModules(): void
	{
		$config		= $this->env->getConfig();
		$page		= $this->env->getPage();
		$modules	= $this->env->getModules();

		$pathContent	= $config->get( 'path.contents' );
		$pathImages		= $config->get( 'path.images' );
		$pathThemes		= $config->get( 'path.themes' );
		$pathDocuments	= NULL;
		$pathDownloads	= NULL;

		$pathsDefinedInModules	= [
			'pathImages'	=> ['Manage_Content_Images', 'path.images'],
			'pathDocuments'	=> ['Manage_Content_Documents', 'path.documents'],
			'pathDownloads'	=> ['Info_Downloads', 'path'],
		];
		foreach( $pathsDefinedInModules as $path => $definition )
			if( ( $module	= $modules->get( $definition[0], TRUE, FALSE ) ) )
				${$path}	= $module->config[$definition[1]]->value;

		$modePaths	= [
			'image'		=> [$pathImages, $pathThemes],
			'theme'		=> [$pathThemes],
			'document'	=> $pathDocuments ? [$pathDocuments] : [],
			'download'	=> $pathDownloads ? [$pathDownloads] : [],
		];
		$scripts	= [];
		foreach( $modePaths as $key => $paths ){
			if( !count( $paths ) )
				continue;
			$scripts[]	= vsprintf( 'HelperInputResource.modePaths.%s = %s;', [
				$key,
				json_encode( $paths ),
			] );
		}
		$page->js->addScriptOnReady( join( PHP_EOL, $scripts ) );
	}
}

<?php
namespace CeusMedia\HydrogenModulesTest;

use CeusMedia\Common\FS\File\Reader;
use CeusMedia\Common\FS\File\Reader as FileReader;
use CeusMedia\Common\FS\File\Writer;
use CeusMedia\Common\Loader;
use CeusMedia\Common\XML\Element as XmlElement;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;
use Exception;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
	protected string $path;
	protected string $pathApp;
	protected array $defaultAppOptions;

	protected function setUp(): void
	{
		$this->path 	= __DIR__.'/';
		$this->pathApp	= $this->path.'.app/';
		$this->defaultAppOptions	= [
			'pathApp'		=> $this->pathApp,
			'configFile'	=> 'config.ini'
		];
	}

	/**
	 *	@param		$options
	 *	@return		Environment
	 *	@throws		Environment\Exception
	 */
	protected function createEnvironment( $options = NULL, $web = FALSE ): Environment
	{
//		Loader::create( 'php', $this->pathApp.'classes/' )->register();
		$options	= $options ?? $this->defaultAppOptions;
		if( $web ){
			$options['isTest']	= TRUE;
			$options['pathApp']	= $this->pathApp;
			putEnv( 'REQUEST_METHOD=GET' );
			return new WebEnvironment( $options );
		}
		return new Environment( $options );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		array		$config
	 *	@return		void
	 *	@throws		Exception
	 */
	protected function installModule( string $moduleId, array $config = [] ): void
	{
		$modulePath	= str_replace( [':', '_'], '/', $moduleId );
		$moduleFile	= str_replace( [':', '/'], '_', $moduleId );
		Writer::save(
			$this->pathApp.'config/modules/'.$moduleFile.'.xml',
			Reader::load( $this->path.'../src/'.$modulePath.'/module.xml' )
		);

		if( file_exists( $this->path.'../src/'.$modulePath.'/classes' ) )
			Loader::create( 'php', $this->path.'../src/'.$modulePath.'/classes' )->register();

		$definition	= Environment\Resource\Module\Reader::load( $this->pathApp.'config/modules/'.$moduleFile.'.xml', $moduleId );
		/** @var Environment\Resource\Module\Definition\File $file */
/*		foreach( $definition->files->classes as $file ){
			$targetFile	= $this->pathApp.'classes/'.$file->file;
			mkdir( dirname( $targetFile ), 0777, TRUE );
			copy( $this->path.'../src/'.$modulePath.'/classes/'.$file->file, $targetFile );
		}*/

//		copy( 'src/'.$modulePath.'/module.xml', $this->pathApp.'config/modules/'.$moduleFile.'.xml' );
		foreach( $config as $key => $value )
			$this->setModuleConfig( $moduleId, $key, $value );
	}

	/**
	 *	@param		string		$moduleId
	 *	@param		string		$configKey
	 *	@param		$configValue
	 *	@return		void
	 *	@throws		Exception
	 */
	public function setModuleConfig( string $moduleId, string $configKey, $configValue ): void
	{
		$moduleFile	= $this->pathApp.'config/modules/'.str_replace( [':', '/'], '_', $moduleId ).'.xml';
		$xml		= FileReader::load( $moduleFile );
		$xml		= new XmlElement( $xml );
		foreach( $xml->config as $node ){														//  iterate original module config pairs
			$key	= (string) $node['name'];													//  shortcut config pair key
			if( $key !== $configKey )
				continue;
			$node->setValue( (string) $configValue );
		}
		$xml->saveXml( $moduleFile );																//  save changed DOM to module file
		clearstatcache();
	}

	protected function uninstallModule( $moduleId ): void
	{
		$moduleFile	= str_replace( [':', '/'], '_', $moduleId );

		$definition	= Environment\Resource\Module\Reader::load( $this->pathApp.'config/modules/'.$moduleFile.'.xml', $moduleId );
		/** @var Environment\Resource\Module\Definition\File $file */
		foreach( $definition->files->classes as $file )
			unlink( $this->pathApp.'classes/'.$file->file );

		unlink( $this->pathApp.'config/modules/'.$moduleFile.'.xml' );
	}

	protected function tearDown(): void
	{
	}
}
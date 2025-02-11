<?php
/**
 *	Controller to test server classes.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\Exception\Serializable as SerializableException;
use CeusMedia\Common\FS\File\RegexFilter as RegexFileFilter;
use CeusMedia\HydrogenFramework\Controller;

/**
 *	Controller to test server classes.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Test extends Controller
{
	/**
	 *	Run all tests and return result.
	 *	@access		public
	 *	@return		array
	 */
	public function index(): array
	{
		$core			= $this->syntaxCore();
		$controllers	= $this->syntaxControllers();
		$models			= $this->syntaxModels();
		$total			= count( $core ) + count( $controllers ) + count( $models );
		$failed			= 0;
		foreach( $core as $class => $result )
			if( !$result )
				$failed++;
		foreach( $controllers as $class => $result )
			if( !$result )
				$failed++;
		foreach( $models as $class => $result )
			if( !$result )
				$failed++;
		$data	= [
			'core'			=> $core,
			'controllers'	=> $controllers,
			'models'		=> $models,
			'total'			=> $total,
			'failed'		=> $failed
		];
		return $data;
	}

	public function syntaxController( $controller ): ?bool
	{
		$filePath	= 'classes/Controller/'.ucfirst( $controller ).'.php';
		if( !file_exists( $filePath ) )
			return NULL;
		return self::checkSyntax( $filePath, $error );
	}

	public function syntaxControllers(): array
	{
		$list	= [];
		$index	= $this->listFilesInFolder( 'classes/Controller' );
		foreach( array_keys( $index ) as $controller )
			$list[$controller]	 = $this->syntaxController( $controller );
		return $list;
	}

	public function syntaxCore(): array
	{
		$list	= [];																			//  create empty list
		$index	= $this->listFilesInFolder( 'classes' );											//  list all core classes
		foreach( $index as $fileName => $filePath )													//  iterate index
			$list[$fileName]	= self::checkSyntax( $filePath, $error );							//  check class syntax
		$index	= $this->listFilesInFolder( 'classes/Resource' );											//  list all core classes
		foreach( $index as $fileName => $filePath )													//  iterate index
			$list[$fileName]	= self::checkSyntax( $filePath, $error );							//  check class syntax
		return $list;
	}

	public function syntaxModel( $model ): ?bool
	{
		$filePath	= 'classes/Model/'.ucfirst( $model ).'.php';
		if( !file_exists( $filePath ) )
			return NULL;
		return self::checkSyntax( $filePath, $error );
	}

	public function syntaxModels(): array
	{
		$list	= [];
		$index	= $this->listFilesInFolder( 'classes/Model' );
		foreach( array_keys( $index ) as $model )
			$list[$model]	 = $this->syntaxModel( $model );
		return $list;
	}

	public function throwException( $message = NULL, $code = 0 )
	{
		$message	= strlen( $message ) ? $message : 'This is a test exception.';
		throw new RuntimeException( $message, (int) $code );
	}

	public function throwSerializableException( $message = NULL, $code = 0 )
	{
		$message	= strlen( $message ) ? $message : 'This is a test exception.';
		throw new SerializableException( $message, (int) $code );
	}

	/**
	 *	Checks PHP file syntax and returns true if valid.
	 *	@static
	 *	@access		protected
	 *	@param		string		$filePath
	 *	@param		string		$error			Reference of output if not valid
	 *	@return		bool		TRUE if valid, FALSE in invalid. See $error for more information.
	 */
	protected static function checkSyntax( $filePath, &$error )
	{
		exec( "php -l ".$filePath, $error, $code );
		return !$code;
	}

	protected function listFilesInFolder( $path, $extension = "php" )
	{
		$list	= [];																			//  create empty list
		$index	= new RegexFileFilter( $path, '/\.'.$extension.'$/' );								//  list all classes in folder
		foreach( $index as $file ) {																//  iterate index
			$name			= pathinfo( $file->getFilename(), PATHINFO_FILENAME );					//  get file name
			$list[$name]	= $file->getPathname();
		}
		ksort( $list );
		return $list;
	}
}

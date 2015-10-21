<?php
/**
 *	Controller to test server classes.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Test.php 3004 2012-06-25 23:22:02Z christian.wuerker $
 */
/**
 *	Controller to test server classes.
 *	@category		cmApps
 *	@package		Chat.Server.Controller
 *	@extends		CMF_Hydrogen_Controller
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2010 Ceus Media
 *	@version		$Id: Test.php 3004 2012-06-25 23:22:02Z christian.wuerker $
 */
class Controller_Test extends CMF_Hydrogen_Controller {

	/**
	 *	Checks PHP file syntax and returns true if valid.
	 *	@static
	 *	@access		protected
	 *	@param		string		$filePath
	 *	@param		string		$error			Reference of output if not valid
	 *	@return		bool		TRUE if valid, FALSE in invalid. See $error for more information.
	 */
	protected static function checkSyntax( $filePath, &$error ) {
		exec( "php -l ".$filePath, $error, $code );
		return !$code;
	}

	/**
	 *	Run all tests and return result.
	 *	@access		public
	 *	@return		array
	 */
	public function index() {
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
		$data	= array(
			'core'			=> $core,
			'controllers'	=> $controllers,
			'models'		=> $models,
			'total'			=> $total,
			'failed'		=> $failed
		);
		return $data;
	}

	protected function listFilesInFolder( $path, $extension = "php" ) {
		$list	= array();																			//  create empty list
		$index	= new FS_File_RegexFilter( $path, '/\.'.$extension.'$/' );								//  list all classes in folder
		foreach( $index as $file ) {																//  iterate index
			$name			= pathinfo( $file->getFilename(), PATHINFO_FILENAME );					//  get file name
			$list[$name]	= $file->getPathname();
		}
		ksort( $list );
		return $list;
	}

	public function syntaxController( $controller ) {
		$filePath	= 'classes/Controller/'.ucfirst( $controller ).'.php';
		if( !file_exists( $filePath ) )
			return NULL;
		return self::checkSyntax( $filePath, $error );
	}

	public function syntaxControllers() {
		$list	= array();
		$index	= $this->listFilesInFolder( 'classes/Controller' );
		foreach( array_keys( $index ) as $controller )
			$list[$controller]	 = $this->syntaxController( $controller );
		return $list;
	}

	public function syntaxCore() {
		$list	= array();																			//  create empty list
		$index	= $this->listFilesInFolder( 'classes' );											//  list all core classes
		foreach( $index as $fileName => $filePath )													//  iterate index
			$list[$fileName]	= self::checkSyntax( $filePath, $error );							//  check class syntax
		$index	= $this->listFilesInFolder( 'classes/Resource' );											//  list all core classes
		foreach( $index as $fileName => $filePath )													//  iterate index
			$list[$fileName]	= self::checkSyntax( $filePath, $error );							//  check class syntax
		return $list;
	}

	public function syntaxModel( $model ) {
		$filePath	= 'classes/Model/'.ucfirst( $model ).'.php';
		if( !file_exists( $filePath ) )
			return NULL;
		return self::checkSyntax( $filePath, $error );
	}

	public function syntaxModels() {
		$list	= array();
		$index	= $this->listFilesInFolder( 'classes/Model' );
		foreach( array_keys( $index ) as $model )
			$list[$model]	 = $this->syntaxModel( $model );
		return $list;
	}

	public function throwException( $message = NULL, $code = 0 ){
		$message	= strlen( $message ) ? $message : 'This is a test exception.';
		throw new RuntimeException( $message, (int) $code );
	}

	public function throwSerializableException( $message = NULL, $code = 0 ){
		$message	= strlen( $message ) ? $message : 'This is a test exception.';
		throw new Exception_Serializable( $message, (int) $code );
	}
}
?>

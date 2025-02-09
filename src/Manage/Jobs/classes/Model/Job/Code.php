<?php

use CeusMedia\Common\Exception\FileNotExisting as FileNotExistingException;
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\PhpParser\Exception\MergeException as ParserMergeException;
use CeusMedia\PhpParser\Parser\Regular as PhpParser;

class Model_Job_Code
{
	protected Environment $env;
	protected array $classes	= [];

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function getClassesNames(): array
	{
		return array_keys( $this->classes );
	}

	/**
	 *	@param		string		$className
	 *	@return		object
	 */
	public function getClassData( string $className ): object
	{
		if( !array_key_exists( $className, $this->classes ) )
			throw new DomainException( 'Invalid class name' );
		return $this->classes[$className];
	}

	/**
	 *	@param		string		$className
	 *	@param		string		$methodName
	 *	@return		object
	 */
	public function getClassMethodData( string $className, string $methodName ): object
	{
		$class	= $this->getClassData( $className );
		if( !array_key_exists( $methodName, $class->methods ) )
			throw new DomainException( 'Invalid method name' );
		return $class->methods[$methodName];
	}

	/**
	 *	@param		string		$className
	 *	@return		array
	 */
	public function getClassMethods( string $className ): array
	{
		$class	= $this->getClassData( $className );
		return array_keys( $class->methods );
	}

	/**
	 *	@param		string		$className
	 *	@param		string		$methodName
	 *	@return		array
	 */
	public function getClassMethodSourceCode( string $className, string $methodName ): array
	{
		$method	= $this->getClassMethodData( $className, $methodName );
		return $method->source;
	}

	public function getDataOfAllClasses(): array
	{
		return $this->classes;
	}

	/**
	 *	@param		string		$path
	 *	@return		array
	 *	@throws		ParserMergeException
	 */
	public function readAll( string $path ): array
	{
		if( !( file_exists( $path ) && is_dir( $path ) ) )
			throw new DomainException( 'Path is not existing' );
		$lister	= new RecursiveFolderLister( $path );
		$lister->setExtensions( ['php', 'php5'] );
		$lister->showFolders( FALSE );
		foreach( $lister->getList() as $entry ){
			$this->readFile( $entry->getPathname() );
		}
		ksort( $this->classes );
		return $this->classes;
	}

	/**
	 *	@param		string		$filePath
	 *	@return		object|FALSE
	 *	@throws		FileNotExistingException
	 *	@throws		ParserMergeException
	 */
	public function readFile( string $filePath ): object|FALSE
	{
		if( !( file_exists( $filePath ) && ( is_file( $filePath ) || is_link( $filePath ) ) ) )
			throw FileNotExistingException::create( 'File is not existing' )
				->setResource( $filePath )
				->setDescription( 'The class name, referenced in job definition, is not leading to a job class file.' );

		return $this->readFileWithParser( $filePath );
	}

	/**
	 *	@param		string		$filePath
	 *	@return		object|FALSE
	 *	@throws		ParserMergeException
	 */
	protected function readFileWithParser( string $filePath ): object|FALSE
	{
		$parser	= new PhpParser();
		$file	= $parser->parseFile( $filePath, '' );
		foreach( $file->getClasses() as $className => $class ){
			$methods	= [];
			$this->classes[$className]	= (object) [
				'file'		=> $filePath,
				'methods'	=> & $methods,
				'desc'		=> preg_split( '/\r?\n/', $class->getDescription() ?? '' ),
			];
			foreach( $class->getMethods( FALSE ) as $methodName => $method ){
				if( $method->getAccess() !== 'public' )
					continue;
				$arguments	= [];
				$methods[$methodName]	= (object) [
					'arguments'	=> & $arguments,
					'source' 	=> $this->clearSourceCode( $method->getSourceCode() ),
					'desc'		=> preg_split( '/\r?\n/', $method->getDescription() ?? '' ),
				];
				foreach( $method->getParameters() as $paramName => $param ){
					$arguments[$paramName]	= (object) [
						'type'	=> $param->getType(),
						'desc'	=> $param->getDescription(),
					];
				}
			}
		}
		return reset( $this->classes );
	}

	/**
	 *	Replaces two leading tabs from every line.
	 *	@param		array		$code		Lines of source code
	 *	@return		array
	 */
	protected function clearSourceCode( array $code ): array
	{
		$list	= [];
		foreach( $code as $line )
			$list[]	= preg_replace( '@^(//)?(\t){2}(.*)@', '\\3', $line );
		return $list;
	}
}

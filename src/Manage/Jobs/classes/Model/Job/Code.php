<?php
use CeusMedia\Common\FS\Folder\RecursiveLister as RecursiveFolderLister;
use CeusMedia\HydrogenFramework\Environment;
use CeusMedia\PhpParser\Parser\Regular as PhpParser;

class Model_Job_Code
{
	protected array $classes	= [];

	public function __construct( Environment $env )
	{
	}

	public function getClassesNames(): array
	{
		return array_keys( $this->classes );
	}

	public function getClassData( string $className ): object
	{
		if( !array_key_exists( $className, $this->classes ) )
			throw new DomainException( 'Invalid class name' );
		return $this->classes[$className];
	}

	public function getClassMethodData( string $className, string $methodName ): object
	{
		$class	= $this->getClassData( $className );
		if( !array_key_exists( $methodName, $class->methods ) )
			throw new DomainException( 'Invalid method name' );
		return $class->methods[$methodName];
	}

	public function getClassMethods( string $className ): array
	{
		$class	= $this->getClassData( $className );
		return array_keys( $class->methods );
	}

	public function getClassMethodSourceCode( string $className, string $methodName ): array
	{
		$method	= $this->getClassMethodData( $className, $methodName );
		return $method->source;
	}

	public function getDataOfAllClasses(): array
	{
		return $this->classes;
	}

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

	public function readFile( string $filePath ): object
	{
		if( !( file_exists( $filePath ) && ( is_file( $filePath ) || is_link( $filePath ) ) ) )
			throw new DomainException( 'File is not existing' );

		return $this->readFileWithParser( $filePath );
	}

	protected function readFileWithParser( string $filePath ): object
	{
		$parser	= new PhpParser();
		$file	= $parser->parseFile( $filePath, '' );
		foreach( $file->getClasses() as $className => $class ){
			$methods	= [];
			$this->classes[$className]	= (object) array(
				'file'		=> $filePath,
				'methods'	=> & $methods,
				'desc'		=> preg_split( '/\r?\n/', $class->getDescription() ),
			);
			foreach( $class->getMethods( FALSE ) as $methodName => $method ){
				if( $method->getAccess() !== 'public' )
					continue;
				$arguments	= [];
				$methods[$methodName]	= (object) array(
					'arguments'	=> & $arguments,
					'source' 	=> $this->clearSourceCode( $method->getSourceCode() ),
					'desc'		=> preg_split( '/\r?\n/', $method->getDescription() ),
				);
				foreach( $method->getParameters() as $paramName => $param ){
					$arguments[$paramName]	= (object) array(
						'type'	=> $param->getType(),
						'desc'	=> $param->getDescription(),
					);
				}
			}
		}
		return reset( $this->classes );
	}

	protected function clearSourceCode( ?array $code ): ?array
	{
		return $code;
		$code	= preg_replace( '@^(//)?\t@s', '\\1', $code );
	}
}

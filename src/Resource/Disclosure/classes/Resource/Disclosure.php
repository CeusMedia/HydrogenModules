<?php
/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */

use CeusMedia\Common\FS\File\RecursiveRegexFilter as RecursiveRegexFileIndex;

/**
 *	...
 *	@category		Library
 *	@package		CeusMedia.HydrogenFramework.Environment.Resource
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2012-2021 Ceus Media
 *	@license		http://www.gnu.org/licenses/gpl-3.0.txt GPL 3
 *	@link			https://github.com/CeusMedia/HydrogenFramework
 */
class Resource_Disclosure
{
	protected $reflectOptions	= array(
		'classPrefix'		=> 'Controller_',
		'readMethods'		=> TRUE,
		'readParameters'	=> TRUE,
		'fileExtension'		=> 'php.?',
		'reflectClass'		=> FALSE,
		'reflectMethod'		=> FALSE,
		'reflectParameter'	=> FALSE,
		'skipAbstract'		=> TRUE,
		'skipMagic'			=> TRUE,
		'skipInherited'		=> TRUE,
		'skipFramework'		=> TRUE,
		'methodFilter'		=> ReflectionMethod::IS_PUBLIC
	);

	public function __construct( $env, array $reflectOptions = [] )
	{
		$this->env	= $env;
		$this->reflectOptions	= array_merge( $this->reflectOptions, $reflectOptions );
	}

	public function getFrontendControllers( $frontendEnv )
	{
		$controllers	= [];
		$pathConfig		= $frontendEnv->getConfig()->get( 'path.config' );
		$pathModules	= $frontendEnv->getConfig()->get( 'path.modules' );
		$pathModules	= $pathModules ? $pathModules : $pathConfig.'modules/';
		foreach( $frontendEnv->getModules()->getAll() as $moduleId => $module ){
			if( empty( $module->files->classes ) )
				continue;
			foreach( $module->files->classes as $moduleFile )
				if( preg_match( "/^Controller/", $moduleFile->file ) ){
					$name	= preg_replace( "/^Controller\/(.+)\.php.?$/", "$1", $moduleFile->file );
					$controllers[]	= str_replace( "/", "_", $name );
				}
		}
		return array_unique( $controllers );
	}

	/**
	 *	Index controller classes with methods and arguments.
	 *	Uses reflection to inspect classes. So, it is kinda slow.
	 *
	 *	@access		public
	 *	@param		string		$path		Path to look for controller classes
	 *	@param		array		$options	Additional options to extend default reflect option
	 *	@return		array		Map of controllers with methods and arguments
	 */
	public function reflect( string $path, array $options = [] ): array
	{
		$options	= array_merge( $this->reflectOptions, $options );

		$classes	= [];
		$path		= realpath( $path );
		$index		= new RecursiveRegexFileIndex( $path, '/^[^_].+\.'.$options['fileExtension'].'$/' );
		foreach( $index as $entry ){
			$fileName	= preg_replace( '@^'.$path.'/@', '', $entry->getPathname() );
			$fileBase	= preg_replace( '@\.'.$options['fileExtension'].'$@', '', $fileName );
			$controller	= str_replace( '/', '_', $fileBase );
			$className	= $options['classPrefix'].$controller;

			if( !class_exists( $className ) )
				continue;
			$classReflection	= new ReflectionClass( $className );
			$class	= new stdClass();
			$class->name			= $className;
			$class->methods		= [];
			if( $options['skipAbstract'] )															//  abstract classes shall be skipped
				if( $classReflection->isAbstract() )												//  class is abstract
					continue;																		//  skip this class
			if( $options['reflectClass'] )															//  it is enabled to ...
				$class->reflection		= $classReflection;											//  store the class reflection object
			$classes[$controller]	= $class;

			if( !$options['readMethods'] )															//  do not read class methods
				continue;																			//  we're done here

			$methods	= $classReflection->getMethods( $options['methodFilter'] );
			foreach( $methods as $methodReflection ){
				$method	= new stdClass();
				$method->name		= $methodReflection->name;
				$method->class		= $methodReflection->getDeclaringClass()->getName();
				if( $options['skipInherited'] )														//  skipping inherited methods is enabled
					if( $method->class !== $className )												//  method is inherited
						continue;																	//  skip this method
				if( $options['skipFramework'] )														//  skipping framework methods is enabled
					if( substr( $method->class, 0, 4 ) == "CMF_" )									//  method is inherited from framework
						continue;																	//  skip this method
				if( $options['skipMagic'] )															//  skipping magic methods is enabled
					if( substr( $method->name, 0, 2 ) == "__" )										//  method is magic
						continue;																	//  skip this method
				if( $options['reflectMethod'] )														//  reflecting methods is enabled to
					$method->reflection	= $methodReflection;										//  store the method reflection object
				$method->parameters	= [];
				$class->methods[$method->name]	= $method;

				if( !$options['readParameters'] )													//  do not read method parameters
					continue;																		//  we're done here

				$parameters	= $methodReflection->getParameters();
				foreach( $parameters as $parameterReflection ){
					$parameter	= new stdClass();
					$parameter->name		= $parameterReflection->name;
					$method->parameters[$parameter->name]	= $parameter;
					if( $options['reflectParameter'] )
						$parameter->reflection	= $parameterReflection;
				}
			}
			ksort( $class->methods );
		}

		ksort( $classes );
		return $classes;
	}
}

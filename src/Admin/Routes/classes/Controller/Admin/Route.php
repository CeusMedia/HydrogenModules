<?php

use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\Common\XML\DOM\Builder as XmlBuilder;
use CeusMedia\Common\XML\DOM\Node as XmlNode;
use CeusMedia\Common\XML\ElementReader as XmlReader;
use CeusMedia\HydrogenFramework\Controller;

class Controller_Admin_Route extends Controller
{
	protected HttpRequest $request;
	protected Logic_Frontend $frontend;
	protected string $fileName;
	protected Model_Route $model;
	protected ?string $source				= NULL;
	protected array $routes					= [];
	protected array $routeMapBySource		= [];

	/**
	 *	@param		int|string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function activate( int|string $id ): void
	{
		switch( $this->source ){
			case 'Database':
				$this->model->edit( $id, ['status' => 1] );
				break;
			case 'XML':
			default:
				if( !array_key_exists( $id, $this->routes ) )
					$this->restart( NULL, TRUE );
				$this->routes[$id]->status	= TRUE;
				$this->saveRoutes();
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function add(): void
	{
		if( $this->request->has( 'save' ) ){
			switch( $this->source ){
				case 'Database':
					$this->model->add( [
						'status'	=> $this->request->get( 'status' ),
						'regex'		=> $this->request->get( 'regex' ),
						'code'		=> $this->request->get( 'code' ),
						'source'	=> $this->request->get( 'source' ),
						'target'	=> $this->request->get( 'target' ),
						'title'		=> $this->request->get( 'title' ),
						'createdAt'	=> time(),
					] );
					break;
				case 'XML':
				default:
					$this->routes[]	= (object) [
						'source'	=> $this->request->get( 'source' ),
						'target'	=> $this->request->get( 'target' ),
						'code'		=> $this->request->get( 'code' ),
						'status'	=> $this->request->get( 'status' ),
						'regex'		=> $this->request->get( 'regex' ),
					];
					$this->saveRoutes();
			}
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	/**
	 *	@param		int|string $id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function deactivate( int|string $id ): void
	{
		switch( $this->source ){
			case 'Database':
				$this->model->edit( $id, ['status' => 0] );
				break;
			case 'XML':
			default:
				if( !array_key_exists( $id, $this->routes ) )
					$this->restart( NULL, TRUE );
				$this->routes[$id]->status	= FALSE;
				$this->saveRoutes();
		}
		$this->restart( NULL, TRUE );
	}

	/**
	 *	@param		int|string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function edit( int|string $id ): void
	{
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			switch( $this->source ){
				case 'Database':
					$this->model->edit( $id, [
						'source'		=> $this->request->get( 'source' ),
						'target'		=> $this->request->get( 'target' ),
						'code'			=> $this->request->get( 'code' ),
						'status'		=> $this->request->get( 'status' ),
						'regex'			=> $this->request->get( 'regex' ),
//						'modifiedAt'	=> time(),
					] );
					break;
				case 'XML':
				default:
					$this->routes[$id]->source	= $this->request->get( 'source' );
					$this->routes[$id]->target	= $this->request->get( 'target' );
					$this->routes[$id]->code	= $this->request->get( 'code' );
					$this->routes[$id]->status	= $this->request->get( 'status' );
					$this->routes[$id]->regex	= $this->request->get( 'regex' );
					$this->saveRoutes();
			}
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'route', $this->routes[$id] );
	}

	public function index(): void
	{
	}

	/**
	 *	@param		int|string		$id
	 *	@return		void
	 *	@throws		\Psr\SimpleCache\InvalidArgumentException
	 */
	public function remove( int|string $id ): void
	{
		switch( $this->source ){
			case 'Database':
				$route	= $this->model->get( $id );
				if( !$route )
					$this->restart( NULL, TRUE );
				$this->model->remove( $id );
				break;
			case 'XML':
			default:
				if( !array_key_exists( $id, $this->routes ) )
					$this->restart( NULL, TRUE );
				unset( $this->routes[$id] );
				$this->saveRoutes();
		}
		$this->restart( NULL, TRUE );
	}

	protected function __onInit(): void
	{
		$this->request	= $this->env->getRequest();
		$this->frontend	= Logic_Frontend::getInstance( $this->env );
		$this->source	= $this->frontend->getModuleConfigValue( 'Server_Router', 'source' );
		switch( $this->source ){
			case 'Database':
				$this->model	= new Model_Route( $this->env );
				foreach( $this->model->getAll() as $route ){
					$this->routes[$route->routeId]	= $route;
					$this->routeMapBySource[$route->source]	= $route;
				}
				break;
			case 'XML':
			default:
				$this->fileName	= $this->frontend->getPath()."config/routes.xml";
				if( file_exists( $this->fileName ) ){
					$xml	= XmlReader::readFile( $this->fileName );
					foreach( $xml as $route ){
						$id	= md5( (string) $route->source );
						$this->routes[$id]	= (object) array(
							'routeId'		=> $id,
							'source'		=> (string) $route->source,
							'target'		=> (string) $route->target,
							'status'		=> (int) $route->getAttribute( 'status' ),
							'code'			=> (int) $route->getAttribute( 'code' ),
							'regex'			=> (bool) $route->getAttribute( 'regex' ),
							'title'			=> '',
						);
						if( $route->hasAttribute( 'title' ) )
							$this->routes[$id]->title	= (string) $route->getAttribute( 'title' );
					}
				}
				foreach( $this->routes as $route ){
					$this->routeMapBySource[$route->source]	= $route;
				}
		}
		$this->addData( 'routes', $this->routes );
		$this->addData( 'routesBySource', $this->routeMapBySource );
	}

	/**
	 *	@return		int
	 */
	protected function saveRoutes(): int
	{
		$root	= new XmlNode( 'routes' );
		foreach( $this->routes as $route ){
			$child	= new XmlNode( 'route' );
			$child->addChild( new XmlNode( 'source', $route->source ) );
			$child->addChild( new XmlNode( 'target', $route->target ) );
			$child->setAttribute( 'status', (int) $route->status );
			$child->setAttribute( 'regex', (int) $route->regex );
			$child->setAttribute( 'code', (int) $route->code );
			$root->addChild( $child );
		}
		try{
			$xml	= XmlBuilder::build( $root );
			return FileWriter::save( $this->fileName, $xml );
		}
		catch( DOMException $e ){
			$this->env->getMessenger()->noteError( 'Generating routes XML failed: '.$e->getMessage() );
			return 0;
		}
	}
}

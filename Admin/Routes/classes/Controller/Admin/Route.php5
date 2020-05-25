<?php
class Controller_Admin_Route extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $fileName;
	protected $model;
	protected $source;
	protected $routes			= array();
	protected $routeMapBySource	= array();

	public function __onInit(){
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
					$xml	= XML_ElementReader::readFile( $this->fileName );
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

	public function activate( $id ){
		switch( $this->source ){
			case 'Database':
				$this->model->edit( $id, array( 'status' => 1 ) );
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

	public function add(){
		if( $this->request->has( 'save' ) ){
			switch( $this->source ){
				case 'Database':
					$route	= $this->model->add( array(
						'status'	=> $this->request->get( 'status' ),
						'regex'		=> $this->request->get( 'regex' ),
						'code'		=> $this->request->get( 'code' ),
						'source'	=> $this->request->get( 'source' ),
						'target'	=> $this->request->get( 'target' ),
						'title'		=> $this->request->get( 'title' ),
						'createdAt'	=> time(),
					) );
					break;
				case 'XML':
				default:
					$this->routes[]	= (object) array(
						'source'	=> $this->request->get( 'source' ),
						'target'	=> $this->request->get( 'target' ),
						'code'		=> $this->request->get( 'code' ),
						'status'	=> $this->request->get( 'status' ),
						'regex'		=> $this->request->get( 'regex' ),
					);
					$this->saveRoutes();
			}
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'data', (object) $this->request->getAll() );
	}

	public function deactivate( $id ){
		switch( $this->source ){
			case 'Database':
				$this->model->edit( $id, array( 'status' => 0 ) );
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

	public function edit( $id ){
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			switch( $this->source ){
				case 'Database':
					$this->model->edit( $id, array(
						'source'		=> $this->request->get( 'source' ),
						'target'		=> $this->request->get( 'target' ),
						'code'			=> $this->request->get( 'code' ),
						'status'		=> $this->request->get( 'status' ),
						'regex'			=> $this->request->get( 'regex' ),
//						'modifiedAt'	=> time(),
					) );
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

	public function index(){
	}

	public function remove( $id ){
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

	protected function saveRoutes(){
		$root	= new XML_DOM_Node( 'routes' );
		foreach( $this->routes as $route ){
			$child	= new XML_DOM_Node( 'route' );
			$child->addChild( new XML_DOM_Node( 'source', $route->source ) );
			$child->addChild( new XML_DOM_Node( 'target', $route->target ) );
			$child->setAttribute( 'status', (int) $route->status );
			$child->setAttribute( 'regex', (int) $route->regex );
			$child->setAttribute( 'code', (int) $route->code );
			$root->addChild( $child );
		}
		$xml	= XML_DOM_Builder::build( $root );
		return File_Writer::save( $this->fileName, $xml );
	}
}

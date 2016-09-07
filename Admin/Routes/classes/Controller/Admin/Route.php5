<?php
class Controller_Admin_Route extends CMF_Hydrogen_Controller{

	protected $frontend;
	protected $fileName;
	protected $routes			= array();
	protected $routeMapBySource	= array();

	public function __onInit(){
		$this->frontend	= Logic_Frontend::getInstance( $this->env );
		$this->fileName	= $this->frontend->getPath()."config/routes.xml";
		if( file_exists( $this->fileName ) ){
			$xml	= XML_ElementReader::readFile( $this->fileName );
			foreach( $xml as $route ){
				$id	= md5( (string) $route->source );
				$this->routes[$id]	= (object) array(
					'source'		=> (string) $route->source,
					'target'		=> (string) $route->target,
					'status'		=> (int) $route->getAttribute( 'status' ),
					'code'			=> (int) $route->getAttribute( 'code' ),
					'regex'			=> (bool) $route->getAttribute( 'regex' ),
				);
			}
		}
		foreach( $this->routes as $route )
			$this->routeMapBySource[$route->source]	= $route;

		$this->addData( 'routes', $this->routes );
		$this->addData( 'routesBySource', $this->routeMapBySource );
	}

	public function activate( $id ){
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		$this->routes[$id]->status	= TRUE;
		$this->saveRoutes();
		$this->restart( NULL, TRUE );
	}

	public function add(){
		if( $this->request->has( 'save' ) ){
			$this->routes[]	= (object) array(
				'source'	=> $this->request->get( 'source' ),
				'target'	=> $this->request->get( 'target' ),
				'code'		=> $this->request->get( 'code' ),
				'status'	=> $this->request->get( 'status' ),
			);
			$this->saveRoutes();
			$this->restart( NULL, TRUE );
		}
	}

	public function deactivate( $id ){
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		$this->routes[$id]->status	= FALSE;
		$this->saveRoutes();
		$this->restart( NULL, TRUE );
	}

	public function edit( $id ){
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		if( $this->request->has( 'save' ) ){
			$this->routes[$id]->source	= $this->request->get( 'source' );
			$this->routes[$id]->target	= $this->request->get( 'target' );
			$this->routes[$id]->code	= $this->request->get( 'code' );
			$this->routes[$id]->status	= $this->request->get( 'status' );
			$this->routes[$id]->regex	= $this->request->get( 'regex' );
			$this->saveRoutes();
			$this->restart( NULL, TRUE );
		}
		$this->addData( 'route', $this->routes[$nr] );
	}

	public function index(){
	}

	public function remove( $id ){
		if( !array_key_exists( $id, $this->routes ) )
			$this->restart( NULL, TRUE );
		unset( $this->routes[$id] );
		$this->saveRoutes();
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

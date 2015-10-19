<?php
class Controller_Work_Graph extends CMF_Hydrogen_Controller{

	protected $path	= "";

	protected function __onInit(){
		$this->request	= $this->env->getRequest();

		$this->modelGraph	= new Model_Work_Graph( $this->env );
		$this->modelNode	= new Model_Work_Graph_Node( $this->env );
		$this->modelEdge	= new Model_Work_Graph_Edge( $this->env );

		$graphs	= $this->modelGraph->getAll();
		if( !$graphs ){
			$this->modelGraph->add( array(
				'type'		=> 'digraph',
				'rankdir'	=> 'LR',
				'title'		=> 'test'
			) );
			$graphs		= $this->modelGraph->getAll();
		}
		$this->addData( 'graphs', $graphs );
	}

	public function addEdge( $graphId, $nodeId = NULL ){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$edgeId	= $this->modelEdge->add( $data );
		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $nodeId ? 'node/'.$nodeId : $graphId, TRUE );
	}

	public function addGraph(){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$graphId	= $this->modelGraph->add( $data );
		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $graphId, TRUE );
	}

	public function addNode( $graphId ){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$nodeId	= $this->modelNode->add( $data );
		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( 'node/'.$nodeId /*$graphId*/, TRUE );
	}

	public function editEdge( $edgeId, $nodeId = NULL ){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelEdge->edit( $edgeId, $data );
		$edge	= $this->modelEdge->get( $edgeId );
		$this->renderGraphImage( $edge->graphId, TRUE );
		$this->restart( $nodeId ? 'node/'.$nodeId : 'edge/'.$edgeId, TRUE );
	}

	public function editGraph( $graphId ){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelGraph->edit( $graphId, $data );
		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $graphId, TRUE );
	}

	public function editNode( $nodeId ){
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelNode->edit( $nodeId, $data );
		$node	= $this->modelNode->get( $nodeId );
		$this->renderGraphImage( $node->graphId, TRUE );
		$this->restart( 'node/'.$nodeId, TRUE );
	}

	public function index( $graphId = NULL){
		if( !$graphId )
			$graphId	= 1;
		$this->addData( 'graphId', $graphId );
		$this->addData( 'graph', $this->modelGraph->get( $graphId ) );
		$this->addData( 'nodes', $this->modelNode->getAllByIndex( 'graphId', $graphId ) );
	}

	public function edge( $edgeId, $nodeId = NULL ){
		$edge	= $this->modelEdge->get( $edgeId );
		$graph	= $this->modelGraph->get( $edge->graphId );
		$this->addData( 'edgeId', $edgeId );
		$this->addData( 'edge', $edge );
		$this->addData( 'nodeId', $nodeId );
		$this->addData( 'graphId', $edge->graphId );
		$this->addData( 'graph', $graph );
		$this->addData( 'nodes', $this->modelNode->getAllByIndex( 'graphId', $edge->graphId ) );
//		$this->addData( 'edges', $this->modelEdge->getAllByIndex( 'graphId', $edge->graphId ) );
	}

	public function node( $nodeId ){
		$node	= $this->modelNode->get( $nodeId );
		$graph	= $this->modelGraph->get( $node->graphId );
		$this->addData( 'nodeId', $nodeId );
		$this->addData( 'node', $node );
		$this->addData( 'graphId', $node->graphId );
		$this->addData( 'graph', $graph );
		$this->addData( 'nodes', $this->modelNode->getAllByIndex( 'graphId', $node->graphId ) );
		$this->addData( 'edgesFrom', $this->modelEdge->getAllByIndex( 'fromNodeId', $nodeId ) );
		$this->addData( 'edgesTo', $this->modelEdge->getAllByIndex( 'toNodeId', $nodeId ) );
	}

	protected function renderGraph( $graphId ){
		$graph		= $this->modelGraph->get( $graphId );
		$nodes		= $this->modelNode->getAllByIndex( 'graphId', $graphId );
		$edges		= $this->modelEdge->getAllByIndex( 'graphId', $graphId );

		if( $graph && $nodes ){

			$nodeIndex	= array();
			foreach( $nodes as $node )
				$nodeIndex[$node->nodeId]	= $node->ID;

			$indent		= '    ';
			$lines		= array();
			$title		= preg_replace( "/ /", "_", $graph->title );
			$lines[]	= $graph->type.' '.$title.' {';
			$lines[]	= $indent.'rankdir="'.$graph->rankdir.'"';
			foreach( $nodes as $node ){
				$attr	= array();
				$attr['shape']		= $node->shape ? $node->shape : $graph->nodeShape;
				$attr['style']		= $node->style ? $node->style : $graph->nodeStyle;
				$attr['color']		= $node->color ? $node->color : $graph->nodeColor;
				$attr['fillcolor']	= $node->fillcolor ? $node->fillcolor : $graph->nodeFillcolor;
				$attr['fontsize']	= $node->fontsize ? $node->fontsize : $graph->nodeFontsize;
				$attr['width']		= $node->width ? $node->width : $graph->nodeWidth;
				$attr['height']		= $node->height ? $node->height : $graph->nodeHeight;
				$attr['label']		= $node->label ? $node->label : $node->ID;
				foreach( $attr as $key => $value )
					$attr[$key]	= $key.'="'.$value.'"';
				$attr	= $attr ? ' ['.join( " ", $attr ).']' : '';
				$lines[]	= $indent.$node->ID.$attr;
			}
			$lines[]	= '';
			foreach( $edges as $edge ){
				$attr		= array();
				$attr['arrowhead']	= $edge->arrowhead ? $edge->arrowhead : $graph->edgeArrowhead;
				$attr['arrowsize']	= $edge->arrowsize ? $edge->arrowsize : $graph->edgeArrowsize;
				$attr['color']		= $edge->color ? $edge->color : $graph->edgeColor;
				$attr['fontcolor']	= $edge->fontcolor ? $edge->fontcolor : $graph->edgeFontcolor;
				$attr['fontsize']	= $edge->fontsize ? $edge->fontsize : $graph->edgeFontsize;
				$attr['label']		= $edge->label;
				foreach( $attr as $key => $value )
					$attr[$key]	= $key.'="'.$value.'"';
				$attr	= $attr ? ' ['.join( " ", $attr ).']' : '';
				$trans	= $nodeIndex[$edge->fromNodeId].'->'.$nodeIndex[$edge->toNodeId];
				$lines[]	= $indent.$trans.$attr;
			}
			$lines[]	= '}';
			$graphFile	= $this->path."graph_".rawurlencode( $graph->title );
			File_Writer::save( $graphFile.".dot", join( "\n", $lines ) );
		}
	}

	protected function renderGraphImage( $graphId, $force = NULL ){
		$graph		= $this->modelGraph->get( $graphId );
		if( $graph ){
			$graphFile	= $this->path."graph_".rawurlencode( $graph->title );
			if( !file_exists( $graphFile.".dot" ) || $force )
				$this->renderGraph( $graphId );
			$command	= "dot -Tpng ".$graphFile.".dot > ".$graphFile.".png";
			exec( $command );
		}
	}

	public function view( $graphId, $force = NULL ){
		$graph		= $this->modelGraph->get( $graphId );
		if( $graph ){
			$graphFile	= $this->path."graph_".rawurlencode( $graph->title );
			if( !file_exists( $graphFile.".png" ) || $force )
				$this->renderGraphImage( $graphId, $force );
			header( "Content-Type: image/png" );
			header( "Content-Length: ".filesize( $graphFile.".png" ) );
			readfile( $graphFile.".png" );
			exit;
		}
	}

/*	protected function checkGraph( $graphId ){
		$graph		= $this->modelGraph->get( $graphId );
		if( $graph )
			return $graph;
		throw new RuntimeException( 'Graph with ID %d is not existing', $graphId );
	}

	protected function getGraph( $graphId, $withNodes = NULL, $withEdges = NULL ){
		$graph		= $this->modelGraph->get( $graphId );
		if( !$graph )
			throw new RuntimeException( 'Graph with ID %d is not existing', $graphId );
		if( $withNodes ){
			$graph->nodes	= array();
			foreach( $this->modelNodes->getAll() as $node )
				$graph->nodes[$node->nodeId]	= $node;
		}
		if( $withEdges ){
			$graph->edges	= array();
			foreach( $this->modelEdges->getAll() as $edge )
				$graph->edges[$edge->edgeId]	= $edge;
		}
		return $graph;
	}
*/
}

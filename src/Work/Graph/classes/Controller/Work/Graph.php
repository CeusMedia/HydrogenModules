<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\Common\FS\File\Writer as FileWriter;
use CeusMedia\Common\Net\HTTP\Request as HttpRequest;
use CeusMedia\HydrogenFramework\Controller;
use CeusMedia\HydrogenFramework\Environment\Resource\Messenger as MessengerResource;

class Controller_Work_Graph extends Controller
{
	protected HttpRequest $request;
	protected Dictionary $session;
	protected MessengerResource $messenger;

	protected Model_Work_Graph $modelGraph;
	protected Model_Work_Graph_Node $modelNode;
	protected Model_Work_Graph_Edge $modelEdge;

	public function addEdge( $graphId, $nodeId = NULL ): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$edgeId	= $this->modelEdge->add( $data );
		$this->modelGraph->edit( $graphId, array( 'modifiedAt' => time() ) );
//		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $nodeId ? 'node/'.$nodeId : $graphId, TRUE );
	}

	public function addGraph(): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$data['createdAt']	= time();
		$graphId	= $this->modelGraph->add( $data );
		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $graphId, TRUE );
	}

	public function addNode( $graphId ): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$nodeId	= $this->modelNode->add( $data );
		$this->modelGraph->edit( $graphId, array( 'modifiedAt' => time() ) );
//		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( 'node/'.$nodeId /*$graphId*/, TRUE );
	}

	public function editEdge( $edgeId, $nodeId = NULL ): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelEdge->edit( $edgeId, $data );
		$edge	= $this->modelEdge->get( $edgeId );
		$this->modelGraph->edit( $edge->graphId, array( 'modifiedAt' => time() ) );
//		$this->renderGraphImage( $edge->graphId, TRUE );
		$this->restart( $nodeId ? 'node/'.$nodeId : 'edge/'.$edgeId, TRUE );
	}

	public function editGraph( $graphId ): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelGraph->edit( $graphId, $data );
		$this->modelGraph->edit( $graphId, array( 'modifiedAt' => time() ) );
//		$this->renderGraphImage( $graphId, TRUE );
		$this->restart( $graphId, TRUE );
	}

	public function editNode( $nodeId ): void
	{
		$data	= $this->request->getAll();
		foreach( $data as $key => $value )
			$data[$key]	= $value !== "" ? $value : NULL;
		$this->modelNode->edit( $nodeId, $data );
		$node	= $this->modelNode->get( $nodeId );
		$this->modelGraph->edit( $node->graphId, array( 'modifiedAt' => time() ) );
//		$this->renderGraphImage( $node->graphId, TRUE );
		$this->restart( 'node/'.$nodeId, TRUE );
	}

	public function index( $graphId = NULL): void
	{
		if( $graphId )
			$this->selectGraph( $graphId );
		$graphId	= $this->session->get( 'work_graph_id' );
		if( !$graphId )
			$graphId	= 1;
		$this->addData( 'graphId', $graphId );
		$this->addData( 'graph', $this->modelGraph->get( $graphId ) );
		$this->addData( 'nodes', $this->modelNode->getAllByIndex( 'graphId', $graphId ) );
	}

	public function edge( $edgeId, $nodeId = NULL ): void
	{
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

	public function node( $nodeId ): void
	{
		$node	= $this->modelNode->get( $nodeId );
		$graph	= $this->modelGraph->get( $node->graphId );
		$this->addData( 'nodeId', $nodeId );
		$this->addData( 'node', $node );
		$this->addData( 'graphId', $node->graphId );
		$this->addData( 'graph', $graph );
		$this->addData( 'nodes', $this->modelNode->getAllByIndex( 'graphId', $node->graphId ) );
		$this->addData( 'edgesIn', $this->modelEdge->getAllByIndex( 'toNodeId', $nodeId ) );
		$this->addData( 'edgesOut', $this->modelEdge->getAllByIndex( 'fromNodeId', $nodeId ) );
	}

	public function selectGraph( $graphId ): void
	{
		$graph	= $this->modelGraph->get( $graphId );
		if( !$graph ){
			$this->messenger->noteError( 'Invalid graph ID.' );
		}
		else{
			$this->session->set( 'work_graph_id', (int) $graphId );
		}
		$this->restart( NULL, TRUE );
	}

	public function view( $graphId, $force = NULL ): void
	{
		$graph		= $this->modelGraph->get( $graphId );
		if( !$graph )
			throw new RuntimeException( 'Invalid graph ID' );

		$force		= $graph->modifiedAt > $graph->renderedAt || $force;
		if( !strlen( $graph->image ) || $force ){
			$this->renderGraphImage( $graphId, $force );
			$graph		= $this->modelGraph->get( $graphId );
		}
		header( "Content-Type: image/png" );
		header( "Content-Length: ".strlen( $graph->image ) );
		print( $graph->image );
		exit;
	}

	protected function __onInit(): void
	{
		$this->request		= $this->env->getRequest();
		$this->session		= $this->env->getSession();
		$this->messenger	= $this->env->getMessenger();

		$this->modelGraph	= new Model_Work_Graph( $this->env );
		$this->modelNode	= new Model_Work_Graph_Node( $this->env );
		$this->modelEdge	= new Model_Work_Graph_Edge( $this->env );

		$graphs	= $this->modelGraph->getAll();
		if( !$graphs ){
			$this->modelGraph->add( [
				'type'			=> 'digraph',
				'rankdir'		=> 'LR',
				'title'			=> 'test',
				'createdAt'		=> time(),
				'modifiedAt'	=> time(),
			] );
			$graphs		= $this->modelGraph->getAll();
		}
		$this->addData( 'graphs', $graphs );
	}

	protected function renderGraph( string $graphId ): void
	{
		$graph		= $this->modelGraph->get( $graphId );
		$nodes		= $this->modelNode->getAllByIndex( 'graphId', $graphId );
		$edges		= $this->modelEdge->getAllByIndex( 'graphId', $graphId );

//		if( !( $graph && $nodes ) )
//			return;
		if( !$graph )
			return;

		$nodeIndex	= [];
		foreach( $nodes as $node )
			$nodeIndex[$node->nodeId]	= $node->ID;

		$indent		= '    ';
		$lines		= [];
		$lines[]	= $graph->type.' graph_'.$graph->graphId.' {';
		$lines[]	= $indent.'rankdir="'.$graph->rankdir.'"';
		foreach( $nodes as $node ){
			$attr	= [];
			$attr['shape']		= $node->shape ?: $graph->nodeShape;
			$attr['style']		= $node->style ?: $graph->nodeStyle;
			$attr['color']		= $node->color ?: $graph->nodeColor;
			$attr['fillcolor']	= $node->fillcolor ?: $graph->nodeFillcolor;
			$attr['fontsize']	= $node->fontsize ?: $graph->nodeFontsize;
			$attr['width']		= $node->width ?: $graph->nodeWidth;
			$attr['height']		= $node->height ?: $graph->nodeHeight;
			$attr['label']		= $node->label ?: $node->ID;
			foreach( $attr as $key => $value )
				$attr[$key]	= $key.'="'.$value.'"';
			$attr	= $attr ? ' ['.join( " ", $attr ).']' : '';
			$lines[]	= $indent.$node->ID.$attr;
		}
		$lines[]	= '';
		foreach( $edges as $edge ){
			$attr		= [];
			$attr['arrowhead']	= $edge->arrowhead ?: $graph->edgeArrowhead;
			$attr['arrowsize']	= $edge->arrowsize ?: $graph->edgeArrowsize;
			$attr['color']		= $edge->color ?: $graph->edgeColor;
			$attr['fontcolor']	= $edge->fontcolor ?: $graph->edgeFontcolor;
			$attr['fontsize']	= $edge->fontsize ?: $graph->edgeFontsize;
			$attr['label']		= $edge->label;
			foreach( $attr as $key => $value )
				$attr[$key]	= $key.'="'.$value.'"';
			$attr	= $attr ? ' ['.join( " ", $attr ).']' : '';
			$trans	= $nodeIndex[$edge->fromNodeId].'->'.$nodeIndex[$edge->toNodeId];
			$lines[]	= $indent.$trans.$attr;
		}
		$lines[]	= '}';
		$this->modelGraph->edit( $graphId, array(
			'dot'	=> join( "\n", $lines ),
		), FALSE );
	}

	protected function renderGraphImage( string $graphId, bool $force = NULL )
	{
		$graph		= $this->modelGraph->get( $graphId );
		if( !$graph )
			throw new RuntimeException( 'Invalid graph ID' );

		$force		= $graph->modifiedAt > $graph->renderedAt || $force;
		if( !strlen( $graph->image ) || $force ){
			if( !strlen( $graph->dot ) || $force ){
				$this->renderGraph( $graphId );
				$graph		= $this->modelGraph->get( $graphId );
			}
			$graphFile	= "graph_".$graph->graphId;
			FileWriter::save( $graphFile.".dot", $graph->dot );
			$command	= "dot -Tpng ".$graphFile.".dot > ".$graphFile.".png";
			exec( $command );
			$this->modelGraph->edit( $graphId, array(
				'image'			=> file_get_contents( $graphFile.".png" ),
				'renderedAt'	=> time(),
			), FALSE );
			unlink( $graphFile.".dot" );
			unlink( $graphFile.".png" );
		}
	}

/*	protected function checkGraph( string $graphId )
	{
		$graph		= $this->modelGraph->get( $graphId );
		if( $graph )
			return $graph;
		throw new RuntimeException( 'Graph with ID %d is not existing', $graphId );
	}

	protected function getGraph( string $graphId, bool $withNodes = NULL, bool $withEdges = NULL )
	{
		$graph		= $this->modelGraph->get( $graphId );
		if( !$graph )
			throw new RuntimeException( 'Graph with ID %d is not existing', $graphId );
		if( $withNodes ){
			$graph->nodes	= [];
			foreach( $this->modelNodes->getAll() as $node )
				$graph->nodes[$node->nodeId]	= $node;
		}
		if( $withEdges ){
			$graph->edges	= [];
			foreach( $this->modelEdges->getAll() as $edge )
				$graph->edges[$edge->edgeId]	= $edge;
		}
		return $graph;
	}
*/
}

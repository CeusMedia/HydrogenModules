<?php
class Logic_Module_Relation{
	public $logic;
	public $nodes	= array();
	public $skip	= array();

	public function __construct( Logic_Module $logic ){
		$this->logic	= $logic;
		$this->modules	= $logic->model->getAll();
	}

	public function loadModule( $moduleId, $level = 0 ){
		if( array_key_exists( $moduleId, $this->nodes ) ){
#			$this->nodes[$moduleId]->level	= max( $this->nodes[$moduleId]->level, $level );
			return;
		}
		$this->nodes[$moduleId]	= (object) array(
			'name'	=> $moduleId,
			'level'	=> $level,
			'in'	=> array(),
			'out'	=> array()
		);
		$needs	= array_keys( $this->logic->model->getAllNeededModules( $moduleId, TRUE ) );
		foreach( $needs as $neededModuleId ){
			if( in_array( $neededModuleId, $this->skip ) )
				continue;
			$this->loadModule( $neededModuleId, $level + 1 );
			$this->nodes[$moduleId]->out[$neededModuleId]	= $this->nodes[$neededModuleId];
		}
		if( $level == 0 ){
			foreach( $this->nodes as $name => $node )
				foreach( $node->out as $out )
					$this->nodes[$out->name]->in[$name]	= $node;
		}
	}

	public function load( $moduleId, $type = 'needs', $level = 0 ){
		if( array_key_exists( $moduleId, $this->nodes ) ){
			$this->nodes[$moduleId]->level	= max( $this->nodes[$moduleId]->level, $level );
			return;
		}
		$module	= $this->logic->getModule( $moduleId );
		if( $module ){
			$this->nodes[$moduleId]	= (object) array(
				'moduleId'	=> $moduleId,
				'level'		=> $level,
				'status'	=> $this->logic->model->getStatus( $moduleId ),
				'in'		=> array(),
				'out'		=> array()
			);
			$relations	= $module->relations->$type;
			foreach( $relations as $neededModuleId ){
				if( in_array( $neededModuleId, $this->skip ) )
					continue;
				$this->load( $neededModuleId, $type, $level + 1 );
				$this->nodes[$moduleId]->out[$neededModuleId]	= $this->nodes[$neededModuleId];
			}
		}
	}

	public function loadModulesRelatingTo( $moduleId, $type = 'needs', $recursive = FALSE, $level = 0 ){
		if( array_key_exists( $moduleId, $this->nodes ) ){
			$this->nodes[$moduleId]->level	= max( $this->nodes[$moduleId]->level, $level );
			return;
		}
		$this->nodes[$moduleId]	= (object) array(
			'moduleId'	=> $moduleId,
			'level'		=> $level,
			'status'	=> $this->logic->model->getStatus( $moduleId ),
			'in'		=> array(),
			'out'		=> array()
		);
		$module	= $this->logic->getModule( $moduleId );
		if( $module ){
			$relations	= $this->logic->model->getAll( array( 'relation:'.$type => $moduleId ) );
			foreach( array_keys( $relations ) as $neededModuleId ){
				if( in_array( $neededModuleId, $this->skip ) )
					continue;
				if( 0 || $recursive ){
					$this->loadModulesRelatingTo( $neededModuleId, $type, $recursive, $level + 1 );
				}
				else{
					$this->nodes[$neededModuleId] = (object) array(
						'moduleId'	=> $neededModuleId,
						'level'		=> $level + 1,
						'status'	=> $this->logic->model->getStatus( $neededModuleId ),
						'in'		=> array(),
						'out'		=> array()
					);
				}
				$this->nodes[$moduleId]->in[$neededModuleId]	= $this->nodes[$neededModuleId];
			}
		}
	}

	public function renderGraph( $moduleId, $type = 'needs' ){										//  @todo	rename to produceGraphvizGraph and make indepentent from need/support
		$this->nodes	= array();
		$this->load( $moduleId, $type );
		$nodes	= array();
		$edges	= array();
		$number	= 0;
		foreach( $this->nodes as $node ){
			$style		= "";
			$label	= $node->moduleId;
			if( array_key_exists( $node->moduleId, $this->modules ) )
				$label	= $this->modules[$node->moduleId]->title;
			switch( $node->status ){
				case 4:
					$style	= ' color="#7F7F00" fillcolor="#FFFFCF"';
					break;
				case 2:
					$style	= ' color="#007F00" fillcolor="#CFFFCF"';
					break;
				case 0:
					$style	= ' color="#7F0000" fillcolor="#FFCFCF"';
					break;

			}
/*			if( count( $nodes ) ){
			}
			else
				$style	= ' fillcolor="#EFEFEF"';
*/			$nodes[]	= $node->moduleId.' [label="'.$label.'" fontsize=8 shape=box color=black style=filled'.$style.'];';
			foreach( $node->out as $out )
				$edges[]	= $node->moduleId.' -> '.$out->moduleId.' []';
			$number++;
		}
		$graph	= "digraph {\n\t".join( "\n\t", $nodes )."\n\t".join( "\n\t", $edges )."\n}";
		return $graph;
	}

	public function renderRelatingGraph( $moduleId, $type = 'needs', $recursive = FALSE ){										//  @todo	rename to produceGraphvizGraph and make indepentent from need/support
		$this->nodes	= array();
		$this->loadModulesRelatingTo( $moduleId, $type, $recursive );
		$nodes	= array();
		$edges	= array();
		$number	= 0;
		foreach( $this->nodes as $node ){
			$style		= "";
			$label	= $node->moduleId;
			if( array_key_exists( $node->moduleId, $this->modules ) )
				$label	= $this->modules[$node->moduleId]->title;
			switch( $node->status ){
				case 4:
					$style	= ' color="#7F7F00" fillcolor="#FFFFCF"';
					break;
				case 2:
					$style	= ' color="#007F00" fillcolor="#CFFFCF"';
					break;
				case 0:
					$style	= ' color="#7F0000" fillcolor="#FFCFCF"';
					break;

			}
/*			if( count( $nodes ) ){
			}
			else
				$style	= ' fillcolor="#EFEFEF"';
*/			$nodes[]	= $node->moduleId.' [label="'.$label.'" fontsize=8 shape=box color=black style=filled'.$style.'];';
			foreach( $node->out as $out )
				$edges[]	= $node->moduleId.' -> '.$out->moduleId.' []';
			foreach( $node->in as $in )
				$edges[]	= $in->moduleId.' -> '.$node->moduleId.' []';
			$number++;
		}
		$graph	= "digraph {\n\t".join( "\n\t", $nodes )."\n\t".join( "\n\t", $edges )."\n}";
		return $graph;
	}

	public function getOrder(){
		if( !$this->nodes )
			throw new Exception( 'No modules loaded' );
		$list	= array();
		$depth	= 0;
		foreach( $this->nodes as $name => $node )
			$depth	= max( $depth, $node->level );
		foreach( $this->nodes as $name => $node ){
			$a	= array(
				(string) ( $depth - $node->level ),
				(string) count( $node->out ),
				(string) count( $node->in )
			);
			$list[$name]	= implode( '.', $a );
		}
		asort( $list );
		return $list;
	}
}
?>
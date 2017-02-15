<?php
class View_Helper_ItemRelationLister{

	protected $env;
	protected $relations;
	protected $hookResource;
	protected $hookEvent;
	protected $hookIndices	= array();
	protected $tableClass	= '';
	protected $renderMode	= 'table';
	protected $activeOnly	= FALSE;
	protected $linkable		= TRUE;

	protected $types;

	public function __construct( $env ){
		$this->env		= $env;
//		$this->env->getLanguage();
		$this->types	= array(
			'relation'	=> UI_HTML_Tag::create( 'abbr', '%d Verknüpfungen', array( 'title' => 'Bleiben beim Entfernen dieses Eintrages erhalten.' ) ),
			'entity'	=> UI_HTML_Tag::create( 'abbr', '%d Einträge', array( 'title' => 'Werden beim Entfernen dieses Eintrages gelöscht.' ) ),
		);
	}

	static public function enqueueRelations( $data, $module, $type, $items, $label, $controller = NULL, $action = NULL ){
		if( !isset( $data->list ) )
			$data->list	= array();
		if( count( $items ) ){
			$data->list[]	= (object) array(
				'module'		=> (object) array(
					'id'		=> $module->id,
					'label'		=> $module->title,
				),
				'type'			=> $type,
				'label'			=> $label,
				'items'			=> $items,
				'count'			=> count( $items ),
				'controller'	=> $controller,
				'action'		=> $action,
			);
		}
	}

	public function hasRelations(){
		if( $this->relations === NULL )
			$this->load();
		return (boolean) $this->relations;
	}

	protected function load(){
		if( !$this->hookResource || !$this->hookEvent )
			throw new RuntimeException( 'No hook for event call defined' );
		if( !$this->hookIndices )
			throw new RuntimeException( 'No indices set' );
		$data	= array_merge( $this->hookIndices, array(
			'activeOnly'	=> $this->activeOnly,
			'linkable'		=> $this->linkable,
		) );
		$data	= (object) $data;
		$this->env->getCaptain()->callHook( $this->hookResource, $this->hookEvent, $this, $data );
		$this->relations	= $data->list;
	}

	public function render( ){
		if( $this->relations === NULL )
			$this->load();
		if( $this->renderMode == 'table' )
			return $this->renderRelationsAsTable();
		return $this->renderRelationsAsList();
	}

	protected function renderRelationsAsList(){
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'roleId' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$list		= array();
		foreach( $this->relations as $relation ){
			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$items	= array();
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
			$access	= $acl->has( $relation->controller, $relation->action );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && (bool) $item->id ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				}
				$items[]	= UI_HTML_Tag::create( 'li', $label );
			}
			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$type.')', array( 'class' => 'muted' ) );
			$list[]	= UI_HTML_Tag::create( 'h5', $relation->label.'&nbsp;'.$count ).$items;
		}
		return UI_HTML_Tag::create( 'div', $list );
	}

	protected function renderRelationsAsTable(){
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'roleId' );
//		$fullAccess
		$acl		= $this->env->getAcl();
		$rows		= array();
		foreach( $this->relations as $relation ){
			$items	= array();
			$icon	= '';
			if( !empty( $relation->icon ) )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
			$access	= $acl->has( $relation->controller, $relation->action );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && $item->id ){
					$controller	= str_replace( "_", "/", strtolower( $relation->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$relation->action.'/'.$arguments;
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				}
				$items[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'autocut' ) );
			}

			$type	= sprintf( $this->types[$relation->type], $relation->count );
			$type	= UI_HTML_Tag::create( 'small', $type, array( 'class' => 'muted' ) );

			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$relation->count.')', array( 'class' => 'muted' ) );
			$label	= UI_HTML_Tag::create( 'big', $relation->label ).'<br/>'.$type;
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $label, array( 'class' => 'cell-relation-label' ) ),
				UI_HTML_Tag::create( 'td', $items, array( 'class' => 'cell-relation-items' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "30%", "" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
			"Typ",
			"Verknüpfungen / Einträge",
		) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped item-relation-lister '.$this->tableClass,
		) );

		$style	= '<style>
table.item-relation-lister {
	table-layout: fixed;
	}
table.item-relation-lister ul {
	}
table.item-relation-lister.limited ul {
	padding: 2px 4px;
	margin: 0px;
	max-height: 180px;
	overflow-y: auto;
/*	border: 1px solid #CFCFCF;
	border-radius: 3px;*/
/*	background-color: #FFF7EE;
	background-color: #FFFFFF;*/
	}
</style>';

		return $table.$style;
	}

	public function setActiveOnly( $boolean ){
		$this->activeOnly	= $boolean;
		$this->relations	= NULL;
	}

	public function setHook( $resource, $event, $indices ){
		$this->hookResource		= $resource;
		$this->hookEvent		= $event;
		$this->hookIndices		= $indices;
		$this->relations	= NULL;
	}

	public function setLinkable( $boolean ){
		$this->linkable		= $boolean;
		$this->relations	= NULL;
	}

	public function setMode( $mode ){
		$this->renderMode	= $mode;
	}

	public function setTableClass( $class ){
		$this->tableClass	= $class;
	}
}
?>

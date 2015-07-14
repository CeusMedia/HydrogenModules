<?php
class View_Helper_ItemRelationLister{

	protected $env;
	protected $relations;
	protected $resource;
	protected $event;
	protected $types;

	public function __construct( $env ){
		$this->env		= $env;
//		$this->env->getLanguage();
		$this->types	= array(
			'relation'	=> 'Verknüpfung',
			'entity'	=> 'Eintrag',
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

	public function callForRelations( $resource, $event, $data ){
		$data	= (object) $data;
		$this->env->getCaptain()->callHook( $resource, $event, $this, $data );
		$this->relations	= $data->list;
	}

	public function hasRelations(){
		return (boolean) $this->relations;
	}

	public function render( $resource, $event, $data ){
		$this->callForRelations( $resource, $event, $data );
		return $this->renderRelations();
	}

	public function renderRelations(){
		return $this->renderRelationsAsTable();
		return $this->renderRelationsAsList();
	}

/*	public function renderPanel( $heading, $textTop, $buttonCancel, $buttonSave ){
	}*/

	public function renderRelationsAsList(){
		if( !$this->relations )
			return '';
		$roleId		= $this->env->getSession()->get( 'roleId' );
		$acl		= $this->env->getAcl();
		$list		= array();
		foreach( $this->relations as $relation ){
			$items	= array();
			$icon	= '';
			if( $relation->icon )
				$icon	= UI_HTML_Tag::create( 'i', '', array( 'class' => $relation->icon ) ).'&nbsp;';
			$access		= $acl->has( $item->controller, $item->action );
			foreach( $relation->items as $item ){
				$label		= $icon.$item->label;
				if( $access && (bool) $item->id ){
					$controller	= str_replace( "_", "/", strtolower( $item->controller ) );
					$arguments	= !empty( $item->arguments ) ? join( "/", $item->arguments ) : $item->id;
					$url		= './'.$controller.'/'.$item->action.'/'.$arguments;
					$label		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				}
				$items[]	= UI_HTML_Tag::create( 'li', $label );
			}
			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$relation->count.')', array( 'class' => 'muted' ) );
			$items	= UI_HTML_Tag::create( 'dd', $items );
			$list[]	= UI_HTML_Tag::create( 'dt', $relation->label.'&nbsp;'.$count ).$items;
		}
		return UI_HTML_Tag::create( 'dl', $list, array( 'class' => 'not-dl-horizontal' ) );
	}

	public function renderRelationsAsTable(){
		if( !$this->relations )
			return '';
//		$roleId		= $this->env->getSession()->get( 'roleId' );
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
			$items	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'unstyled' ) );
			$count	= UI_HTML_Tag::create( 'small', '('.$relation->count.')', array( 'class' => 'muted' ) );
			$items	= UI_HTML_Tag::create( 'dd', $items );
			$rows[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $this->types[$relation->type], array( 'class' => 'cell-relation-type' ) ),
				UI_HTML_Tag::create( 'td', $relation->label.'&nbsp;'.$count, array( 'class' => 'cell-relation-label' ) ),
				UI_HTML_Tag::create( 'td', $items, array( 'class' => 'cell-relation-items' ) ),
			) );
		}
		$colgroup	= UI_HTML_Elements::ColumnGroup( "120px", "30%", "" );
		$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array(
			"Art",
			"Typ / Modul",
			"Einträge",
		) ) );
		$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'class'		=> 'table table-striped item-relation-lister',
		) );

		$style	= '<style>
table.item-relation-lister {
	table-layout: fixed;
	}
table.item-relation-lister ul {
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
}
?>

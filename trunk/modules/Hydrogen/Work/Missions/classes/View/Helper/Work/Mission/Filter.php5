<?php
/*class View_Helper_MultiButtonGroupMultiToolbar{

	public $toolbars	= array();

	public function addButton( $toolbarId, $groupId, $button ){
		if( !isset( $this->toolbars[$toolbarId] ) )
			$this->toolbars[$toolbarId]	= array();
		if( !isset( $this->toolbars[$toolbarId][$groupId] ) )
			$this->toolbars[$toolbarId][$groupId]	= array();
		$this->toolbars[$toolbarId][$groupId][]	= $button;
	}

	public function addButtonGroup( $toolbarId, $groupId, $groupButtons ){
		foreach( $groupButtons as $button )
			$this->addButton( $toolbarId, $groupId, $button );
	}

	public function render(){
		$list	= array();
		foreach( $this->toolbars as $toolbarId => $buttonGroups ){
			$buttons	= array();
			foreach( $buttonGroups as $groupId => $buttonGroup ){
				$buttons[]	= '<div class="btn-group" id="'.$groupId.'">'.join( " ", $buttonGroup ).'</div>';
			}
			$toolbar	= '<div class="btn-toolbar" id="'.$toolbarId.'">'.join( " ", $buttons ).'</div>';
			$list[]		= '<div class="pull-left">'.$toolbar.'</div>';
		}
		return join( $list );
	}

	public function sort( $level = 0 ){
		if( $level == 0 )
			ksort( $this->toolbars );
		else if( $level == 1 )
			foreach( array_keys( $this->toolbars ) as $toolbarId )
				ksort( $this->toolbars[$toolbarId] );
		else if( $level == 2 )
			foreach( $this->toolbars as $toolbarId => $buttonGroups )
				foreach( array_keys( $buttonGroups ) as $groupId )
					ksort( $this->toolbars[$toolbarId][$groupId] );
	}
}*/

/*class View_Helper_MultiCheckDropdownButton{

	protected $url			= "./work/mission/setFilter/";
	protected $filterValues;
	protected $buttonClass;
	protected $buttonLabel;
	protected $useItemIcons	= FALSE;

	public function __construct( $filterKey, $filterValues, $buttonLabel ){
		$this->buttonLabel	= $buttonLabel;
		$this->filterKey	= $filterKey;
		$this->filterValues	= $filterValues;
	}

	public function addDivider(){
		$this->items[]	= 'divider';
	}

	public function addItem( $value, $label, $class = NULL, $icon = NULL ){
		$this->items[]	= (object) array(
			'value'		=> $value,
			'label'		=> $label,
			'class'		=> $class,
			'icon'		=> $icon,
		);
	}

	public function render(){
		$list		= array();
		foreach( $this->items as $item ){
			if( $item == "divider" )
				$list[]	= UI_HTML_Tag::create( 'li',NULL, array( 'class' => "divider" ) );
			else{
				$icon		= "";
				$isActive	= (int) in_array( $item->value, $this->filterValues );
				if( $this->useItemIcons ){
					$icon	= $item->icon ? $item->icon : "empty";
					$icon	= UI_HTML_Tag::create( 'i', "", array( 'class' => 'icon-'.$icon ) );
				}
				$tick		= $isActive ? "ok" : "empty";
				$tick		= UI_HTML_Tag::create( 'i', "", array( 'class' => 'icon-'.$tick ) );

				$url		= $this->url.$this->filterKey.'/'.$item->value.'/'.abs( $isActive - 1 );
				$label		= $tick.'&nbsp;&nbsp;&nbsp;'.$item->label;
				$link		= UI_HTML_Tag::create( 'a', $label, array( 'href' => $url ) );
				$attributes	= array( 'class' => 'selectable '.$item->class );
				$list[]		= UI_HTML_Tag::create( 'li', $link, $attributes );
			}
		}
		$dropdown	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => "dropdown-menu " ) );				//  optional class: pull-right
		$caret		= UI_HTML_Tag::create( 'span', "", array( 'class' => "caret" ) );
		$attributes	= array(
			'class'			=> 'dropdown-toggle btn '.$this->buttonClass,
			'data-toggle'	=> "dropdown"
		);
		$button		= UI_HTML_Tag::create( 'button', $this->buttonLabel." ".$caret, $attributes );
		return $button.$dropdown;
	}

	public function setButtonClass( $class ){
		$this->buttonClass	= $class;
	}

	public function useItemIcons( $useItemIcons = TRUE ){
		$this->useItemIcons		= $useItemIcons;
	}
}*/

class View_Helper_MissionFilter{

	protected $env;
	protected $options	= array(
		'animation'	=> 'slide',
		'speedShow'	=> 500,
		'speedHide'	=> 300
	);

	public function __construct( $env ){
		$this->env	= $env;
	}

	/**
	 *	...
	 *	@access		public
	 *	@param		string		$name			...
	 *	@param		array		$options		...
	 *	@param		array		$selection		...
	 *	@param		class		$class			...
	 *	@param		array		$data			...
	 *	@param		string		$dataKey		...
	 *	@param		integer		$orderBy		0 - none, 1 - by option key, 2 - by option label
	 *	@return		array		List of rendered filter list options
	 */
	function renderFilterOptions( $name, $options, $selection, $class = "", $data = array(), $dataKey = NULL, $orderBy = NULL ){
		$index	= array();
		$list	= array();
		foreach( $options as $optionKey => $optionLabel ){
			$id	= array_shift( explode( " ", $class ) );
			$id	= preg_replace( "/[^a-z0-9]/", "_", $id ).'_'.$optionKey;
			$state = $dataKey."-".$optionKey;
			$count	= 0;
			if( $data && $dataKey ){
				foreach( $data as $item )
					$count += $item->$dataKey == $optionKey ? 1 : 0;
			}
			$isChecked	= in_array( $optionKey, $selection );
			$classes	= array( $class, $state );
			if( $isChecked )
				$classes[]	= 'selected';
			$attributes	= array(
				'type'		=> 'checkbox',
				'name'		=> $name.'[]',
				'value'		=> $optionKey,
				'id'		=> $id,
				'class'		=> $class,
				'checked'	=> $isChecked ? 'checked' : NULL,
			);
			if( !( count( $options ) == 1 && $selection[0] == $optionKey ) )
				$attributes['onchange']	= 'WorkMissions.filter(this.form);';
			$input	= UI_HTML_Tag::create( 'input', NULL, $attributes );
			$label	= $optionLabel;
			if( $count ){
				$label		= $label.' <small>('.$count.')</small>';
				$classes[]	= 'used';
			}
			else{
				$label	= '<span class="empty">'.$label.'</span>';
				$classes[]	= 'empty';
			}
			$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id, 'class' => 'checkbox' ) );
//			$link	= UI_HTML_Tag::create( 'a', $label, array( 'href' => "#" ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => implode( ' ', $classes ) ) );
			if( $orderBy ){
				$orderValue = $orderBy == 1 ? $optionKey : $optionLabel;
				$index[count( $list ) - 1]	= $orderValue;
			}
		}
		if( $orderBy ){
			$list2	= array();
			natcasesort( $index );
			foreach( array_keys( $index ) as $nr )
				$list2[]	= $list[$nr];
			$list	= $list2;
		}
		return $list;
	}

	public function renderCheckboxFilter( $id, $name, $class, $optionName, $optionsMap, $optionSelection, $optionData = NULL, $optionDataKey = NULL, $optionClass = NULL, $optionOrder = NULL ){
		$isOpen	=  count( $optionsMap ) != count( $optionSelection );
		$attributes		= array(
			'type'				=> 'checkbox',
			'name'				=> $name,
			'id'				=> $id,
			'class'				=> 'optional-trigger',
			'onchange'			=> 'showOptionals(this);',
			'data-animation'	=> $this->options['animation'],
			'data-speed-show'	=> $this->options['speedShow'],
			'data-speed-hide'	=> $this->options['speedHide'],
			'checked'			=> $isOpen ? 'checked' : NULL,
		);
		$options	= $this->renderFilterOptions( $optionName, $optionsMap, $optionSelection, $optionClass, $optionData, $optionDataKey, 0 );
		if( count( $options ) > 1 ){
			$switch		= UI_HTML_Tag::create( 'input', NULL, $attributes );
			$attributes	= array(
//				'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $isOpen ? 'block' : 'none' ),
				'class'		=> 'dropdown-menu',// optional '.$name.' '.$name.'-true',
//				'role'		=> 'menu',
//				'arialabelledby'	=> 'dropdownmenu',
			);
			$options	= UI_HTML_Tag::create( 'ul', $options, $attributes );
			return array( $switch, $options );
		}
		return array( '', '' );
	}
}
?>

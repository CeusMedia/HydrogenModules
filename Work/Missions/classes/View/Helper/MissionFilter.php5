<?php
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
			$count	= 0;
			if( $data && $dataKey ){
				foreach( $data as $item )
					$count += $item->$dataKey == $optionKey ? 1 : 0;
			}
			$isChecked	= in_array( $optionKey, $selection );
			$classes	= array( $class );
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
			$label	= UI_HTML_Tag::create( 'label', $input.'&nbsp;'.$label, array( 'for' => $id ) );
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
				'style'		=> 'margin: 0px; padding: 0px; list-style: none; display: '.( $isOpen ? 'block' : 'none' ),
				'class'		=> 'optional '.$name.' '.$name.'-true'
			);
			$options	= UI_HTML_Tag::create( 'ul', $options, $attributes );
			return array( $switch, $options );
		}
		return array( '', '' );
	}
}
?>

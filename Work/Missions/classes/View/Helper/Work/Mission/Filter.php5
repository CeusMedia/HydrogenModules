<?php
class View_Helper_Work_Mission_Filter{

	protected $defaultFilterValues	= array();
	protected $words;

	public function __construct( $env, $defaultFilterValues, $words ){
		$this->env	= $env;
		$this->setDefaultFilterValues( $defaultFilterValues );
		$this->setWords( $words );
	}

	/*  -- mission types  --  */
	public function renderTypeFilter( $filteredTypes ){
		$types			= $this->defaultFilterValues['types'];
		$changedTypes	= array_diff( $types, $filteredTypes );
		$typeIcons	= array(
			0	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-wrench" ) ),
			1	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "icon-time" ) ),
		);
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) ){
			$typeIcons	= array(
				0	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "fa fa-fw fa-thumb-tack" ) ),
				1	=> UI_HTML_Tag::create( 'i', "", array( 'class' => "fa fa-fw fa-clock-o" ) ),
			);
		}

		$list	= array();
		foreach( $types as $type ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'types[]',
				'id'		=> 'type-'.$type,
				'value'		=> $type,
				'checked'	=> in_array( $type, $filteredTypes ) ? "checked" : NULL
			) );
			$label	= $input.'&nbsp;'.$typeIcons[$type].'&nbsp;'.$this->words['types'][$type];
			$label	= UI_HTML_Tag::create( 'label', $label, array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-type type-'.$type ) );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-filter' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['type'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedTypes ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'types' ) );
	}

	/*  -- mission priorities  --  */
	public function renderPriorityFilter( $filteredPriorities ){
		$priorities			= $this->defaultFilterValues['priorities'];
		$changedPriorities	= array_diff( $priorities, $filteredPriorities );
		$list	= array();
		foreach( $priorities as $priority ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'priorities[]',
				'id'		=> 'priority-'.$priority,
				'value'		=> $priority,
				'checked'	=> in_array( $priority, $filteredPriorities ) ? "checked" : NULL
			) );
			$label	= UI_HTML_Tag::create( 'label', $input.' './*$priority.' - '.*/$this->words['priorities'][$priority], array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-priority priority-'.$priority ) );
		}
		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['priority'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedPriorities ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'priorities' ) );
	}

	/*  -- mission states  --  */
	public function renderStateFilter( $filteredStates ){
		$states			= $this->defaultFilterValues['states'];
		$changedStates	= array_diff( $states, $filteredStates );
		$list	= array();
		foreach( $states as $status ){
			$input	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'states[]',
				'id'		=> 'status-'.$status,
				'value'		=> $status,
				'checked'	=> in_array( $status, $filteredStates ) ? "checked" : NULL
			) );
			$label	= UI_HTML_Tag::create( 'label', $input.' '.$this->words['states'][$status], array( 'class' => 'checkbox' ) );
			$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'filter-status status-'.$status ) );
		}
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-spinner' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['status'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonClass	= 'dropdown-toggle btn '.( $changedStates ? "btn-info" : "" );
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, array( 'class'	=> $buttonClass, 'data-toggle' => 'dropdown' ) ),
			UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group', 'id' => 'states' ) );
	}

	/*  -- mission projects  --  */
	public function renderProjectFilter( $filteredProjects, $userProjects ){
		$changedProjects	= array();
		if( !empty( $userProjects ) ){
			$changedProjects	= array_diff( array_keys( $userProjects ), $filteredProjects );
			$list	= array();
			foreach( $userProjects as $project ){
				$input	= UI_HTML_Tag::create( 'input', NULL, array(
					'type'		=> 'checkbox',
					'name'		=> 'projects[]',
					'id'		=> 'project-'.$project->projectId,
					'value'		=> $project->projectId,
					'checked'	=> in_array( $project->projectId, $filteredProjects ) ? "checked" : NULL
				) );
				$label	= UI_HTML_Tag::create( 'label', $input.' '.$project->title, array( 'class' => 'checkbox' ) );
				$list[]	= UI_HTML_Tag::create( 'li', $label, array( 'class' => 'project status'.$project->status ) );
			}
			$listClass		= 'dropdown-menu condensed-width';
			if( count( $userProjects ) > 9 )
				$listClass	.= ' condensed-height';
			$listAttr		= array( 'class' => $listClass );
			$buttonIcon			= '';
			if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
				$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-archive' ) ).'&nbsp;';
			$labelFilter	= $this->words['filters']['project'];
			$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
			$buttonClass	= 'dropdown-toggle btn '.( $changedProjects ? "btn-info" : "" );
			$buttonAttr		= array( 'class' => $buttonClass, 'data-toggle' => 'dropdown' );
			return UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', $buttonIcon.$buttonLabel, $buttonAttr ),
				UI_HTML_Tag::create( 'ul', $list, $listAttr ),
			), array( 'class' => 'btn-group', 'id' => 'projects' ) );
		}
	}

	/*  -- query search  --  */
	public function renderSearch( $filteredQuery ){
		$inputSearch	= UI_HTML_Tag::create( 'input', NULL, array(
			'type'			=> "text",
			'name'			=> "query",
			'id'			=> "filter_query",
			'class'			=> 'span2 '.( $filteredQuery ? 'changed' : '' ),
			'value'			=> htmlentities( $filteredQuery, ENT_QUOTES, 'UTF-8' ),
			'placeholder'	=> $this->words['index']['labelQuery'],
		) );

		$label				= '<i class="icon-search '.( $filteredQuery ? 'icon-white' : '' ).'"></i>';
		$buttonSearch	= UI_HTML_Tag::create( 'button', $label, array(
			'type'		=> "button",
			'class'		=> 'btn '.( $filteredQuery ? 'btn-info' : '' ),
			'id'		=> 'button_filter_search'
		) );
		return $inputSearch.$buttonSearch;
	}

	public function renderReset(){
		$label				= '<i class="icon-remove-circle"></i>';
		$buttonSearchReset	= UI_HTML_Tag::create( 'button', $label, array(
			'type'				=> "button",
			'disabled'			=> "disabled",/*$changedFilters ? NULL : "disabled",*/
			'class'				=> 'btn',/*'btn '.( $changedFilters ? 'btn-inverse' : "" ),*/
			'id'				=> 'button_filter_reset',					//  remove query only: 'button_filter_search_reset',
			'title'				=> 'alle Filter zurücksetzen',
		) );
		return $buttonSearchReset;
	}

	public function renderViewTypeSwitch( $mode ){
		$caret	= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$items	= array();

		$wordsViewTypes	= (object) $this->words['viewTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['viewTypes'] as $typeKey => $typeLabel ){
			$iconClass		= NULL;
			$currentModes	= array( $typeKey );
			switch( $typeKey ){
				case 'calendar':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-calendar' : 'icon-calendar';
					break;
				case 'kanban':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-thumb-tack' : 'icon-note';
					break;
				case 'gantt':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-signal fa-rotate-90' : 'not-icon-any';
					break;
				case 'now':
				case 'archive':
				case 'future':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-list' : 'icon-wrench';
					$currentModes	= array( 'now', 'archive', 'future' );
					break;
			}
			$icon		= $iconClass ? UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) ).'&nbsp;' : '';
			$link		= UI_HTML_Tag::create( 'a', $icon.$typeLabel, array( 'href' => './work/mission/'.$typeKey ) );
			$class		= in_array( $mode, $currentModes ) ? 'active' : NULL;
			$current	= in_array( $mode, $currentModes ) ? $typeLabel : $current;
			$items[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $class ) );
		}

		$labelFilter	= $this->words['filters']['viewType'];
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
	//		UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group' ) );
	}

	public function renderViewModeSwitch( $mode ){
		if( !in_array( $mode, array( 'archive', 'now', 'future' ) ) )
			return "";
		$caret	= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$items	= array();

		$wordsViewTypes	= (object) $this->words['modeTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['modeTypes'] as $modeKey => $modeLabel ){
			$iconClass	= NULL;
			switch( $modeKey ){
				case 'archive':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-arrow-left' : 'icon-arrow-left';
					$url		= "...";
					$id			= "work-mission-view-mode-archive";
					break;
				case 'now':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-star' : 'icon-star';
					$url		= "...";
					$id			= "work-mission-view-mode-now";
					break;
				case 'future':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-arrow-right' : 'icon-arrow-right';
					$url		= "...";
					$id			= "work-mission-view-mode-future";
					break;
			}
			$icon		= $iconClass ? UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) ).'&nbsp;' : '';
			$link		= UI_HTML_Tag::create( 'a', $icon.$modeLabel, array( 'href' => './work/mission/'.$modeKey ) );
			$items[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $mode === $modeKey ? 'active' : NULL ) );
			$current	= $mode === $modeKey ? $modeLabel : $current;
		}
		$labelFilter	= $this->words['filters']['modeType'];
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<span class="not-muted">'.$labelFilter.':</span> <b>'.$current.'</b>', array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
//			UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group' ) );
	}

	public function setDefaultFilterValues( $defaultFilterValues ){
		$this->defaultFilterValues	= $defaultFilterValues;
	}

	public function setWords( $words ){
		$this->words	= $words;
	}
}
?>

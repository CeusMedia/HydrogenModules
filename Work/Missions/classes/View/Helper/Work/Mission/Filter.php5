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
		$buttonLabel	= 'Aufgabentypen <span class="caret"></span>';
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
		$buttonIcon			= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-exclamation' ) ).'&nbsp;';
		$buttonLabel		= 'Prioritäten <span class="caret"></span>';
		$buttonClass		= 'dropdown-toggle btn '.( $changedPriorities ? "btn-info" : "" );
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
		$buttonLabel	= 'Zustände <span class="caret"></span>';
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
			$buttonLabel	= 'Projekte <span class="caret"></span>';
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

	public function renderViewTypeSwitch(){
		$caret	= UI_HTML_Tag::create( 'span', '', array( 'class' => 'caret' ) );
		$items	= array();
		$mode	= $this->env->getSession()->get( 'filter.work.mission.mode' );

		$wordsViewTypes	= (object) $this->words['viewTypes'];

		$current	= '';
		$hasFontAwesome	= $this->env->getModules()->has( 'UI_Font_FontAwesome' );
		foreach( $this->words['viewTypes'] as $typeKey => $typeLabel ){
			$iconClass	= NULL;
			switch( $typeKey ){
				case 'now':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-list' : 'icon-wrench';
					break;
				case 'calendar':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-calendar' : 'icon-calendar';
					break;
				case 'kanban':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-thumb-tack' : 'icon-note';
					break;
				case 'gantt':
					$iconClass	= $hasFontAwesome ? 'fa fa-fw fa-signal fa-rotate-90' : 'not-icon-any';
					break;
			}
			$icon		= $iconClass ? UI_HTML_Tag::create( 'i', '', array( 'class' => $iconClass ) ).'&nbsp;' : '';
			$link		= UI_HTML_Tag::create( 'a', $icon.$typeLabel, array( 'href' => './work/mission/'.$typeKey ) );
			$items[]	= UI_HTML_Tag::create( 'li', $link, array( 'class' => $mode === $typeKey ? 'active' : NULL ) );
			$current	= $mode === $typeKey ? $typeLabel : $current;
		}

/*		$label	= $caret.' Ansicht: <b>'.$current.'</b>';
		$list	= UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) );
		return UI_HTML_Tag::create( 'button', $label, array( 'class' => 'btn', 'data-toggle' => "dropdown" ) ).$list;
*/
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<span class="not-muted">Ansicht:</span> <b>'.$current.'</b>', array( 'class' => 'btn', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'button', $caret, array( 'class' => 'btn dropdown-toggle', 'data-toggle' => "dropdown" ) ),
			UI_HTML_Tag::create( 'ul', $items, array( 'class' => 'dropdown-menu' ) ),
		), array( 'class' => 'btn-group' ) );

		$badgeIcon	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-refresh icon-white' ) );
		$badge      = UI_HTML_Tag::create( 'span', $badgeIcon, array(
			'id'	=> "number-total",
			'class'	=> "badge badge-success",
		) );
		return UI_HTML_Tag::create( 'div', array(
/*			UI_HTML_Tag::create( 'button', $badge, array(
				'type'		=> "button",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) ),*/
			UI_HTML_Tag::create( 'button', '<i class="icon-calendar"></i> Monat', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-type-0",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) ),
			UI_HTML_Tag::create( 'button', '<i class="icon-tasks"></i> Liste', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-type-1",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) ),
			UI_HTML_Tag::create( 'button', '<i class="icon-clock"></i> Kanban', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-type-2",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) )
		), array( 'class' => 'btn-group' ) );
	}

	public function renderViewModeSwitch( $mode ){
		if( !in_array( $mode, array( 'archive', 'now', 'future' ) ) )
			return "";
		return UI_HTML_Tag::create( 'div', array(
			UI_HTML_Tag::create( 'button', '<i class="icon-arrow-left"></i> Archiv', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-mode-archive",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) ),
			UI_HTML_Tag::create( 'button', '<i class="icon-star"></i> Aktuell', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-mode-now",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) ),
			UI_HTML_Tag::create( 'button', '<i class="icon-arrow-right"></i> Zukunft', array(
				'type'		=> "button",
				'id'		=> "work-mission-view-mode-future",
				'disabled'	=> "disabled",
				'class'		=> "btn -btn-small",
			) )
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

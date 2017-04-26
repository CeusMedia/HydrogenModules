<?php
class View_Helper_Work_Mission_Filter_Worker{

	public function __construct( $env ){
		$this->env	= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
	}

	public function setModalRegistry( $modalRegistry ){
		$this->modalRegistry	= $modalRegistry;
	}

	public function setValues( $all, $selected ){
		$this->values	= $all;
		$this->selected	= $selected;
	}

	public function render(){
		if( empty( $this->values ) )
			return;
		$helperMember		= new View_Helper_Member( $this->env );
//		$helperMember->setMode( 'bar' );
		$iconEye			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
		$iconWarning		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-warning' ) );
		$iconCheck			= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-square-o' ) );
		$changedWorkers	= array_diff( array_keys( $this->values ), $this->selected );
		$list	= array();
		foreach( $this->values as $worker ){
			$checkbox	= UI_HTML_Tag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'workers[]',
				'id'		=> 'worker-'.$worker->userId,
				'value'		=> $worker->userId,
				'checked'	=> in_array( $worker->userId, $this->selected ) ? "checked" : NULL
			) );
//			$label		= UI_HTML_Tag::create( 'label', $checkbox.'&nbsp;'.$worker->username, array( 'class' => 'checkbox' ) );

			$helperMember->setUser( $worker );
			$label		= $helperMember->render();
			$label		= UI_HTML_Tag::create( 'label', $checkbox.'&nbsp;'.$label, array(
				'class'	=> 'checkbox',
				'for'	=> 'worker-'.$worker->userId,
				'style'	=> 'margin-left: 6px;',
			) );

			$buttons	= UI_HTML_Tag::create( 'div', array(
				UI_HTML_Tag::create( 'button', 'nur dieser', array(
					'class'		=> 'btn btn-mini trigger-select-this',
//						'disabled'	=> 'disabled',
					'data-id'	=> $worker->userId,
				) ),
				UI_HTML_Tag::create( 'button', $iconEye, array(
					'class'	=> 'btn btn-small btn-info',
					'href'	=> './member/view/'.$worker->userId,
				) ),
			), array( 'class' => 'pull-right' ) );

			$list[]	= UI_HTML_Tag::create( 'tr', array(
				UI_HTML_Tag::create( 'td', $label, array(
					'style'		=> 'padding: 1px;',
				) ),
				UI_HTML_Tag::create( 'td', $buttons, array(
					'style'		=> 'padding: 1px;',
				) ),
			) );
		}

		$buttonAll	= UI_HTML_Tag::create( 'a', $iconCheck, array(
		//	'id'	=> 'modal-work-mission-filter-workers-all',
			'class'	=> 'trigger-select-all',
			'href'	=> '#',
		) );
		$colgroup	= UI_HTML_Elements::ColumnGroup( "", "140px" );
		$tableHeads	= UI_HTML_Elements::TableHeads( array( $buttonAll."&nbsp;&nbsp;Bearbeiter", "" ) );
		$thead		= UI_HTML_Tag::create( 'thead', $tableHeads );
		$tbody		= UI_HTML_Tag::create( 'tbody', $list );
		$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array(
			'id'	=> 'filter-work-missions-workers-list',
			'class'	=> 'table table-condensed table-fixed'
		) );

		$modal			= new View_Helper_Modal( $this->env );
		$modal->setId( 'modal-work-mission-filter-workers' );
		$modal->setHeading( 'Filter: Bearbeiter' );
		$modal->setBody( $table );
		$modal->setFade( FALSE );
		$this->modalRegistry->register( 'workMissionFilterWorkers', $modal );

		$buttonIcon		= '';
		if( $this->env->getModules()->has( 'UI_Font_FontAwesome' ) )
			$buttonIcon		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) ).'&nbsp;';
		$labelFilter	= $this->words['filters']['worker'];
		$buttonLabel	= $labelFilter.'&nbsp;<span class="caret"></span>';
		$buttonAttr		= array(
			'class'	=> 'btn '.( count( $changedWorkers ) ? "btn-info" : "" ),
		);
		$modalTrigger	= new View_Helper_ModalTrigger( $this->env );
		$modalTrigger->setId( 'modal-work-mission-filter-workers-trigger' );
		$modalTrigger->setModalId( 'modal-work-mission-filter-workers' );
		$modalTrigger->setLabel( $buttonIcon.$buttonLabel );
		$modalTrigger->setAttributes( $buttonAttr );
		return $modalTrigger->render();

	}
}

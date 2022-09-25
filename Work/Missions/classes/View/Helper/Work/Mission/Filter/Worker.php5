<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

class View_Helper_Work_Mission_Filter_Worker{

	public function __construct( $env ){
		$this->env		= $env;
		$this->words	= $this->env->getLanguage()->getWords( 'work/mission' );
		$this->userId	= $this->env->getSession()->get( 'auth_user_id' );
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
		$iconEye			= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-eye' ) );
		$iconWarning		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-warning' ) );
		$iconCheck			= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check-square-o' ) );
		$changedWorkers	= array_diff( array_keys( $this->values ), $this->selected );
		$list	= [];
		foreach( $this->values as $worker ){
			$checkbox	= HtmlTag::create( 'input', NULL, array(
				'type'		=> 'checkbox',
				'name'		=> 'workers[]',
				'id'		=> 'worker-'.$worker->userId,
				'value'		=> $worker->userId,
				'checked'	=> in_array( $worker->userId, $this->selected ) ? "checked" : NULL
			) );
//			$label		= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$worker->username, array( 'class' => 'checkbox' ) );

			$helperMember->setUser( $worker );
			$label		= $helperMember->render();
			$label		= HtmlTag::create( 'label', $checkbox.'&nbsp;'.$label, array(
				'class'	=> 'checkbox',
				'for'	=> 'worker-'.$worker->userId,
				'style'	=> 'margin-left: 6px;',
			) );

			$buttons	= HtmlTag::create( 'div', array(
				HtmlTag::create( 'button', 'nur dieser', array(
					'class'		=> 'btn btn-mini trigger-select-this',
//						'disabled'	=> 'disabled',
					'data-id'	=> $worker->userId,
				) ),
				HtmlTag::create( 'button', $iconEye, array(
					'class'	=> 'btn btn-small btn-info',
					'href'	=> './member/view/'.$worker->userId,
				) ),
			), array( 'class' => 'pull-right' ) );

			$list[]	= HtmlTag::create( 'tr', array(
				HtmlTag::create( 'td', $label, array(
					'style'		=> 'padding: 1px;',
				) ),
				HtmlTag::create( 'td', $buttons, array(
					'style'		=> 'padding: 1px;',
				) ),
			) );
		}

		$buttonAll	= HtmlTag::create( 'a', $iconCheck, array(
		//	'id'	=> 'modal-work-mission-filter-workers-all',
			'class'	=> 'trigger-select-all',
			'href'	=> '#',
		) );

		$buttonUser	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-user"></i>&nbsp;<b>nur ich</b>', array(
			'class'		=> 'btn btn-small trigger-select-this',
			'data-id'	=> $this->userId,
		) );
		$buttonAll	= HtmlTag::create( 'button', '<i class="fa fa-fw fa-users"></i>&nbsp;<b>alle</b>', array(
			'class'		=> 'btn btn-small trigger-select-all',
			'href'		=> '#',
		) );
		$buttons	= HtmlTag::create( 'div', array( $buttonUser, $buttonAll ), array( 'class' => 'btn-group' ) );
		$colgroup	= HtmlElements::ColumnGroup( "", "160px" );
		$tableHeads	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'th', "Bearbeiter" ),
			HtmlTag::create( 'th', $buttons, array( 'style' => 'text-align: right') )
		) );
		$thead		= HtmlTag::create( 'thead', $tableHeads );
		$tbody		= HtmlTag::create( 'tbody', $list );
		$table		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array(
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
			$buttonIcon		= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-user' ) ).'&nbsp;';
		$labelFilter	= HtmlTag::create( 'span', $this->words['filters']['worker'], array( 'class' => 'hidden-phone' ) );
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

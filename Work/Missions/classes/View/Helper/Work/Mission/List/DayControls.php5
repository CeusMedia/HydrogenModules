<?php
class View_Helper_Work_Mission_List_DayControls extends View_Helper_Work_Mission_List{

	protected $dayMissions	= array();

	public function __construct( $env ){
		parent::__construct( $env );
	}

	public function render(){
		$buttons	= array();
		$numbers	= array();
		for( $i=0; $i<6; $i++ )
			$numbers[$i]	= count( $this->dayMissions[$i] );										//  @todo	kriss: exception management
		$max		= max( $numbers );
		for( $i=0; $i<6; $i++ ){
			$label		= $this->renderDayButtonLabel( $i, $numbers[$i], $max );
			$attributes	= array(
				'href'		=> '#',
				'onclick'	=> 'WorkMissions.showDayTable('.$i.', true); return false',
			);
			$link		= UI_HTML_Tag::create( 'a', $label, $attributes );
			$buttons[]	= UI_HTML_Tag::create( 'li', $link );
		}
		$list		= UI_HTML_Tag::create( 'ul', $buttons, array( 'class' => 'nav' ) );
		$container	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'container' ) );
		$inner		= UI_HTML_Tag::create( 'div', $container, array( 'class' => 'navbar-inner' ) );
		return UI_HTML_Tag::create( 'div', $inner, array( 'class' => 'navbar' ) );
	}

	public function renderDayButtonLabel( $day, $number, $max, $template = '%1$s%2$s%3$s%4$s' ){
		$then		= time() - $this->logic->timeOffset + ( $day * 24 * 60 * 60 );
		$indicator	= $this->renderDayLoadIndicator( $number, $max, !TRUE );
		$number		= ' <div class="mission-number"><span class="badge">'.$number.'</span></div>';

		$dayDate	= date( "j.", $then );
		$dayDate	.= '<small class="muted">'.date( "n.", $then ).'</small>';
		$dayDate	= UI_HTML_Tag::create( 'div', $dayDate, array( 'class' => 'dayDate date' ) );
		$dayLabel	= $this->words['days'][date( "w", $then )];
		$dayLabel	= UI_HTML_Tag::create( 'div', $dayLabel, array( 'class' => 'dayName' ) );
		return sprintf( $template, $dayDate, $dayLabel, $number, $indicator );
	}

	protected function renderDayLoadIndicator( $number, $max, $useInfo = FALSE ){
		$max		= $max < 18 ? 18 : $max;														//  max is atleast 18
		$ratio		= $number / $max;
		if( $useInfo ){
			$width		= "100%";																	//  
			$color		= "";
			if( $ratio > 0.75 )
				$color		= "bar-danger";
			else if( $ratio > 0.5 )
				$color		= "bar-warning";
			else if( $ratio > 0.25 )
				$color		= "bar-success";
			else if(  $ratio > 0 )
				$color		= "bar-info";
		}
		else{
			$width		= $ratio ? "100%" : "0";													//  
			$color		= "";
			if( $ratio > 0.66 )
				$color		= "bar-danger";
			else if( $ratio > 0.33 )
				$color		= "bar-warning";
			else if( $ratio > 0 )
				$color		= "bar-success";
		}
		$attributes	= array(
			'class'		=> 'bar '.$color,
			'style'		=> 'width: '.$width,
		);
		$bar	= UI_HTML_Tag::create( 'div', "", $attributes );
		return UI_HTML_Tag::create( 'div', $bar, array( 'class' => 'progress', 'data-max' => $max ) );
	}

	public function setDayMissions( $dayMissions ){
		$this->dayMissions	= $dayMissions;
	}
}
?>

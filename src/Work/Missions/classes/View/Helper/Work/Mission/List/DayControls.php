<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_List_DayControls extends View_Helper_Work_Mission_List
{
	protected array $dayMissions	= [];

	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env );
	}

	public function render(): string
	{
		$buttons	= [];
		$numbers	= [];
		for( $i=0; $i<6; $i++ )
			$numbers[$i]	= count( $this->dayMissions[$i] );										//  @todo	exception management
		$max		= max( $numbers );
		for( $i=0; $i<6; $i++ ){
			$label		= $this->renderDayButtonLabel( $i, $numbers[$i], $max );
			$class		= $numbers[$i] ? '' : 'empty-day';
			$attributes	= [
				'href'				=> '#',
				'onclick'			=> 'WorkMissions.showDayTable('.$i.', true, true); return false',
				'class'				=> 'btn mission-day-control '.$class,
				'id'				=> 'work-mission-day-control-'.$i,
				'data-day-nr'		=> $i,
				'data-items'		=> $numbers[$i],
				'data-items-max'	=> $max,
			];
			$buttons[]	= HtmlTag::create( 'a', $label, $attributes );
		}
		return HtmlTag::create( 'div', $buttons, array(
			'id'	=> 'day-controls-btn-group',
			'class' => 'btn-group',
		) );
	}

	public function renderDayButtonLabel( $day, $number, $max, $template = '%1$s%2$s%3$s%4$s' ): string
	{
		$then		= time() - $this->logic->timeOffset + ( $day * 24 * 60 * 60 );
		$indicator	= $this->renderDayLoadIndicator( $number, $max, !TRUE );
		$number		= ' <div class="mission-number"><span class="badge">'.$number.'</span></div>';

		$dayDate	= date( "j.", $then );
		$dayDate	.= '<small class="muted">'.date( "n.", $then ).'</small>';
		$dayDate	= HtmlTag::create( 'div', $dayDate, ['class' => 'dayDate date'] );
		$dayLabel	= $this->words['days'][date( "w", $then )];
		$dayLabel	= HtmlTag::create( 'div', $dayLabel, ['class' => 'dayName'] );
		return sprintf( $template, $dayDate, $dayLabel, $number, $indicator );
	}

	protected function renderDayLoadIndicator( $number, $max, bool $useInfo = FALSE ): string
	{
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
		$attributes	= [
			'class'		=> 'bar '.$color,
			'style'		=> 'width: '.$width,
		];
		$bar	= HtmlTag::create( 'div', "", $attributes );
		return HtmlTag::create( 'div', $bar, ['class' => 'progress', 'data-max' => $max] );
	}

	public function setDayMissions( $dayMissions ): self
	{
		$this->dayMissions	= $dayMissions;
		return $this;
	}
}


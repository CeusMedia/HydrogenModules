<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web as WebEnvironment;

class View_Helper_Work_Mission_List_DayControlsSmall extends View_Helper_Work_Mission_List
{
	protected array $dayMissions	= [];

	/**
	 *	@param		WebEnvironment		$env
	 *	@throws		Exception
	 */
	public function __construct( WebEnvironment $env )
	{
		parent::__construct( $env );
	}

	/**
	 *	@return		string
	 */
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
				'data-toggle'		=> 'tab',
				'onclick'			=> 'WorkMissions.showDayTable('.$i.', true); return false',
				'id'				=> 'work-mission-day-control-small-'.$i,
				'class'				=> 'btn mission-day-control '.$class,
				'data-day-nr'		=> $i,
				'data-items'		=> $numbers[$i],
				'data-items-max'	=> $max,
			];
			$link		= HtmlTag::create( 'a', $label, $attributes );
			$buttons[]	= HtmlTag::create( 'li', $link );
		}
		$list		= HtmlTag::create( 'ul', $buttons, ['class' => 'nav nav-tabs'] );

		return HtmlTag::create( 'div', $list, ['class' => 'container'] );
	}

	/**
	 *	@param		int			$day
	 *	@param		int			$number
	 *	@param		int			$max
	 *	@return		string
	 */
	public function renderDayButtonLabel( int $day, int $number, int $max/*, $template = '%1$s%2$s%3$s%4$s'*/ ): string
	{
		$then		= time() - $this->logic->timeOffset + ( $day * 24 * 60 * 60 );
		$dayName	= $this->words['days-short'][date( "w", $then )];
//		$dayCount	= '<small class="muted">'.$number.'</small>';
		$dayDate	= date( "j.", $then );
		$dayDate	.= '<small class="muted">'.date( "n.", $then ).'</small>';
		$indicator	= $this->renderDayLoadIndicator( $number, $max, !TRUE );
		return '<b>'.$dayName.'</b><br/><small>'.$dayDate.'</small><br/>'.$indicator;

/*		$number		= ' <div class="mission-number"><span class="badge">'.$number.'</span></div>';
		$dayDate	= date( "j.", $then );
		$dayDate	.= '<small class="muted">'.date( "n.", $then ).'</small>';
		$dayDate	= HtmlTag::create( 'div', $dayDate, ['class' => 'dayDate date'] );
		$dayLabel	= $this->words['days'][date( "w", $then )];
		$dayLabel	= HtmlTag::create( 'div', $dayLabel, ['class' => 'dayName'] );
		return sprintf( $template, $dayDate, $dayLabel, $number, $indicator );*/
	}

	/**
	 *	@param		int			$number
	 *	@param		int			$max
	 *	@param		bool		$useInfo
	 *	@return		string
	 */
	protected function renderDayLoadIndicator( int $number, int $max, bool $useInfo = FALSE ): string
	{
		$max		= max( $max, 18 );														//  max is atleast 18
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

	/**
	 *	@param		array<object>		$dayMissions
	 *	@return		self
	 */
	public function setDayMissions( array $dayMissions ): self
	{
		$this->dayMissions	= $dayMissions;
		return $this;
	}
}

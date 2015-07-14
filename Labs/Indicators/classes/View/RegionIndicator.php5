<?php
/**
 *	@category		MV2.Tools
 *	@package		SeleniumTester.View
 *	@author			Christian Würker <christian.wuerker@ceumedia.de>
 *	@version        $Id: RegionIndicator.php5 12591 2012-03-23 11:58:45Z christian.wuerker $
 */
/**
 *	@category		MV2.Tools
 *	@package		SeleniumTester.View
 *	@author			Christian Würker <christian.wuerker@ceumedia.de>
 *	@version        $Id: RegionIndicator.php5 12591 2012-03-23 11:58:45Z christian.wuerker $
 */
class View_RegionIndicator{

	protected $regions		= array();
	protected $minBarHeight	= NULL;

	public function __construct( $minBarHeight = NULL ){
		$this->minBarHeight = $minBarHeight;
	}

	public function addRegion( $weight, $color, $content ){
		$this->regions[]	= (object) array(
			'color'		=> $color,
			'content'	=> $content,
			'weight'	=> $weight,
		);
	}

	public function render( $width, $height ){
		$values	= array();
		foreach( $this->regions as $nr => $region )
			$values[]	= $region->weight;

		$min		= -1;
		$ranges		= Math_Extrapolation::calculateRanges( $values, $height );
#		print_m( $ranges );
		if( $this->minBarHeight ){																	//  recalculate ranges if atleast one range is smaller than defined minimum bar height
			foreach( $ranges as $range )
				if( $range->size > 0 && ( $min < 0 || $range->size < $min ) )
					$min	= $range->size;
			if( $min < $this->minBarHeight ){
				$min		= min( $values );
				$max		= max( $values );
				$min		= $min / array_sum( $values ) * $height;
				$height		= ceil( $this->minBarHeight / $min * $height );
				$ranges		= Math_Extrapolation::calculateRanges( $values, $height );
			}
		}
#		print_m( $ranges );
#		die;
		foreach( $ranges as $nr => $range ){
			$a			= array(
				'class'	=> 'region-content',
				'style'	=> 'left: '.$width.'px'
			);
			$pin		= UI_HTML_Tag::create( 'div', '', array( 'class' => 'region-content-pin' ) );
			$content	= $this->regions[$nr]->content.$pin;
			$content	= UI_HTML_Tag::create( 'div', $content, array( 'class' => 'region-content-inner' ) );
			$content	= UI_HTML_Tag::create( 'div', $content, $a );

			$a	= array(
				'class'		=> 'region-bar',
				'style'		=> join( '; ', array(
					'top: '.$range->offset.'px',
					'height: '.$range->size.'px',
					'background-color: '.$this->regions[$nr]->color,
				) )
			);

			$list[]	= UI_HTML_Tag::create( 'div', $content, $a );
		}
		$list	= '<div class="region-container" style="height: '.$height.'px;">'.join( $list ).'</div>';
		return $list;
	}
}
?>
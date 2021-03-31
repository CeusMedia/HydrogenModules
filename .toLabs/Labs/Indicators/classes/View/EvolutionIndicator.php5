<?php
/**
 *	@category		MV2.Tools
 *	@package		SeleniumTester.View
 *	@author			Christian Würker <christian.wuerker@ceumedia.de>
 *	@version        $Id: EvolutionIndicator.php5 12591 2012-03-23 11:58:45Z christian.wuerker $
 */
/**
 *	@category		MV2.Tools
 *	@package		SeleniumTester.View
 *	@author			Christian Würker <christian.wuerker@ceumedia.de>
 *	@version        $Id: EvolutionIndicator.php5 12591 2012-03-23 11:58:45Z christian.wuerker $
 */
class View_EvolutionIndicator{

	static public $options	= array(
		'width'		=> 100,
		'height'	=> 8
	);

	static public function getColor( $ratio ){
		$colorR	= ( 1 - $ratio ) > 0.5 ? 255 : round( ( 1 - $ratio ) * 2 * 255 );
		$colorG	= $ratio > 0.5 ? 255 : round( $ratio * 2 * 255 );
		$colorB	= "0";
		return "rgb(".$colorR.",".$colorG.",".$colorB.")";
	}

	static public function render( $data, $options = array() ){
		$options	= array_merge( self::$options, $options );
		if( !count( $data ) )
			throw new InvalidArgumentException( 'No data given' );
		$bars	= array();
		$regions	= array_fill( 0, count( $data ), 1 );
		$ranges	= Math_Extrapolation::calculateRanges( $regions, $options['width'] );

		foreach( $data as $nr => $ratio ){
			$color	= self::getColor( $ratio );
			$width	= $ranges[$nr]->size;
			$attributes	= array(
				'class'	=> 'bar',
				'style'	=> join( '; ', array(
					'float: left',
					'width: '.$width.'px',
					'height: 100%',
					'background-color: '.$color
				) )
			);
			$bars[]	= UI_HTML_Tag::create( 'div', '', $attributes );
		}
		$attributes	= array(
			'class'		=> 'indicator-evolution container',
			'style'		=> join( '; ', array(
				'float: left',
				'width: '.$options['width'].'px',
				'height: '.$options['height'].'px'
			) )
		);
		return UI_HTML_Tag::create( 'div', join( $bars ), $attributes );
	}
}
?>
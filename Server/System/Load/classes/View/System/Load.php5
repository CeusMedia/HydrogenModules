<?php
/**
 *	View for system CPU load indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 */
/**
 *	View for system CPU load indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	Ceus Media 2015
 *	@extends	CMF_Hydrogen_View
 */
class View_System_Load extends CMF_Hydrogen_View{

	/**
	 *	Prints HTML indicator for system load and quits execution.
	 *	@access		public
	 *	@return		void
	 */
	public function ajaxRenderDashboardPanel(){
		$panelId	= $this->getData( 'panelId' );
		switch( $panelId ){
			case 'system-server-load-current':
			default:
				$loads		= $this->getData( 'loads' );													//  get load values registered by controller
				$percentages	= array(
					round( $loads[0] * 100, 1 ),
					round( $loads[1] * 100, 1 ),
					round( $loads[2] * 100, 1 ),
				);
				$barValue	= max( 0, min( 100, $loads[0] / 1 * 100 ) );
				$barStyle	= '';
				$barStyles	= array(
					20 => 'info',
					40 => 'success',
					60 => 'warning',
					80 => 'danger',
				);
				foreach( $barStyles as $edge => $style )
					if( $barValue > $edge )
						$barStyle	= $style;
				$trend5m	= $this->renderTrend( $percentages[0] - $percentages[1], '%', 2, TRUE );
				$trend15m	= $this->renderTrend( $percentages[0] - $percentages[2], '%', 2, TRUE );
				$contentNumbers	= '
					<br/>
					<div class="row-fluid">
						<div class="span6" style="text-align: center; padding-top: 0.4em; ">
							<big style="font-size: 2em;">'.$percentages[0].'%</big><br/>
							<small>last minute</small><br/>
							<br/>
						</div>
						<div class="span6 hidden-phone">
							'.$percentages[1].'% <small class="muted hidden-tablet">last 5 minutes</small><br/>
							'.$percentages[2].'% <small class="muted hidden-tablet">last 15 minutes</small><br/>
						</div>
					</div>';

				$contentGraph	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'div', '', array(
						'class'	=> 'bar',
						'style'	=> 'width: '.round( $barValue, 2 ).'%;',
					) ), array( 'class' => 'progress progress-'.$barStyle ) );

				$contentTrends	= '
				<table class="table table-condensed table-fixed" style="margin: 0;" data-style="border: 1px solid rgba(127, 127, 127, 0.25)">
						<colgroup>
							<col width=""/>
							<col width="60px"/>
							<col width="60px"/>
						</colgroup>
						<thead>
							<tr>
								<th>Periode</th>
								<th style="text-align: right">Wert</th>
								<th style="text-align: right">Trend</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><span class="autocut">5 minutes</td>
								<td style="text-align: right">'.$percentages[1].'%</td>
								<td style="text-align: right">'.$trend5m.'</td>
							</tr>
							<tr>
								<td><span class="autocut">15 minutes</span></td>
								<td style="text-align: right">'.$percentages[2].'%</td>
								<td style="text-align: right">'.$trend15m.'</td>
							</tr>
						</tbody>
					</table>';

				$content	= '
					'.$contentNumbers.'
					<div style="padding: 0.5em">
						'.$contentGraph.'
					</div>
					'.$contentTrends;
		}
		print( $content );																			//  render and print indicator
		exit;																						//  and quit application
	}

	/**
	 *	Prints HTML indicator for system load and quits execution.
	 *	@access		public
	 *	@return		void
	 */
	public function ajaxRenderIndicator(){
		$load		= $this->getData( 'load' );														//  get load registered by controller
		$cpuCores	= $this->getData( 'cpuCores' );													//  get number of cpu cores from module config
		$load		= 1 / ( 1 + $load / $cores );													//  calculate load relative to number of cores
		$indicator	= new UI_HTML_Indicator();														//  create instance of indicator renderer
		print( $indicator->build( $load, 1 ) );														//  render and print indicator
		exit;																						//  and quit application
	}

	protected function renderTrend( $number, $unit, $accuracy = 0, $inverse = FALSE ){
//		$prefix		= '&plus;';
//		$style		= 'success';
		$value		= round( abs( $number ), $accuracy );
		$prefix		= $number < 0 ? '&minus;' : '&plus;';
		$factor		= $inverse ? -1 : 1;
		$style		= ( $number * $factor < 0 ) ? 'error' : 'success';
		return UI_HTML_Tag::create( 'span', $prefix.$value.$unit, array( 'class' => 'text text-'.$style ) );
	}
}
?>

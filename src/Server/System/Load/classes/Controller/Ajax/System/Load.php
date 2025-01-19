<?php
/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2014-2024 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;
use CeusMedia\Common\UI\HTML\Indicator as HtmlIndicator;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/**
 *	Controller for system CPU load handling and indicating.
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright	2014-2024 Ceus Media (https://ceusmedia.de/)
 */
class Controller_Ajax_System_Load extends AjaxController
{
	protected Dictionary $config;
	protected int $cpuCores;
	protected Dictionary $moduleConfig;

	/**
	 *	@param		int			$mode
	 *	@param		bool		$relative
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function getLoad( int $mode = 0, bool $relative = FALSE ): void
	{
		if( !$this->env->getRequest()->isAjax() )												//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );					//  quit with exception
		$load		= $this->__getLoad( $mode, $relative );										//  get system load
		$this->respondData( [
			'load'	=> $load,
			'time'	=> time()
		] );																					//  send loads as JSON response
	}

	/**
	 *	@param		bool		$relative
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function getLoads( bool $relative = FALSE ): void
	{
		if( !$this->env->getRequest()->isAjax() )												//  not an AJAX request
			throw new RuntimeException( 'Accessible using AJAX only' );					//  quit with exception
		$loads	= self::__getLoads( $relative, $this->cpuCores );								//  get system loads
		$this->respondData( [																	//  send loads as JSON response
			'load'	=> $loads,
			'time'	=> time()
		] );
	}

	/**
	 *	@param		string		$panelId
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function renderDashboardPanel( string $panelId ): void
	{
		switch( $panelId ){
			case 'system-server-load-current':
			default:
				$this->respondData( $this->renderDashboardPanelForCurrentSystemServerLoad() );		//  append system load values relative to CPU core
		}
	}

	protected function renderDashboardPanelForCurrentSystemServerLoad(): string
	{
		$loads		= self::__getLoads( TRUE, $this->cpuCores );								//  get load values registered by controller
		$percentages	= [
			round( $loads[0] * 100, 1 ),
			round( $loads[1] * 100, 1 ),
			round( $loads[2] * 100, 1 ),
		];
		$barValue	= max( 0, min( 100, $loads[0] / 1 * 100 ) );
		$barStyle	= '';
		$barStyles	= [
			20 => 'info',
			40 => 'success',
			60 => 'warning',
			80 => 'danger',
		];
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

		$contentGraph	= HtmlTag::create( 'div', HtmlTag::create( 'div', '', [
			'class'	=> 'bar',
			'style'	=> 'width: '.round( $barValue, 2 ).'%;',
		] ), ['class' => 'progress progress-'.$barStyle] );

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

		return '
			'.$contentNumbers.'
			<div style="padding: 0.5em">
				'.$contentGraph.'
			</div>
			'.$contentTrends;
	}

	/**
	 *	Show server load (of last 1, 5 or 15 minutes, absolute or relative to number of CPU cores) as HTML indicator.
	 *	@access		public
	 *	@param		integer		$mode		Get load for last 1, 5 or 15 minutes (mode: 0, 1, 2)
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function renderIndicator( int $mode = 0, bool $relative = FALSE ): void
	{
		$load		= $this->__getLoad( $mode, $relative );											//  get load registered by controller
		$cpuCores	= $this->cpuCores;																//  get number of cpu cores from module config
		$load		= 1 / ( 1 + $load / $cpuCores );												//  calculate load relative to number of cores
		$indicator	= new HtmlIndicator();															//  create instance of indicator renderer
		$this->respondData( $indicator->build( $load, 1 ) );									//  render and print indicator
	}

	protected function __onInit(): void
	{
		$this->config		= $this->env->getConfig();
		$this->moduleConfig	= $this->config->getAll( 'module.server_system_load.', TRUE );	//  shortcut module configuration
		$this->cpuCores		= (int) $this->moduleConfig->get( 'cores' );						//  get number of cpu cores from module config
		$this->addData( 'moduleConfig', $this->moduleConfig );
		$this->addData( 'cpuCores', $this->cpuCores );
	}

	/**
	 *	Returns server load (of last 1, 5 or 15 minutes), absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@access		public
	 *	@param		integer		$mode		Get load for last 1, 5 or 15 minutes (mode: 0, 1, 2)
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@return		float					Server load of last 1, 5 or 15 minutes (depending on mode)
	 */
	protected function __getLoad( int $mode = 0, bool $relative = FALSE ): float
	{
		$mode	= max( 0, min( 2, $mode ) );											//  make sure mode is of integer within {0, 1, 2}
		$loads	= self::__getLoads( $relative, $this->cpuCores );									//  get server loads of last 1, 5 and 15 minutes
		return $loads[$mode];																		//  return one selected load value as float
	}

	/**
	 *	Returns server loads of last 1, 5 and 15 minutes, absolute or relative to number of CPU cores.
	 *	Needs number of CPU cores to be configured to work correctly.
	 *	@static
	 *	@access		public
	 *	@param		boolean		$relative	Calculate load in relation to number of CPU cores
	 *	@param		integer		$cores		Number of CPU cores
	 *	@return		array					Server load of last 1, 5 or 15 minutes as list floats
	 */
	protected static function __getLoads( bool $relative = FALSE, int $cores = 1 ): array
	{
		$loads	= sys_getloadavg();
		$cores	= max( 1, floor( (float) $cores ) );
		if( $relative && $cores > 1 )
			foreach( $loads as $nr => $load )
				$loads[$nr]	= $load / $cores;
		return $loads;
	}

	protected function renderTrend( float $number, string $unit, int $accuracy = 0, bool $inverse = FALSE ): string
	{
//		$prefix		= '&plus;';
//		$style		= 'success';
		$value		= round( abs( $number ), $accuracy );
		$prefix		= $number < 0 ? '&minus;' : '&plus;';
		$factor		= $inverse ? -1 : 1;
		$style		= ( $number * $factor < 0 ) ? 'error' : 'success';
		return HtmlTag::create( 'span', $prefix.$value.$unit, ['class' => 'text text-'.$style] );
	}
}

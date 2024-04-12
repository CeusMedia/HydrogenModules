<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Exception\Page as HtmlExceptionPage;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\View;

class View_Admin_Mail_Queue extends View
{
	public function ajaxRenderDashboardPanel(): string
	{
		$model			= new Model_Mail( $this->env );

		$statuses		= [
			Model_Mail::STATUS_SENT		=> 'versendet',
			Model_Mail::STATUS_FAILED	=> 'fehlgeschlagen',
			Model_Mail::STATUS_ABORTED	=> 'gescheitert',
		];
		$ranges		= [
			1		=> 'Tag',
			7		=> 'Woche',
			30		=> 'Monat',
			365		=> 'Jahr',
		];
		$data	= [];
		foreach( $statuses as $statusKey => $statusLabel ){
			$data[$statusKey]	= [];
			foreach( $ranges as $rangeKey => $rangeLabel ){
				$conditions	= [
					'status'		=> $statusKey,
					'enqueuedAt'	=> '>= '.( time() - $rangeKey * 24 * 3600 ),
				];
				$data[$statusKey][$rangeKey]	= $model->count( $conditions );
			}
		}

		$tableHeads		= [''];
		foreach( $ranges as $rangeLabel )
			$tableHeads[]	= HtmlTag::create( 'small', $rangeLabel, ['class' => 'pull-right'] );

		$lengthKeys			= array_keys( $ranges );
		$lastRangeLength	= @array_pop( $lengthKeys );

		$rows	= [];
		foreach( $statuses as $statusKey => $statusLabel ){
			$row	= [];
			foreach( array_reverse( $ranges, TRUE ) as $days => $label ){
				$lastRange	= (object) [
					'key'		=> $days,
					'value'		=> $data[$statusKey][$days],
					'label'		=> $label,
				];
				break;
			}
			foreach( array_reverse( $ranges, TRUE ) as $rangeKey => $rangeLabel ){
				$label	= $data[$statusKey][$rangeKey];
				if( $rangeKey !== $lastRange->key && $data[$statusKey][$lastRange->key] > 10 ){
					$average	= $lastRange->value ? $data[$statusKey][$lastRange->key] / $lastRange->key : 0;
					$capacity	= $data[$statusKey][$rangeKey] / $rangeKey;
					$change		= $average ? round( ( ( $capacity / $average ) - 1 ) * 100 ) : 0;
					$diff		= $change > 0 ? '+'.$change : $change;
					$label		.= '&nbsp;<small class="muted">'.$diff.'</small>';
				}
				$row[]	= HtmlTag::create( 'td', $label, ['style' => 'text-align: right'] );
			}
			$row[]	= HtmlTag::create( 'th', $statusLabel );
			$rows[]	= HtmlTag::create( 'tr', array_reverse( $row ) );
		}
		$table2	= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '', '15%', '15%', '15%', '15%' ),
			HtmlTag::create( 'thead', HtmlElements::TableHeads( $tableHeads ) ),
			HtmlTag::create( 'tbody', $rows ),
		], [
			'class'		=> 'table table-condensed table-fixed',
		] );
		$table1	= HtmlTag::create( 'table', [
			HtmlElements::ColumnGroup( '20%', '80%' ),
			HtmlTag::create( 'tbody', HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', '<span style="font-size: 3em">'.$model->count( ['status' => 0] ).'</span>', ['style' => 'text-align: right; vertical-align: bottom'] ),
				HtmlTag::create( 'td', '<span>Mails in der<br/>Warteschlange</span>', ['style' => 'vertical-align: bottom'] ),
			] ) ),
		], [
			'class'		=> 'table table-fixed',
		] );
		return $table1.'<br/>'.$table2;
	}

	public function enqueue(): void
	{
	}

	public function html(): void
	{
		try{
			$mail	= $this->getData( 'mail' );
			$helper	= new View_Helper_Mail_View_HTML( $this->env );
			$helper->setMailObjectInstance( $mail->object->instance );
			print( $helper->render() );
		}
		catch( Exception $e ){
			HtmlExceptionPage::display( $e );
		}
		exit;
	}

	public function index(): void
	{
		$script	= 'ModuleAdminMail.Queue.init();';
		$this->env->getPage()->js->addScriptOnReady( $script );
	}

	public function view(): void
	{
	}

	public function renderFact( string $key, $value ): string
	{
		$words	= $this->env->getLanguage()->getWords( 'admin/mail/queue' );
		if( $key === 'object')
			return '';
		if( $key === 'status' ){
			$value = $words['states'][$value].' <small class="muted">('.$value.')</small>';
		}
		else if( $key === 'mailClass' ){
			$original	= $value;
			$value	= preg_replace( '/^Mail_/', '', $value );
			$value	= preg_replace( '/_/', ':', $value );
			$value	= HtmlTag::create( 'abbr', $value, ['title' => $original] );
		}
		else if( str_ends_with( $key, 'At' ) ){
			if( !( (int) $value ) )
				return '';
			$helper	= new View_Helper_TimePhraser( $this->env );
			$date	= date( 'Y-m-d H:i:s', $value );
			$phrase	= $helper->convert( $value, TRUE, 'vor ' );
			$value	= $phrase.'&nbsp;<small class="muted">('.$date.')</small>';
		}
		else if( str_ends_with( $key, 'Id' ) ){
			if( (int) $value === 0 )
				return '';
		}
		else if( str_contains( $key, 'Address' ) && strlen( $value ) ){
			$icon	= HtmlTag::create( 'i', '', ['class' => 'icon-envelope'] );
			$link	= HtmlTag::create( 'a', $value, ['href' => 'mailto:'.$value] );
			$value	= $icon.'&nbsp;'.$link;
		}
		else{
			if( NULL === $value || 0 === strlen( $value ) )
				return '';
		}
		$label	= $words['view-facts']['label'.ucfirst( $key )];
		$term	= HtmlTag::create( 'dt', $label );
		$def	= HtmlTag::create( 'dd', $value.'&nbsp;' );
		return $term.$def;
	}

	protected function __onInit(): void
	{
		$this->env->getPage()->addCommonStyle( 'module.admin.mail.css' );
		$this->env->getPage()->js->addModuleFile( 'module.admin.mail.js' );
	}
}

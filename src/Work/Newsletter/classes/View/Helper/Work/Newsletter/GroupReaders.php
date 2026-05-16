<?php
declare(strict_types=1);

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

class View_Helper_Work_Newsletter_GroupReaders
{
	protected Environment $env;
	protected ?object $group		= NULL;
	protected array $readers		= [];
	protected array $words			= [];
	protected int $maxItems			= 50;
	protected int $nrItems			= 0;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function render(): string
	{
		$w	= (object) $this->words['edit_readers'];

		$labelEmpty		= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );
		$listReaders	= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );
		if( [] !== $this->readers )
			$listReaders	= $this->renderReaders();

		$iconAdd	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] ).'&nbsp;';
		$buttonAdd	= HtmlTag::create( 'a', $iconAdd.$w->buttonAdd, [
			'href'		=> './work/newsletter/reader/add/?groups[]='.$groupId,
			'class'		=> 'btn btn-success btn-small',
		] );

		return HtmlTag::create( 'div', [
			HtmlTag::create( 'div', $buttonAdd, ['class' => 'pull-right', 'style' => 'margin-top: 15px;'] ),
			HtmlTag::create( 'h3', $w->heading ),
			HtmlTag::create( 'div', $listReaders, [
				'class'	=> 'content-panel-inner',
				'id'	=> 'group-reader-list',
			] ),
		], ['class' => 'content-panel'] );
	}

	/**
	 *	@param		object		$group
	 *	@return		static
	 */
	public function setGroup( object $group ): self
	{
		$this->group	= $group;
		return $this;
	}

	/**
	 *	@param		array		$readers
	 *	@return		static
	 */
	public function setReaders( array $readers ): self
	{
		$this->nrItems	= count( $readers );
		$this->readers	= array_slice( $readers, 0, $this->maxItems );
		return $this;
	}

	/**
	 *	@param		array		$words
	 *	@return		static
	 */
	public function setWords( array $words ): self
	{
		$this->words	= $words;
		return $this;
	}


	//  --  PROTECTED  --  //


	protected function renderReaders(): string
	{
		$w			= (object) $this->words['edit_readers'];

		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-trash'] );
		$iconNew		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-star'] ).'&nbsp;';
		$iconReady		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] ).'&nbsp;';
		$iconGone		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-unlink'] ).'&nbsp;';
		$iconBanned		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-lock'] ).'&nbsp;';

		$list	= [];
		$helperStatus	= new View_Helper_StatusBadge();
		$helperStatus->setStatusMap( [
			View_Helper_StatusBadge::STATUS_POSITIVE	=> 1,
			View_Helper_StatusBadge::STATUS_TRANS		=> 0,
			View_Helper_StatusBadge::STATUS_NEUTRAL		=> -1,
			View_Helper_StatusBadge::STATUS_NEGATIVE	=> -2,
		] );
		$helperStatus->setLabelMap( [
			View_Helper_StatusBadge::STATUS_POSITIVE	=> $iconReady.'&nbsp;'.$this->words['reader_statuses'][View_Helper_StatusBadge::STATUS_POSITIVE],
			View_Helper_StatusBadge::STATUS_TRANS		=> $iconNew.'&nbsp;'.$this->words['reader_statuses'][View_Helper_StatusBadge::STATUS_TRANS],
			View_Helper_StatusBadge::STATUS_NEUTRAL		=> $iconGone.'&nbsp;'.$this->words['reader_statuses'][View_Helper_StatusBadge::STATUS_NEUTRAL],
			View_Helper_StatusBadge::STATUS_NEGATIVE	=> $iconBanned.'&nbsp;'.$this->words['reader_statuses'][View_Helper_StatusBadge::STATUS_NEGATIVE],
		] );

		foreach( $this->readers as $reader ){
			$urlReader		= './work/newsletter/reader/edit/'.$reader->newsletterReaderId;
			$urlRemove		= './work/newsletter/group/removeReader/'.$this->group->newsletterGroupId.'/'.$reader->newsletterReaderId;

			$label			= $reader->firstname.' '.$reader->surname;
			$linkReader		= HtmlTag::create( 'a', $label, ['href' => $urlReader] );

			$attributes		= [
				'href'		=> $urlRemove,
				'class'		=> 'btn btn-mini btn-inverse',
				'title'		=> $w->buttonRemove,
			];
			$linkRemove		= HtmlTag::create( 'a', $iconRemove, $attributes );
			$linkRemove		= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );

			$status			= $helperStatus->setStatus( $reader->status )->render();
			$list[]			= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $linkReader, ['class' => ''] ),
				HtmlTag::create( 'td', '<small>'.$reader->email.'</small>', ['class' => ''] ),
				HtmlTag::create( 'td', $status, ['class' => ''] ),
				HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
			] );
		}

		if( $this->maxItems < $this->nrItems ){
			$nrMore		= $this->nrItems - $this->maxItems;
			$textMore	= '<p><center>... und '.$nrMore.' Weitere.</center></p>';
			$list[]		= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $textMore, ['colspan' => 4] ),
			] );
		}

		$numberBadge	= HtmlTag::create( 'span', '('.$this->nrItems.')', ['class' => 'muted'] );
		$colgroup		= HtmlElements::ColumnGroup( '', '', '100px', '40px' );
		$tableHeads		= HtmlElements::TableHeads( ['Zugeordnete Leser '.$numberBadge] );
		$thead			= HtmlTag::create( 'thead', $tableHeads );
		$tbody			= HtmlTag::create( 'tbody', $list );
		return HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
			'class'	=> 'table table-condensed table-striped table-fixed'
		] );
	}
}

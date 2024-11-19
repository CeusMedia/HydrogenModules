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

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	public function setGroup( object $group ): self
	{
		$this->group	= $group;
		return $this;
	}
	public function setWords( array $words ): self
	{
		$this->words	= $words;
		return $this;
	}

	public function setReaders( array $readers ): self
	{
		$this->readers	= $readers;
		return $this;
	}
	public function render(): string
	{
		$w			= (object) $this->words['edit_readers'];

		$labelEmpty		= HtmlTag::create( 'em', $w->empty, ['class' => 'muted'] );
		$listReaders	= HtmlTag::create( 'div', $labelEmpty, ['class' => 'alert alert-info'] );
		if( [] !== $this->readers )
			$listReaders	= $this->renderReaders();

		return HtmlTag::create( 'div', [
			HtmlTag::create( 'h3', $w->heading ),
			HtmlTag::create( 'div', $listReaders, [
				'class'	=> 'content-panel-inner',
				'id'	=> 'group-reader-list',
			] ),
		], ['class' => 'content-panel'] );
	}

	protected function renderReaders(): string
	{
		$w			= (object) $this->words['edit_readers'];

		$iconRemove		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-remove'] ).'&nbsp;';
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
			View_Helper_StatusBadge::STATUS_POSITIVE	=> $iconReady.'&nbsp;ready',
			View_Helper_StatusBadge::STATUS_TRANS		=> $iconNew.'&nbsp;new',
			View_Helper_StatusBadge::STATUS_NEUTRAL		=> $iconGone.'&nbsp;gone',
			View_Helper_StatusBadge::STATUS_NEGATIVE	=> $iconBanned.'&nbsp;banned',
		] );

		foreach( $this->readers as $reader ){
			$urlReader		= './work/newsletter/reader/edit/'.$reader->newsletterReaderId;
			$urlRemove		= './work/newsletter/group/removeReader/'.$this->group->newsletterGroupId.'/'.$reader->newsletterReaderId;

			$label			= $reader->firstname.' '.$reader->surname;
			$linkReader		= HtmlTag::create( 'a', $label, ['href' => $urlReader] );

			$attributes		= [
				'href'		=> $urlRemove,
				'class'		=> 'btn btn-mini btn-inverse',
			];
			$linkRemove		= HtmlTag::create( 'a', $iconRemove.$w->buttonRemove, $attributes );
			$linkRemove		= HtmlTag::create( 'div', $linkRemove, ['class' => 'pull-right'] );

			$status			= $helperStatus->setStatus( $reader->status )->render();
			$list[]			= HtmlTag::create( 'tr', [
				HtmlTag::create( 'td', $linkReader, ['class' => ''] ),
				HtmlTag::create( 'td', $reader->email, ['class' => ''] ),
				HtmlTag::create( 'td', $status, ['class' => ''] ),
				HtmlTag::create( 'td', $linkRemove, ['class' => ''] ),
			] );
		}
		$numberBadge	= HtmlTag::create( 'span', '('.count( $this->readers ).')', ['class' => 'muted'] );
		$colgroup		= HtmlElements::ColumnGroup( '', '', '100px', '120px' );
		$tableHeads		= HtmlElements::TableHeads( ['Zugeordnete Leser '.$numberBadge] );
		$thead			= HtmlTag::create( 'thead', $tableHeads );
		$tbody			= HtmlTag::create( 'tbody', $list );
		return HtmlTag::create( 'table', $colgroup.$thead.$tbody, [
			'class'	=> 'table table-condensed table-striped table-fixed'
		] );

	}
}

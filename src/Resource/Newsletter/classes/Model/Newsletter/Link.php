<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */

use CeusMedia\HydrogenFramework\Model;

/**
 *	...
 *	@author			Christian Würker <christian.wuerker@ceusmedia.de>
 *	@copyright		2013-2025 Ceus Media (https://ceusmedia.de/)
 */
class Model_Newsletter_Link extends Model
{
	protected string $name			= 'newsletter_links';

	protected array $columns		= [
		'newsletterLinkId',
		'url',
		'title',
	];

	protected string $primaryKey	= 'newsletterLinkId';

	protected array $indices		= [
		'url',
		'title',
	];

	protected int $fetchMode		= PDO::FETCH_OBJ;
}

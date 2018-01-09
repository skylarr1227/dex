<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Controllers;

use Jp\Dex\Application\Models\MoveUsageMonth\MoveUsageMonthModel;
use Jp\Dex\Domain\Languages\LanguageId;
use Psr\Http\Message\ServerRequestInterface;

class MoveUsageMonthController
{
	/** @var MoveUsageMonthModel $moveUsageMonthModel */
	private $moveUsageMonthModel;

	/**
	 * Constructor.
	 *
	 * @param MoveUsageMonthModel $moveUsageMonthModel
	 */
	public function __construct(
		MoveUsageMonthModel $moveUsageMonthModel
	) {
		$this->moveUsageMonthModel = $moveUsageMonthModel;
	}

	/**
	 * Get usage data to create a list of Pokémon who use a specific move.
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return void
	 */
	public function setData(ServerRequestInterface $request) : void
	{
		$year = (int) $request->getAttribute('year');
		$month = (int) $request->getAttribute('month');
		$formatIdentifier = $request->getAttribute('formatIdentifier');
		$rating = (int) $request->getAttribute('rating');
		$moveIdentifier = $request->getAttribute('moveIdentifier');
		$languageId = new LanguageId((int) $request->getAttribute('languageId'));

		$this->moveUsageMonthModel->setData(
			$year,
			$month,
			$formatIdentifier,
			$rating,
			$moveIdentifier,
			$languageId
		);
	}
}
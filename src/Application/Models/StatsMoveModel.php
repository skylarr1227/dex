<?php
declare(strict_types=1);

namespace Jp\Dex\Application\Models;

use Jp\Dex\Domain\Formats\FormatRepositoryInterface;
use Jp\Dex\Domain\Languages\LanguageId;
use Jp\Dex\Domain\Moves\MoveDescription;
use Jp\Dex\Domain\Moves\MoveDescriptionRepositoryInterface;
use Jp\Dex\Domain\Moves\MoveName;
use Jp\Dex\Domain\Moves\MoveNameRepositoryInterface;
use Jp\Dex\Domain\Moves\MoveRepositoryInterface;
use Jp\Dex\Domain\Stats\Usage\MonthQueriesInterface;
use Jp\Dex\Domain\Stats\Usage\RatingQueriesInterface;
use Jp\Dex\Domain\Usage\StatsMovePokemon;
use Jp\Dex\Domain\Usage\StatsMovePokemonRepositoryInterface;

class StatsMoveModel
{
	/** @var DateModel $dateModel */
	private $dateModel;

	/** @var FormatRepositoryInterface $formatRepository */
	private $formatRepository;

	/** @var MoveRepositoryInterface $moveRepository */
	private $moveRepository;

	/** @var MonthQueriesInterface $monthQueries */
	private $monthQueries;

	/** @var RatingQueriesInterface $ratingQueries */
	private $ratingQueries;

	/** @var MoveNameRepositoryInterface $moveNameRepository */
	private $moveNameRepository;

	/** @var MoveDescriptionRepositoryInterface $moveDescriptionRepository */
	private $moveDescriptionRepository;

	/** @var StatsMovePokemonRepositoryInterface $statsMovePokemonRepository */
	private $statsMovePokemonRepository;


	/** @var string $month */
	private $month;

	/** @var string $formatIdentifier */
	private $formatIdentifier;

	/** @var int $rating */
	private $rating;

	/** @var string $moveIdentifier */
	private $moveIdentifier;

	/** @var LanguageId $languageId */
	private $languageId;

	/** @var bool $prevMonthDataExists */
	private $prevMonthDataExists;

	/** @var bool $nextMonthDataExists */
	private $nextMonthDataExists;

	/** @var int[] $ratings */
	private $ratings = [];

	/** @var MoveName $moveName */
	private $moveName;

	/** @var MoveDescription $moveDescription */
	private $moveDescription;

	/** @var StatsMovePokemon[] $pokemon */
	private $pokemon = [];


	/**
	 * Constructor.
	 *
	 * @param DateModel $dateModel
	 * @param FormatRepositoryInterface $formatRepository
	 * @param MoveRepositoryInterface $moveRepository
	 * @param MonthQueriesInterface $monthQueries
	 * @param RatingQueriesInterface $ratingQueries
	 * @param MoveNameRepositoryInterface $moveNameRepository
	 * @param MoveDescriptionRepositoryInterface $moveDescriptionRepository
	 * @param StatsMovePokemonRepositoryInterface $statsMovePokemonRepository
	 */
	public function __construct(
		DateModel $dateModel,
		FormatRepositoryInterface $formatRepository,
		MoveRepositoryInterface $moveRepository,
		MonthQueriesInterface $monthQueries,
		RatingQueriesInterface $ratingQueries,
		MoveNameRepositoryInterface $moveNameRepository,
		MoveDescriptionRepositoryInterface $moveDescriptionRepository,
		StatsMovePokemonRepositoryInterface $statsMovePokemonRepository
	) {
		$this->dateModel = $dateModel;
		$this->formatRepository = $formatRepository;
		$this->moveRepository = $moveRepository;
		$this->monthQueries = $monthQueries;
		$this->ratingQueries = $ratingQueries;
		$this->moveNameRepository = $moveNameRepository;
		$this->moveDescriptionRepository = $moveDescriptionRepository;
		$this->statsMovePokemonRepository = $statsMovePokemonRepository;
	}

	/**
	 * Get usage data to recreate a stats usage file, such as
	 * http://www.smogon.com/stats/2014-11/ou-1695.txt.
	 *
	 * @param string $month
	 * @param string $formatIdentifier
	 * @param int $rating
	 * @param string $moveIdentifier
	 * @param LanguageId $languageId
	 *
	 * @return void
	 */
	public function setData(
		string $month,
		string $formatIdentifier,
		int $rating,
		string $moveIdentifier,
		LanguageId $languageId
	) : void {
		$this->month = $month;
		$this->formatIdentifier = $formatIdentifier;
		$this->rating = $rating;
		$this->moveIdentifier = $moveIdentifier;
		$this->languageId = $languageId;

		// Get the previous month and the next month.
		$this->dateModel->setData($month);
		$thisMonth = $this->dateModel->getThisMonth();
		$prevMonth = $this->dateModel->getPrevMonth();
		$nextMonth = $this->dateModel->getNextMonth();

		// Get the format.
		$format = $this->formatRepository->getByIdentifier($formatIdentifier);

		// Get the move.
		$move = $this->moveRepository->getByIdentifier($moveIdentifier);

		// Does usage data exist for the previous month?
		$this->prevMonthDataExists = $this->monthQueries->doesMonthFormatDataExist(
			$prevMonth,
			$format->getId()
		);

		// Does usage data exist for the next month?
		$this->nextMonthDataExists = $this->monthQueries->doesMonthFormatDataExist(
			$nextMonth,
			$format->getId()
		);

		// Get the ratings for this month.
		$this->ratings = $this->ratingQueries->getByMonthAndFormat(
			$thisMonth,
			$format->getId()
		);

		// Get the move name.
		$this->moveName = $this->moveNameRepository->getByLanguageAndMove(
			$languageId,
			$move->getId()
		);

		// Get the move description.
		$this->moveDescription = $this->moveDescriptionRepository->getByGenerationAndLanguageAndMove(
			$format->getGenerationId(),
			$languageId,
			$move->getId()
		);

		// Get the Pokémon usage data.
		$this->pokemon = $this->statsMovePokemonRepository->getByMonth(
			$thisMonth,
			$prevMonth,
			$format->getId(),
			$rating,
			$move->getId(),
			$format->getGenerationId(),
			$languageId
		);
	}

	/**
	 * Get the month.
	 *
	 * @return string
	 */
	public function getMonth() : string
	{
		return $this->month;
	}

	/**
	 * Get the format identifier.
	 *
	 * @return string
	 */
	public function getFormatIdentifier() : string
	{
		return $this->formatIdentifier;
	}

	/**
	 * Get the rating.
	 *
	 * @return int
	 */
	public function getRating() : int
	{
		return $this->rating;
	}

	/**
	 * Get the move identifier.
	 *
	 * @return string
	 */
	public function getMoveIdentifier() : string
	{
		return $this->moveIdentifier;
	}

	/**
	 * Get the language id.
	 *
	 * @return LanguageId
	 */
	public function getLanguageId() : LanguageId
	{
		return $this->languageId;
	}

	/**
	 * Get the date model.
	 *
	 * @return DateModel
	 */
	public function getDateModel() : DateModel
	{
		return $this->dateModel;
	}

	/**
	 * Does usage rated data exist for the previous month?
	 *
	 * @return bool
	 */
	public function doesPrevMonthDataExist() : bool
	{
		return $this->prevMonthDataExists;
	}

	/**
	 * Does usage rated data exist for the next month?
	 *
	 * @return bool
	 */
	public function doesNextMonthDataExist() : bool
	{
		return $this->nextMonthDataExists;
	}

	/**
	 * Get the ratings for this month.
	 *
	 * @return int[]
	 */
	public function getRatings() : array
	{
		return $this->ratings;
	}

	/**
	 * Get the move name.
	 *
	 * @return MoveName
	 */
	public function getMoveName() : MoveName
	{
		return $this->moveName;
	}

	/**
	 * Get the move description.
	 *
	 * @return MoveDescription
	 */
	public function getMoveDescription() : MoveDescription
	{
		return $this->moveDescription;
	}

	/**
	 * Get the Pokémon.
	 *
	 * @return StatsMovePokemon[]
	 */
	public function getPokemon() : array
	{
		return $this->pokemon;
	}
}
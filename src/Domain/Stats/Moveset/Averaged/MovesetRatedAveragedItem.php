<?php
declare(strict_types=1);

namespace Jp\Dex\Domain\Stats\Moveset\Averaged;

use DateTime;
use Jp\Dex\Domain\Formats\FormatId;
use Jp\Dex\Domain\Items\ItemId;
use Jp\Dex\Domain\Pokemon\PokemonId;
use Jp\Dex\Domain\Stats\Exceptions\InvalidMonthException;
use Jp\Dex\Domain\Stats\Exceptions\InvalidPercentException;
use Jp\Dex\Domain\Stats\Exceptions\InvalidRatingException;
use Jp\Dex\Domain\Stats\ValidateMonthTrait;

/**
 * This class holds data derived from averaging an item's usage percent over a
 * span of multiple months.
 */
class MovesetRatedAveragedItem
{
	use ValidateMonthTrait;

	/** @var DateTime $start */
	private $start;

	/** @var DateTime $end */
	private $end;

	/** @var FormatId $formatId */
	private $formatId;

	/** @var int $rating */
	private $rating;

	/** @var PokemonId $pokemonId */
	private $pokemonId;

	/** @var ItemId $itemId */
	private $itemId;

	/** @var float $percent */
	private $percent;

	/**
	 * Constructor.
	 *
	 * @param DateTime $start
	 * @param DateTime $end
	 * @param FormatId $formatId
	 * @param int $rating
	 * @param PokemonId $pokemonId
	 * @param ItemId $itemId
	 * @param float $percent
	 *
	 * @throws InvalidMonthException if $start or $end is invalid.
	 * @throws InvalidRatingException if $rating is invalid.
	 * @throws InvalidPercentException if $percent is invalid
	 */
	public function __construct(
		DateTime $start,
		DateTime $end,
		FormatId $formatId,
		int $rating,
		PokemonId $pokemonId,
		ItemId $itemId,
		float $percent
	) {
		$this->validateMonth($start);
		$this->validateMonth($end);

		if ($rating < 0) {
			throw new InvalidRatingException('Invalid rating: ' . $rating);
		}

		if ($percent < 0 || $percent > 100) {
			throw new InvalidPercentException('Invalid percent: ' . $percent);
		}

		$this->start = $start;
		$this->end = $end;
		$this->formatId = $formatId;
		$this->rating = $rating;
		$this->pokemonId = $pokemonId;
		$this->itemId = $itemId;
		$this->percent = $percent;
	}

	/**
	 * Get the start month.
	 *
	 * @return DateTime
	 */
	public function getStart() : DateTime
	{
		return $this->start;
	}

	/**
	 * Get the end month.
	 *
	 * @return DateTime
	 */
	public function getEnd() : DateTime
	{
		return $this->end;
	}

	/**
	 * Get the format id.
	 *
	 * @return FormatId
	 */
	public function getFormatId() : FormatId
	{
		return $this->formatId;
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
	 * Get the Pokémon id.
	 *
	 * @return PokemonId
	 */
	public function getPokemonId() : PokemonId
	{
		return $this->pokemonId;
	}

	/**
	 * Get the item id.
	 *
	 * @return ItemId
	 */
	public function getItemId() : ItemId
	{
		return $this->itemId;
	}

	/**
	 * Get the percent.
	 *
	 * @return float
	 */
	public function getPercent() : float
	{
		return $this->percent;
	}
}
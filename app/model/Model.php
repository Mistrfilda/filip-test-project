<?php

declare(strict_types=1);


namespace App\Model;


use Nette\Utils\Strings;
use Predis\Client;


class Model
{
	const
		REDIS_GAME_ID = 'game:',
		REDIS_PLAYER_ID = 'player:';

	/** @var Client */
	private $redisClient;

	public function __construct(Client $redisClient)
	{
		$this->redisClient = $redisClient;
	}

	public function save(int $gameId, int $userId, int $score) : int
	{
		$redisGameId = self::REDIS_GAME_ID . $gameId;
		$redisUserId = self::REDIS_PLAYER_ID . $userId;
		return $this->redisClient->zadd($redisGameId, [$redisUserId => $score]);
	}

	public function retrieveBestPlayers(int $gameId) : array
	{
		$redisGameId = self::REDIS_GAME_ID . $gameId;
		$players = $this->redisClient->zrevrange($redisGameId, 0, 10, ['WITHSCORES' => 1]);

		$lastScore = 0;
		$currentPosition = 1;
		$sortedPlayers = [];

		foreach ($players as $player => $score) {
			$player = Strings::split($player, '~' . self::REDIS_PLAYER_ID . '~')[1];
			if ($score === $lastScore) {
				$currentPosition = $currentPosition - 1;
				$lastPlayer = array_pop($sortedPlayers);

				if (is_array($lastPlayer)) {
					$sortedPlayers[$currentPosition] = array_merge($lastPlayer, (array)$player);
				} else {
					$sortedPlayers[$currentPosition] = [$lastPlayer, $player];
				}

				$currentPosition++;
				continue;
			}

			$sortedPlayers[$currentPosition] = $player;
			$lastScore = $score;
			$currentPosition = $currentPosition + 1;
		}

		return $sortedPlayers;
	}
}
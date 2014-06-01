<?php

/**
 * A player in a given world
 * This is an unorthodox model since it tacitly violates the ActiveRecord paradigm:
 * the ORM table is dyanmically linked based on the current world.
 */
class Player extends AppModel {

	public $useDbConfig = 'players';
	//public $tablePrefix = 'en'; #CASUALWORLDHACK removed

	private $player_id;
	private $username;
	private $tribe_id;

	/**
	 * Switches table given a world; i.e. use table en69, or en70
	 * @param int $world
	 */
	public function use_table ($world) {
		if ($world == '1' || $world == '2') { #CASUALWORLDHACK
			$this->tablePrefix = 'enp';
		}
		else {
			$this->tablePrefix = 'en';
		}

		$this->useTable = $world;
	}

	public function player_id () {
		return $this->player_id;
	}

	public function username () {
		return $this->username;
	}

	public function tribe_id () {
		return $this->tribe_id;
	}

}

?>
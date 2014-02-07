<?php

define('HIGH', 5000);
define('LOW', 2000);
define('START_COUNTER', 3600);

class ExclaimGrill implements IGrillPlugin {
	private $bot;

	private $counters = array();
	private $lastlines = array();

	private $lines = array();

	private $time;

	public function __construct($bot) {
		$this->bot = $bot;
		$this->time = time();
		$this->reloadLines();
	}

	public function setupHandlers($irc) {
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '(.*)', $this, 'didMsg');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^!reload$/i', $this, 'doReload');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^!shutup$/i', $this, 'doShutup');
		$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '/^!debugline$/i', $this, 'debugLine');
		$irc->registerTimeHandler(20000, $this, 'checkExclaims');
	}

	public function didMsg(&$irc, &$data) {
		if(array_key_exists($data->channel, $this->counters))
			$this->counters[$data->channel] *= 0.8;
		else
			$this->counters[$data->channel] = START_COUNTER;
	}

	public function doReload(&$irc, &$data) {
		$this->reloadLines();
	}

	public function debugLine(&$irc, &$data) {
		$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $this->counters[$data->channel] . "s remaining, last line was: \"" . $this->lastlines[$data->channel] . "\"");
	}

	public function exclaimThings(&$irc, $channel) {
		if(count($this->lines) == 0)
			return;

		$line = "";

		do {
			$line = $this->lines[array_rand($this->lines)];
		} while($line == $this->lastlines[$channel]);

		$irc->message(SMARTIRC_TYPE_CHANNEL, $channel, $line);
		$this->counters[$channel] = START_COUNTER;
		$this->lastlines[$channel] = $line;
	}

	public function checkExclaims(&$irc) {
		$elapsed = time() - $this->time;

		foreach($this->counters as $k=>$c) {
			$this->counters[$k] -= $elapsed;
			if($this->counters[$k] <= 0)
				$this->exclaimThings($irc, $k);
		}

		$this->time = time();
	}

	public function reloadLines() {
		$this->bot->checkData("exclamations.txt");

		$file = $this->bot->getData("exclamations.txt");
		$tmplines = explode("\n", $file);
		$this->lines = array();

		foreach($tmplines as $tmp) {
			$this->lines[] = $tmp;
		}

		echo "Exclaim Grill: Loaded in " . count($this->lines) . " lines.\n";
	}

	public function eventOnJoin($irc, $channel) {
		$this->counters[$channel] = START_COUNTER;
		$this->lastlines[$channel] = "";
	}

	public function eventOnLeave($irc, $channel) {
		unset($this->counters[$channel]);
		unset($this->lastlines[$channel]);
	}
}
?>
<?php
require_once("vendor/ProtoIRC/protoirc.php");


class GrillBot {
	public $irc;

	function __construct($config) {
		$connectstring = "irc://" . $config['user']['nick'] . ":" . $config['user']['token'] . "@" . $config['server']['host'] . ":" . $config['server']['port'] . "/cueball61";

		echo "String: " . $connectstring . "\n";

		$this->irc = new ProtoIRC($connectstring, function($irc) {
			echo "Test.\n";
			$irc->stdout("Connected.");
			$irc->send('USER ' . $config['user']['nick']);
			$irc->send('PASS ' . $config['user']['token']);
			$irc->send('NICK ' . $config['user']['nick']);
		
			foreach($config['channels'] as $chan) {
				$irc->join('#' . $chan);
				$irc->send("#" . $chan, "Hello!");
			}
		});
	}

	function start() {
		$this->setup();
		var_dump($this->irc);
		$this->irc->go();
	}

	function setup() {
		$this->irc->msg('/^echo (.*)/', function ($irc, $nick, $channel, $line) {
			$irc->send($irc->last, $line);
		});

		$this->irc->in('/(.*)/', function ($irc, $line) {
  			$irc->stdout("<< {$line}\n", '_black');
		});
	}
}

<?php
require_once("Net/SmartIRC.php");
require_once("IGrillPlugin.php");

class GrillBot {
	public $irc;

	private $plugins = array();
	private $cooldowns = array();

	function __construct($config) {
		$this->irc = new Net_SmartIRC();
		$this->irc->setUseSockets(true);
		$this->irc->setChannelSyncing(true);
    	$this->irc->setDebug(SMARTIRC_DEBUG_ALL);
		$this->irc->connect($config['server']['host'], $config['server']['port']);
		$this->irc->login($config['user']['nick'], $config['user']['realname'], 8, 'GrillBot',$config['user']['token']);   
		$this->irc->join($config['channels']);
	}

	function start() {
		$this->setup();
		$this->irc->listen();
	}

	function setup() {
		$this->loadPlugins();
		$this->setupPlugins();
	}

	function checkCooldown($channel, $key, $time) {
		if(!array_key_exists($channel, $this->cooldowns) || !array_key_exists($key, $this->cooldowns[$channel]) || $this->cooldowns[$channel][$key] < time() + $time) {
			$this->cooldowns[$channel][$key] = time();
			return true;
		}

		return false;
	}

	function loadPlugins() {
		foreach(glob('plugins/*.php') as $plugin) {
			require_once($plugin);
			$info = pathinfo($plugin);

			if(class_exists($info['filename']))
				$this->plugins[$info['filename']] = new $info['filename']($this);
		}
	}

	function setupPlugins() {
		foreach($this->plugins as $p) {
			$p->setupHandlers($this->irc);
		}
	}
}

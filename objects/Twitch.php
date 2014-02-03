<?php
class Twitch {
	public $status;
	public $displayName;
	public $gameName;
	public $name;
	public $teamName;
	public $teamDisplayName;
	public $logoUrl;
	public $url;

	public static function getStatus($channel) {
		try {
			$json = json_decode(file_get_contents("https://api.twitch.tv/kraken/channels/" . $channel), true);

			if(!array_key_exists('status', $json)) {
				if(array_key_exists("message", $json))
					echo "TwitchGet Error: " . $json['message'] . "\n";
				else
					echo "TwitchGet Error: No Error.\n";
				return null;
			}

			$return = new Twitch();
			$return->status = $json['status'];
			$return->displayName = $json['display_name'];
			$return->gameName = $json['game'];
			$return->name = $json['name'];
			$return->teamName = $json['primary_team_name'];
			$return->teamDisplayName = $json['primary_team_display_name'];
			$return->logoUrl = $json['logo'];
			$return->url = $json['url'];
			
			return $return;
		} catch(Exception $e) {
			echo "TwitchGet Error: " . $e->getMessage() . "\n";
			return null;
		}
	}
}
?>
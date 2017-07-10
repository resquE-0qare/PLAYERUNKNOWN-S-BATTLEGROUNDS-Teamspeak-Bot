<?php
require_once("libraries/TeamSpeak3/TeamSpeak3.php");
$cfg = require_once("config.php");
date_default_timezone_set('UTC');
$tsHandle = TeamSpeak3::factory("serverquery://".$cfg->tsInfo->user.":".$cfg->tsInfo->pass."@".$cfg->tsInfo->host.":".$cfg->tsInfo->qport."/?server_port=".$cfg->tsInfo->port."&nickname=".urlencode($cfg->tsInfo->nickname));

$pubgStats = '[center][size=15]PUBG Stats[/size][/center][center][size=7]['.date("Y-m-d H:i:s").'][/size][/center]'."\n\n";

if(strpos(file_get_contents($_SERVER["SCRIPT_FILENAME"]), "CltjZW50ZXJdW3NpemU9OF1bVVJMPWh0dHBzOi8vMHFhcmUuY29tXTBxYXJlLmNvbVsvVVJMXVsvc2l6ZV1bL2NlbnRlcl0=") == false) {
  exit();
}

foreach(json_decode(file_get_contents('players.json')) as $steamID){
  $context = stream_context_create(array(
    'http' => array(
      'method' => 'GET',
      'header' => "TRN-Api-Key: ".$cfg->botSettings->apiKey."\r\n"
    )
  ));
  $jsonData = json_decode(file_get_contents('http://pubgtracker.com/api/search?steamId='.$steamID, false, $context));
  $jsonData = json_decode(file_get_contents('http://pubgtracker.com/api/profile/pc/'.$jsonData->Nickname, false, $context));

  $pubgStats .= '[B]'.$jsonData->PlayerName."[/B]\n";
  for ($i=0; $i < count($jsonData->Stats); $i++) {
    if($jsonData->Stats[$i]->Region == 'agg' && $jsonData->Stats[$i]->Match == 'solo'){
      $soloData = $jsonData->Stats[$i]->Stats;
      foreach($cfg->botSettings->dispValues as $statID){
        $pubgStats .= $soloData[$statID]->label.': '.$soloData[$statID]->displayValue."\n";
      }
    }
  }
  $pubgStats .= "\n";
}

$tsHandle->channelGetById($cfg->botSettings->infoChannelID)["channel_description"] = $pubgStats.base64_decode('CltjZW50ZXJdW3NpemU9OF1bVVJMPWh0dHBzOi8vMHFhcmUuY29tXTBxYXJlLmNvbVsvVVJMXVsvc2l6ZV1bL2NlbnRlcl0=');
?>

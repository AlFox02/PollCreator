<?php


include 'PollCreator.php';
$iquery=$update['inline_query'];
if($iquery){
	$queryID=$iquery['id'];
    $userID=$iquery['from']['id'];
    $username=$iquery['from']['username'];
    $msg=$iquery['query'];
}
$iMessageID=$update['callback_query']['inline_message_id'];

#####___ FUNZIONI UTILI ___#####
	# answerInlineQuery
function aiq($queryID, $results, $description="send Poll", $spt="Vai al bot"){
	global $api;
	$url="https://api.telegram.org/$api/answerInlineQuery?inline_query_id=$queryID";
    $json=urlencode(json_encode($results));
    $spt=urlencode($spt);
    $q=file_get_contents($url . "&results=$json&cache_time=2&switch_pm_text=$spt&switch_pm_parameter=t");
}

	# editMessageText (con inline_message_id)
function iEdit($inline_message_id, $msg, $menu, $type='inline_keyboard', $parse_mode="html")
{
	global $api;
    
    $msg = urlencode($msg);
	$query="https://api.telegram.org/$api/editMessageText?inline_message_id=$inline_message_id&text=$msg&parse_mode=$parse_mode";
	  
    if($menu)
    {
    	if($type == "") { $type = "inline_keyboard"; }
    	$t = array($type=>$menu, "resize_keyboard"=>true);
        $s = json_encode($t);
        $s = urlencode($s);
        $query .= "&reply_markup=$s";
    }
    
    file_get_contents($query);
}

#####____ CODICE BOT ____#####

$poll=new vote();

		//sezione inline
if($queryID){
    $e=explode(" ", $msg);
    $poll_id=$e[0];
    $creator=$e[1];
    $q=$poll->sendPoll($poll_id, $creator);
	$q=json_decode($q, true);
	$r=$q['description'];
    $poll_id=$q['poll_id'];
    $creator=$q['creator'];
    foreach($q['choice'] as $key=>$value){
    	$menu[]=[['text'=>$key.'-'.$value, 'callback_data'=>"vote:$poll_id-$creator-$key"]]; 
    }
	$results=[["type"=>"article", "id"=>"11111111", "title"=>"POLL", "message_text"=>"$r", "parse_mode"=>"html", "description"=>"Invia Poll", "reply_markup"=>array("inline_keyboard"=>$menu),]];
	aiq($queryID, $results);
}

		//sezione normale
if($msg=="/start"){
	$r="Ciao $usernme!\nQuesto bot ti permette di creare sondaggi e gestire le statistiche. E' il bot di prova del plugin \"statistiche\" che potrai presto trovare pubblicato su @AVPlugin. Non sei ancora entrato? Fallo ora!\n\nPer creare un nuovo sondaggio digita /new";
    $menu[]=[["text"=>"AVPlugin", "url"=>"t.me/AVPlugin"]];
    sm($chatID, $r, $menu);
}


if(strpos($cbdata, "vote:")===0){
	$up=str_replace("vote:", "", $cbdata);
    $e=explode("-", $up, 3);
    $poll_id=$e[0];
    $creator=$e[1];
    $choice=$e[2];
	$poll->removeAllChoices($poll_id, $creator, $userID);
    $poll->addChoice($poll_id, $creator, $userID, $choice);
	$msg="/update $poll_id-$creator";
}

if(strpos($msg,"/update")===0){
	$poll=new vote();
    $e=explode("-", str_replace("/update ", "", $msg));
    $poll_id=$e[0];
    $creator=$e[1];
    $update=($e[2])?true:false;
    $q=$poll->sendPoll($poll_id, $creator);
	$q=json_decode($q, true);
	$r=$q['description'];
    $percentage=json_decode($poll->getPollPercentage("$poll_id", $creator), true);
    foreach($percentage[0] as $key=>$value){
    	$r.="\n$key - $value%";
    }
    $poll_id=$q['poll_id'];
    $creator=$q['creator'];
    foreach($q['choice'] as $key=>$value){
    	$menu[]=[['text'=>$key.'-'.$value, 'callback_data'=>"vote:$poll_id-$creator-$key"]]; 
    }
    $menu[]=[["text"=>"$sondaggio Aggiorna Dati", "callback_data"=>"/update $poll_id-$creator-update"]];
    if($creator==$userID && $chatID>0){
    $menu[]=[["text"=>"Condividi", "switch_inline_query"=>"$poll_id $userID"]];    
    }
    if($iMessageID){ iedit($iMessageID, $r, $menu); if($update){ cb_reply($cbid, "Statistiche Aggiornate", true, $cbmid, $r, $menu); } else {  cb_reply($cbid, "Voto Aggiunto!", true, $cbmid, $r, $menu); } exit;}
    if($cbdata){ cb_reply($cbid, "OK", false, $cbmid, $r, $menu);   if($update){ cb_reply($cbid, "Statistiche Aggiornate", true, $cbmid, $r, $menu); } else { cb_reply($cbid, "Voto Aggiunto!", true, $cbmid, $r, $menu); } exit; } 
}


if($msg=="/new"){ 
	sm($chatID, "Bene! Per creare un nuovo poll inviami i seguenti parametri formattati nel seguente modo:\n/new Descrizione. a fine descrizione metti queste due stanghette: ||  \n 1  opzione\n2 opzione etc");
	exit;
}

if(strpos($msg, "/new")===0){
	$msg=str_replace("/new ", "", $msg);
    $e=explode("||", $msg, 2);
    $desc=$e[0];
    $choice=$e[1];
    $ec=explode("\n", $choice);
	$id=rand(0, 100000);
    $poll->newPoll("poll_$id", $userID);
    $poll->addDescriptionPoll("poll_$id", $userID, $desc);
    foreach($ec as $cho){
    $poll->addChoicePoll("poll_$id", $userID, $cho); }
    $q=$poll->sendPoll("poll_$id", "$userID");
	$q=json_decode($q, true);
	$r=$q['description'];
    $poll_id=$q['poll_id'];
    $creator=$q['creator'];
    foreach($q['choice'] as $key=>$value){
    	$menu[]=[['text'=>$key.'-'.$value, 'callback_data'=>"vote:$poll_id-$creator-$key"]]; 
    }
    $menu[]=[["text"=>"$sondaggio Aggiorna Dati", "callback_data"=>"/update $poll_id-$creator"]];
    $menu[]=[["text"=>"Condividi", "switch_inline_query"=>"poll_$id $userID"]];
    sm($chatID, $r, $menu);
}
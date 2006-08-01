<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_getonlinenick extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];

    $c =& $this->c;
    $container =& $c->getContainerInstance();

    // take care to disconnect timeouted users on this channel
    $disconnected_users = $container->removeObsoleteNick($recipient,$c->timeout);
    if (isset($disconnected_users["nick"]))
      foreach ($disconnected_users["nick"] as $n)
      {
        $cmdp = $p;
        $cmdp["param"] = _pfc("%s quit (timeout)", $n);
        $cmdp["flag"] = 2;
        $cmd =& pfcCommand::Factory("notice");
        $cmd->run($xml_reponse, $cmdp);
      }
    
    // get the cached nickname list
    $nicklist_sid = $c->prefix."nicklist_".$c->getId()."_".$clientid."_".$recipientid;
    $oldnicklist = isset($_SESSION[$nicklist_sid]) ? $_SESSION[$nicklist_sid] : array();

    // get the real nickname list
    $users = $container->getOnlineNick($recipient);
    $nicklist = array();
    if (isset($users["nickid"]))
      foreach($users["nickid"] as $nid)
        $nicklist[] = $container->getNickname($nid);
    sort($nicklist);

    if ($oldnicklist != $nicklist) // check if the nickname list must be updated on the client side
    {
      $_SESSION[$nicklist_sid] = $nicklist;
      
      if ($c->debug)
      {
        $nicklist2 = implode(",",$nicklist);
        pxlog("/getonlinenick (nicklist updated - nicklist=".$nicklist2.")", "chat", $c->getId());
      }

      // build and send the nickname list
      $js = "";
      foreach ($nicklist as $nick)
      {
        $nickname = addslashes($nick); // must escape ' charactere for javascript string
        $js      .= "'".$nickname."',";
      }
      $js = substr($js, 0, strlen($js)-1); // remove last ','
      $xml_reponse->addScript("pfc.updateNickList('".$recipientid."',Array(".$js."));");
    }
  
  }
}

?>
<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");

class pfcCommand_notice extends pfcCommand
{
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $msg         = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
    $flag        = isset($p["flag"]) ? $p["flag"] : 3;
    
    $c =& $this->c;
    $u =& $this->u;

    if ($c->shownotice > 0 &&
        ($c->shownotice & $flag) == $flag)
    {
      $container =& $c->getContainerInstance();
      $msg = phpFreeChat::FilterSpecialChar($msg);
      $container->write($recipient, $u->nick, "notice", $msg);
    }
    if ($c->debug) pxlog("/notice ".$msg." (flag=".$flag.")", "chat", $c->getId());
  }
}

?>

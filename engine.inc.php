<?php

  require_once('config.inc.php');

  #####################
  #     LANGUAGE      #
  #####################
  $lang=substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
  if(stream_resolve_include_path("language/$lang.php")) {
    include ("language/$lang.php");
  } else {
    include ("language/fr.php");
  }

  #####################
  #    FUNCTIONS      #
  #####################

  function available() {
    $erg=@exec(SUDO.' '.F2BC.' status');
    if($erg==''){
        return false;
      } else {
        return true;
      }
  }

  function list_jails() {
    global $f2b;
    $jails=array();
    $erg=@exec(SUDO.' '.F2BC.' status | '.GREP.' "Jail list:" | '.AWK.' -F ":" \'{print $2}\' | '.AWK.' \'{$1=$1;print}\'');
    $erg=explode(",",$erg);
    foreach($erg as $jail) {
      $jails[trim($jail)]=false;
    }
    ksort($jails);
    return $jails;
  }

  function jail_info($jail) {
    global $f2b;
    $info=array();
    $erg=@exec(SUDO.' '.F2BC.' get '.escapeshellarg($jail).' findtime ');
    if(is_numeric($erg)) {
      $info['findtime']='findtime: '.$erg;
    }
    $erg=@exec(SUDO.' '.F2BC.' get '.escapeshellarg($jail).' bantime ');
    if(is_numeric($erg)) {
      $info['bantime']='bantime: '.$erg;
    }
    $erg=@exec(SUDO.' '.F2BC.' get '.escapeshellarg($jail).' maxretry ');
    if(is_numeric($erg)) {
      $info['maxretry']='maxretry: '.$erg;
    }
    return $info;
  }

  function list_clients_banned($jail,$usedns) {
    global $f2b;
    $clients_banned=array();
    $erg=@exec(SUDO.' '.F2BC.' status '.$jail.' | '.GREP.' "IP list:" | '.AWK.' -F "list:" \'{print$2}\' | '.AWK.' \'{$1=$1;print}\'');
    if($erg!='') {
      $clients_banned=explode(" ",$erg);
      if($usedns==1) {
        foreach($clients_banned as $client_banned=>$client) {
          $client_dns=gethostbyaddr($client);
          if($client_dns==$client) {
            $client_dns=' ('.$GLOBALS['unknown'].')';
          } else {
            $client_dns=' ('.$client_dns.')';
          }
          $clients_banned[$client_banned].=$client_dns;
        }
      }
      return $clients_banned;
    }
    return false;
  }

  function ban_unban_ip($action,$jail,$ip) {
    if($jail=='') {
      return 'nojailselected';
    } elseif(!filter_var($ip,FILTER_VALIDATE_IP)) {
      return 'ipnotvalid';
    }
    $erg=@exec(SUDO.' '.F2BC.' set '.escapeshellarg($jail).' '.escapeshellarg($action).' '.escapeshellarg($ip));
    if($erg!=1) {
      return 'couldnot';
    }
    return 'OK';
  }

?>

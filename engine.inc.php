<?php

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

  function list_jails() {
    global $f2b;
    $jails=array();
    $erg=@exec('sudo /usr/bin/fail2ban-client status | grep "Jail list:" | awk -F ":" \'{print $2}\' | awk \'{$1=$1;print}\'');
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
    $erg=@exec('sudo /usr/bin/fail2ban-client get '.escapeshellarg($jail).' findtime ');
    if(is_numeric($erg)) {
      $info['findtime']='findtime: '.$erg;
    }
    $erg=@exec('sudo /usr/bin/fail2ban-client get '.escapeshellarg($jail).' bantime ');
    if(is_numeric($erg)) {
      $info['bantime']='bantime: '.$erg;
    }
    $erg=@exec('sudo /usr/bin/fail2ban-client get '.escapeshellarg($jail).' maxretry ');
    if(is_numeric($erg)) {
      $info['maxretry']='maxretry: '.$erg;
    }
    return $info;
  }

  function list_clients_banned($jail,$usedns) {
    global $f2b;
    $clients_banned=array();
    $erg=@exec('sudo /usr/bin/fail2ban-client status '.$jail.' | grep "IP list:" | awk -F "list:" \'{print$2}\' | awk \'{$1=$1;print}\'');
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
    $erg=@exec('sudo /usr/bin/fail2ban-client set '.escapeshellarg($jail).' '.escapeshellarg($action).' '.escapeshellarg($ip));
    if($erg!=1) {
      return 'couldnot';
    }
    return 'OK';
  }

?>

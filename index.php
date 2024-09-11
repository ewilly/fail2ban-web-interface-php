<?php
  $constant='constant';
  require_once('engine.inc.php');

  #####################
  #      ACTIONS      #
  #####################
  if(isset($_POST['submit_reload'])) {
    unset($_POST);
    clearstatcache();
    sleep(1);
    header("Location: ".$_SERVER['REQUEST_URI']);
  }

  if(isset($_POST['submit_settings'])) {
    header("Location: ".$_SERVER['PHP_SELF']."?usedns=".$_POST['usedns']."&jailnoempty=".$_POST['jailnoempty']."&jailinfo=".$_POST['jailinfo']);
    unset($_POST);
    clearstatcache();
    sleep(1);
  }

  if(isset($_POST['submit_add'])) {
    $error_ban=ban_unban_ip("banip",$_POST['ban_jail'],$_POST['ban_ip']);
    if($error_ban!='OK') {
      if($error_ban=='nojailselected') {
        $error_ban='<p class="msg_er">'.$nojailselected.'</p>';
      }
      elseif($error_ban=='ipnotvalid') {
        $error_ban='<p class="msg_er">'.$ipnotvalid.'</p>';
      }
      elseif($error_ban=='couldnot') {
        $error_ban='<p class="msg_er">'.$couldnot.'</p>';
      }
    } else {
      $error_ban='<p class="msg_ok">'.$ipsuccessfullybanned.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }

  if(isset($_POST['submit_del'])) {
    $error_unban=ban_unban_ip("unbanip",$_POST['unban_jail'],$_POST['unban_ip']);
    if($error_unban!='OK') {
      $error_unban='<p class="msg_er">'.$couldnot.'</p>';
    } else {
      $error_unban='<p class="msg_ok">'.$ipsuccessfullyunbanned.'</p>';
      unset($_POST);
      clearstatcache();
      sleep(1);
    }
  }
?>

<!DOCTYPE html>
<html>
  <head>
    <meta meta name="viewport" content="width=device-width, initial-scale=1" charset="utf-8">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css" type="text/css">
    <title>Fail2Ban Webinterface</title>
    <div class="header" id="myHeader">
      <h1>Fail2Ban Webinterface</h1>
      <form name="reload" method="POST">
        <button class="button" type="submit" name="submit_reload"><?=$refresh?>
          <img src="images/reload.svg" alt="add">
        </button>
      </form>
    </div>
    <?php
      $erg2=@exec('sudo /usr/bin/fail2ban-client status');
      if($erg2=='') {
        echo '<h1><p class="msg_er">'.$serviceerror.'</p></h1>';
        exit;
      }
    ?>
  </head>
  <body>
    <h2><?=$bannedclientsperJail?></h2>
    <?php
      $usedns=$_GET['usedns'];
      $jailnoempty=$_GET['jailnoempty'];
      $jailinfo=$_GET['jailinfo'];
      if($usedns==1) {
        $usednsv="checked='checked'";
      } else {
        $usednsv=="";
      }
      if($jailnoempty==1) {
        $jailnoemptyv="checked='checked'";
      } else {
        $jailnoemptyv=="";
      }
      if($jailinfo==1) {
        $jailinfov="checked='checked'";
      } else {
        $jailinfov=="";
      }
    ?>
    <form name="settings" method="post">
      <table>
        <tr>
          <td align="right">
            <label for="usedns"><?=$usedns_txt?></label>
            <br><label for="jailnoempty"><?=$jailnoempty_txt?></label>
            <br><label for="jailinfo"><?=$jailinfo_txt?></label>
          </td>
          <td>
            <input type="checkbox" name="usedns" id="usedns" value="1" <?=$usednsv?>/>
            <br><input type="checkbox" name="jailnoempty" id="jailnoempty" value="1" <?=$jailnoemptyv?>/>
            <br><input type="checkbox" name="jailinfo" id="jailinfo" value="1" <?=$jailinfov?>/>
          </td>
          <td rowspan="3">
            <button class="button" type="submit" name="submit_settings"><?=$apply ?>
              <img src="images/apply.svg" alt="apply" title="<?=$apply ?>">
            </button>
          </td>
        </tr>
      </table>
    </form>
    <?=$error_unban==null?"&nbsp;":$error_unban?>
    <?php
      $jails=list_jails();
      foreach($jails as $jail=>$client_banned) {
        $clients_banned=list_clients_banned($jail,$usedns);
        $jails[$jail]=$clients_banned;
      }
    ?>
    <table>
      <?php
        foreach($jails as $jail=>$clients) {
          if($jailnoempty==1 || is_array($clients)) {
            echo '<thead><tr><td class="bold" colspan="2">'.strtoupper($jail);
            if($jailinfo==1) {
              $jail_info=jail_info($jail);
              $jail_info=implode(', ',$jail_info);
              echo '<span class="msg_gr"> >> '.$jail_info.'</span>';
            }
            echo '</td></tr></thead>';
            if(is_array($clients)) {
              foreach($clients as $client) {
                $client_ip=explode(" (", $client)[0];
                echo '
                  <tr class="highlight">
                    <form name="unban" method="POST">
                      <input type="hidden" name="unban_jail" value="'.$jail.'">
                      <input type="hidden" name="unban_ip" value="'.$client_ip.'">
                      <td align="right">'.$client.'</td>
                      <td align="center">
                        <button class="button" type="submit" name="submit_del">
                          <img src="images/del.svg" alt="del" title="'.$unbanip.' '.$client_ip.'">
                        </button>
                      </td>
                    </form>
                  </tr>
                ';
              }
            } else {
              echo '<tr class="highlight"><td class="msg_gr" colspan="2">'.$nobannedclients.'</td></tr>';
            }
          }
        }
      ?>
    </table>
    <h2><?=$manuallyaddbannedclienttoJail?></h2>
    <?=$error_ban==null?null:$error_ban?>
    <form name="ban" method="POST">
      <table>
        <tr>
          <th>Jail</th>
          <th>IP</th>
          <th><?=$banip?></th>
        </tr>
        <tr class="highlight">
          <td>
            <select name="ban_jail"><option value="">- <?=$select?> -</option>
              <?php
                foreach($jails as $jail=>$clients) {
                  echo '<option value="'.$jail.'"';
                  if($_POST['ban_jail']==$jail) {
                    echo ' selected';
                  }
                  echo '>'.$jail.'</option>';
                }
              ?>
            </select>
          </td>
          <td><input type="text" name="ban_ip" value="<?=$_POST['ban_ip']?>"></td>
          <td align="center">
            <button class="button" type="submit" name="submit_add">
              <img src="images/add.svg" alt="add" title="<?=$banip ?>">
            </button>
          </td>
        </tr>
      </table>
    </form>
    <br>
  </body>
  <footer>
    <hr>
    <?=date("r")?>
  </footer>
</html>

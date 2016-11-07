<?php
ini_set('display_errors','1'); error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

include('config.php');

if (isset($_GET['setHook'])) {
  echo sendApiRequest('setwebhook', array('url' => $botHook));
  exit;
}

$_IN = file_get_contents("php://input");
$update = json_decode($_IN, true);

if (isset($update['message'])) {
  $update = $update['message'];

  if (!empty($update['text']) && $update["chat"]["id"] == "-154398210") { // If something is received in "LLUbot Talks", send it in "LLU" and exit.
    sendMsg("-1001016650503", $update["text"], false);
    exit();
  }

  if ($update['chat']['type'] === "private") {// If the update is not from a group, forward it to "LLUbot Listens" group to be handled there
    forwardMsg("-1001088410143", $update['chat']['id'], $update['message_id']);
  }

  if (isset($update['new_chat_participant'])) { // Welcome new users
    if ($update['new_chat_participant']['id'] == 106784966) {
      sendMsg($update['chat']['id'], "Holap! Vengo a saludar a los nuevos llusers!!!", false, $update['message_id']);
    } else {
      if (isset($update['new_chat_participant']['username'])) {
        sendMsg($update['chat']['id'], "Welcome @" . $update['new_chat_participant']['username'] . " !\nQuieres unirte a la lista de correo de LibreLabUCM?\nhttps://groups.google.com/forum/#!forum/librelabucm/join \n\n\n Chat random: https://telegram.me/joinchat/ACNdBj4tPjEOdqog8pHTCw", false, $update['message_id']);
      } else {
        sendMsg($update['chat']['id'], "Welcome " . $update['new_chat_participant']['first_name'] . " !\nQuieres unirte a la lista de correo de LibreLabUCM?\nhttps://groups.google.com/forum/#!forum/librelabucm/join \n\n\n Chat random: https://telegram.me/joinchat/ACNdBj4tPjEOdqog8pHTCw", false, $update['message_id']);
      }
    }
  } else if (isset($update['left_chat_participant'])) {
    if (isset($update['left_chat_participant']['username'])) {
      sendMsg($update['chat']['id'], "Bye @" . $update['left_chat_participant']['username'] . " :(", false, $update['message_id']);
    } else {
      sendMsg($update['chat']['id'], "Bye " . $update['left_chat_participant']['first_name'] . " :(", false, $update['message_id']);
    }
  }

  // Some commands
  if (!empty($update['text'])) {
    $command = strtolower($update['text']);

    if ($command == "/mailinglist" || $command == "/mailinglist@llubot") {
      sendMsg($update['chat']['id'], "<a href=\"https://groups.google.com/forum/#!forum/librelabucm/join\">Lista de correo</a>", false, $update['message_id']);
    }
    if ($command == "/web" || $command == "/web@llubot") {
      sendMsg($update['chat']['id'], "<a href=\"www.librelabucm.org\">LLu Web</a>", false, $update['message_id']);
    }
    if ($command == "/grupos" || $command == "/grupos@llubot") {
      $textToSend = "<a href=\"https://telegram.me/joinchat/AN341TyY2wfsr32vpSHcSg\">Grupo LLU</a>\n";
      $textToSend .= "<a href=\"https://telegram.me/joinchat/ACNdBj4tPjEOdqog8pHTCw\">Random</a>\n";
      $textToSend .= "\n <a href=\"http://grasia.fdi.ucm.es/librelab/web/?q=node/7\">Comisiones</a>: \n";
      $textToSend .= "   🔅 Servidor\n";
      $textToSend .= "   🔅 Web\n";
      $textToSend .= "   🔅 Comunicación\n";
      $textToSend .= "\n Grupos de trabajo: \n";
      $textToSend .= "   ✔️  <a href=\"https://telegram.me/joinchat/Btutqwglu5cmJFLPG0L6wg\">Rompiendo Hardware</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/joinchat/Apxn5UERfUCmpDAVCkAbdQ\">Install Parties</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/CryptoParty\">CryptoParty</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/joinchat/Apxn5UF37NVSVh2fv2-jIQ\">Telegram Bots</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/joinchat/Ar4agkCACYELE5TZ5AWtAA\">Hacking</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/joinchat/AC_OwEBhVnhFQsd245LBow\">Liberar FDI</a>\n";
      $textToSend .= "   ✔️ <a href=\"https://telegram.me/joinchat/AIpgP0CeKPelWiGDHsOxTQ\">SCI</a>\n";
      $textToSend .= "";

      // $textToSend .= "";
      sendMsg($update['chat']['id'], $textToSend, false, $update['message_id'], true);
    }
    if ($command == "/github" || $command == "/github@llubot") {
      sendMsg($update['chat']['id'], "<a href=\"https://github.com/librelabucm\">Nuestro GitHub!</a>", false, $update['message_id']);
    }
  }


}


function forwardMsg($chat_id, $from_chat_id, $message_id, $disable_notification = true) {
   $r = sendApiRequest('forwardMessage',
         array(
            'chat_id' => $chat_id,
            'from_chat_id' => $from_chat_id,
            'disable_notification' => $disable_notification,
            'message_id' => $message_id
         )
      );
   return json_encode($r, true);
}

function sendMsg($id, $text, $keyboard = null, $reply_to_message_id = "0", $disable_web_page_preview = false) {
   if ($keyboard === null) {
      $r = sendApiRequest('sendMessage',
         array(
            'chat_id' => $id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_to_message_id' => $reply_to_message_id,
            'disable_web_page_preview' => $disable_web_page_preview
         )
      );
   } else if ($keyboard === false){
      $r = sendApiRequest('sendMessage',
         array(
            'chat_id' => $id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => '{"hide_keyboard":true}',
            'reply_to_message_id' => $reply_to_message_id,
            'disable_web_page_preview' => $disable_web_page_preview
         )
      );
   } else {
      $r = sendApiRequest('sendMessage',
         array(
            'chat_id' => $id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => '{"keyboard":'.json_encode($keyboard).',"one_time_keyboard":true}',
            'reply_to_message_id' => $reply_to_message_id,
            'disable_web_page_preview' => $disable_web_page_preview
         )
      );
   }

}

function sendApiRequest($method, $params = array()) {
   $curl = curl_init();
   curl_setopt_array($curl, array(
       CURLOPT_RETURNTRANSFER => 1,
       CURLOPT_URL => 'https://api.telegram.org/bot'. TOKEN . '/' . $method . '?' . http_build_query($params),
       CURLOPT_SSL_VERIFYPEER => false
   ));
   return curl_exec($curl);
}

<?php
/*
Simple Contact Form for Blesta V1.0-alpha

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact us</title>
    <link rel="stylesheet" href="./pbl/chota.min.css">
    <style>
      body.dark {
        --bg-color: #000;
        --bg-secondary-color: #131316;
        --font-color: #f5f5f5;
        --color-grey: #ccc;
        --color-darkGrey: #777;
      }
    </style>
  </head>
  <body>
    <div id="top" class="container" role="document">
      <header role="banner">
        <h1 class="pull-right" style="margin: 0;">
          <a href="javascript:void(0)" onclick="switchMode(this)">☀️</a>
        </h1>
        <h1>Contact us</h1>
        <div class="clearfix"></div>
      </header>
      <main role="main">
        <section id="forms">
          <?php
          if ($debug === true) {
              ini_set("display_errors", 1);
              ini_set("display_startup_errors", 1);
              error_reporting(E_ALL);
          }
          if (!file_exists("config.php")) {
              echo "<div style='color:red; border 1px solid red; padding: 4px;'>Configuration file missing (config.php)!</div>";
              die();
          }
          if ($_POST) {
              session_start();
              if ($_SESSION["secure"] == $_POST["user_input"]) {
                  $vError = "0";
              } else {
                  $vError = "Captcha not valid! Please try again.";
              }
          }
          if ($_POST["textform"]) {
              $myemail = $mymail;
              $ticketmsg = htmlspecialchars(substr(strip_tags($_POST["textform"]), 0, 500));
              $ticketsummary = htmlspecialchars(
                  substr(strip_tags($_POST["summary1"]), 0, 35)
              );
              $clientmail = strip_tags($_POST["mailaddr"]);

              $requiredf = ["textform", "summary1", "mailaddr"];
              foreach ($requiredf as $fieldf) {
                  if (empty($_POST[$fieldf])) {
                      $vError = "All fields are required.";
                  }
              }

              if (!filter_var($clientmail, FILTER_VALIDATE_EMAIL)) {
                  $vError = "Invalid email format";
              } else {
                  $clientmail = filter_var($clientmail, FILTER_SANITIZE_EMAIL);
              }

              if (
                  !checkdnsrr(
                      substr($clientmail, strpos($clientmail, "@") + 1),
                      "MX"
                  )
              ) {
                  $vError = "Invalid email!";
              }

              if ($vError != "0") {
                  echo '<fieldset id="forms__error"><legend style="color:red">ERROR</legend><p>' .
                      $vError .
                      "</p></fieldset>";
              } else {
                  require_once "etc/blesta_api.php";
                  require_once "config.php";

                  $ticket_data = [
                      "vars" => [
                          "department_id" => $department,
                          "staff_id" => 1, // optional, set id of staff
                          "service_id" => null,
                          "client_id" => null,
                          "email" => $clientmail,
                          "summary" => $ticketsummary,
                          "priority" => $priority,
                          "status" => "open",
                      ],
                  ];
                  $api = new BlestaApi($url, $user, $key);
                  $ticket = $api->post(
                      "support_manager.support_manager_tickets",
                      "add",
                      $ticket_data
                  );
                  $zzid = $ticket->response();
                  $reply_data = [
                      "ticket_id" => $zzid,
                      "vars" => [
                          "ticket_id" => $zzid,
                          "staff_id" => null,
                          "client_id" => null,
                          "contact_id" => null,
                          "type" => "reply",
                          "details" => $ticketmsg,
                          "status" => "open",
                          "staff_id" => null,
                      ],
                      "files" => null,
                      "new_ticket" => false,
                  ];

                  $response = $api->post(
                      "support_manager.support_manager_tickets",
                      "addReply",
                      $reply_data
                  );
                  if ($debug === true) {
                      var_dump($zzid);
                      echo "<hr>";
                      var_dump($response);
                  }
                  if ($ticket->response() > 0) {
                      echo "<div style='color:green;'>Thank you! We got your message!<br></div>";
                      mail(
                          $mymail,
                          "Blesta Simple Contact Form",
                          "A new ticket has been created."
                      );
                  } else {
                      echo "<div style='color:red;'>Error submitting the form. Please contact us by e-mail!</div>";
                      mail(
                          $mymail,
                          "Blesta Simple Contact Form",
                          "Ticket creation has been failed! Please check."
                      );
                  }
              }
          }

?>
          <form id="cntct" action="index.php" method="post">
            <fieldset id="forms__input">
              <legend>Contact us</legend>
              <p>
                <label for="input__text">Summary</label>
                <input id="input__text" name="summary1" type="text" placeholder="" maxlength="35" 
                    <?php
                    if(isset($_POST['mailaddr'])) { 
                         echo 'value="' . htmlentities($_POST['summary1']) . '"';
                    }
                  ?>
                   required />
              </p>
              <p>
                <label for="input__emailaddress">Email Address</label>
                <input
                  id="input__emailaddress"
                  type="email"
                  name="mailaddr"
                  <?php
                   if(isset($_POST['mailaddr'])) { 
                         echo 'value="' . htmlentities($_POST['mailaddr']) . '"';
                  } else {
                         echo 'placeholder="name@email.com"';
                  }
                  ?>
                  required
                />
              </p>
            </fieldset>
            <fieldset id="forms__textareas">
              <legend>Details</legend>
              <p>
                <label for="textarea"></label>
                <textarea
                  id="textform"
                  name="textform"
                  rows="8"
                  cols="48"
                  maxlength="500"
                  placeholder="Enter your message here"
                  onkeypress="textareaLengthCheck(this)"
                  required
                 ><?php
                  if(isset($_POST['textform'])) { 
       				  echo htmlentities($_POST['textform']);
                  }
                  ?></textarea><small><p style="color:gray" id="lblRemainingCount"></p></small>
              </p>
            </fieldset>
        <div id="ae_captcha_api"></div><br>
		<div><input type="text"  style="max-width:150px;" placeholder = "Enter Captcha" name="user_input"/></div>
		<br>
            <button class="button primary">Send</button>
          </form>
        </section>
      </main>
    </div>
    <script src="./pbl/main.js"></script>
  </body>
</html>

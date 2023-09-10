document.getElementById('ae_captcha_api').innerHTML = "<div style='float: left ; height : 60px; width:200px; min-width: 170px;' id='divcaptcha'><img src='./captcha-generator/img_gen.php'></div>";

function newcaptcha(){
  var dataString = 'index=1';
  var url = "./captcha-generator/captcha.php"
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      html = this.responseText;
      if (html!=1){
        document.getElementById('divcaptcha').innerHTML = html;
      }
    }
  };
  xhttp.open("POST", url, true);
  xhttp.send();
}

function switchMode(el) {
  const bodyClass = document.body.classList;
  bodyClass.contains('dark')
    ? (el.innerHTML = '☀️', bodyClass.remove('dark'))
    : (el.innerHTML = '🌙', bodyClass.add('dark')); 
}

function textareaLengthCheck(el) {
  var textArea = el.value.length;
  var charactersLeft = 500 - textArea;
  var count = document.getElementById('lblRemainingCount');
  count.innerHTML = "Characters left: " + charactersLeft;
}
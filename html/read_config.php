<?php
session_start();
include_once 'includes/functions.php';
?>

<?php
if (login_check() != true){
    echo ("<p><span class=error_message>You are not authorized to access this page. Please</span> <a href=index.php>login</a>.</p>");
    exit(0);
}
require 'templates/header.html';
?>
<h1>Configuration</h1>

<?php
require_once 'Config/Lite.php';
$config = new Config_Lite('user_config.ini');
$standalone = '';
$gmail = '';
                
if($config['main']['mailer_mode'] == 'standalone'){
    $standalone = 'selected';
}elseif($config['main']['mailer_mode'] == 'gmail'){
    $gmail = 'selected';
}elseif($config['main']['mailer_mode'] == 'cloud'){
    $cloud = 'selected';
}
//check if user has configured FG API to be able use cloud alerting
$fg_intel_key = $config['main']['fg_intel_key'];
if($fg_intel_key == ''){
	$fg_intel_alert = '<p><span class=error_message>You have to subscribe to FalconGate API to be able use Cloud Alerts!<br></span></p>';
}else{
	$fg_intel_alert =  '';
}
?>

<script type='text/javascript'>
hideAll = function() {
	$('.standaloneTextBox').hide();
	$('.gmailTextBox').hide();
	$('.cloudTextBox').hide();
};

hideStuff = function() {
	<?php
	$current_method = $config['main']['mailer_mode'];
	if($current_method == "standalone"){
		echo '$(\'.standaloneTextBox\').show();';
		echo '$(\'.gmailTextBox\').hide();';
		echo '$(\'.cloudTextBox\').hide();';
		$required_c = "";
		$required_g = "";
	}elseif($current_method == "cloud"){
		echo '$(\'.standaloneTextBox\').hide();';
		echo '$(\'.gmailTextBox\').hide();';
		echo '$(\'.cloudTextBox\').show();';
		$required_c = "required";
		$required_g = "";
	}elseif($current_method == "gmail"){
		echo '$(\'.standaloneTextBox\').hide();';
		echo '$(\'.gmailTextBox\').show();';
		echo '$(\'.cloudTextBox\').hide();';
		$required_c = "";
		$required_g = "required";
	}else{
		echo 'hideAll();';
	}
	?>
};

$(document).ready(function (e) {
	hideStuff();
	
    $('#alertsDropDown').change(function () {
        if ($(this).val() == 'cloud') {
            $('.cloudTextBox').show();
			$('.gmailTextBox').hide();
			$('.standaloneTextBox').hide();
        } else if ($(this).val() == 'gmail') {
            $('.gmailTextBox').show();
			$('.cloudTextBox').hide();
			$('.standaloneTextBox').hide();	
        } else if ($(this).val() == 'standalone') {
            $('.standaloneTextBox').show();
			$('.gmailTextBox').hide();
			$('.cloudTextBox').hide();
        }
    });
});

function torAlert() {
    if (document.getElementById('allow_tor').checked) {
        alert("Sure you want to allow Tor in your network? Malware can use Tor to hide its traffic!");
    }
}

function cloudScanAlert() {
    var key = document.getElementById('fg_intel_key').value;
    if(key.replace(/\s/g,"") == ""){
        if (document.getElementById('enable_cloud_scan').checked == true){
            alert("To enable this option you must first configure your FalconGate API key!");
            document.getElementById('enable_cloud_scan').checked = false;
            return false;
        }else{
            return true;
        }
    }
    if (document.getElementById('enable_cloud_scan').checked == false) {
        return true;
      } else {
       var box= confirm("By selecting this option, you agree with our Terms of Service and that you have read our Privacy Policy");
        if (box==true)
            return true;
        else
           document.getElementById('enable_cloud_scan').checked = false;

      }
}

</script>

<!-- Function to display hints to the users in alerts delivery configuration -->
<script>
$(document).ready(function () {
    $('.para').hide();
    $(".button").click(function () {
        $('.para').hide();
        $('#' + $(this).data('id')).show();
    });
});
</script>

<?php
    if ($config['main']['cloud_malware_sandbox'] == 'true'){
        $cloud_scan_value = "value=1 checked=checked";
    }else{
        $cloud_scan_value = "value=0";
    }
    if ($config['main']['allow_tor'] == 'true'){
        $tor_value = "value=1 checked=checked";
    }else{
        $tor_value = "value=0";
    }
    echo ('<form name="user_config" id="user_config" action="save_config.php" onsubmit="return ValidateInput();" method="post">
           <table width=95% halign=left>
           <tr align=left><td title="Your personal FalconGate Intel API key.">FalconGate Intel API key:</td><td><input type=text size=71 name="fg_intel_key" value='.$config['main']['fg_intel_key'].'></td></tr>
           <tr align=left><td title="Your personal VirusTotal API key.">VirusTotal API key:</td><td><input type=text size=71 name="vt_key" value='.$config['main']['vt_api_key'].'></td></tr>
           <tr align=left><td title="This is the list of recipients for the email alerts sent by FalconGate.">Alert recipients:</td><td><input type=text size=71 name="dst_emails" value='.$config['main']['dst_emails'].'></td></tr>
           <tr align=left><td title="This is the customized list of IP addresses and domains you wish to block.">Blacklist:</td><td><textarea form="user_config" id="blacklist" name="blacklist" rows=5 cols=81>'.$config['main']['blacklist'].''.$config['main']['domain_blacklist'].'</textarea></td></tr>
           <tr align=left><td title="This is the list of IP addresses and domains to be whitelisted from blocking by FalconGuard.">Whitelist:</td><td><textarea form="user_config" id="whitelist" name="whitelist" rows=5 cols=81>'.$config['main']['whitelist'].''.$config['main']['domain_whitelist'].'</textarea></td></tr>
           <tr align=left><td title="This is the list of email addresses to be monitored for potential compromise due to hacking breaches in third party online services.">Email watchlist:</td><td><textarea form="user_config" id="email_watchlist" name="email_watchlist" rows=5 cols=81>'.$config['main']['email_watchlist'].'</textarea></td></tr>
           <tr align=left><td title="Enable cloud based Malware scan of suspicious executables." colspan=2>Enable cloud based Malware scan <input type=checkbox name=enable_cloud_scan id=enable_cloud_scan value='.$cloud_scan_value.' onchange="cloudScanAlert()"</td></tr>
           <tr align=left><td title="Allow traffic towards the Tor network." colspan=2>Allow Tor <input type=checkbox name=allow_tor id=allow_tor value='.$tor_value.' onchange="torAlert()"</td></tr>
           </table>           
           <br>');
echo ('<p class=notes>Note: Multiple recipient emails can be added to the "Alerts recipients" field using commas as separator.</p>');
    echo ("<p><i><b>Select below your preferred option for alerting (default = Standalone):</b></i></p>");
    echo ('<table>
           <td>
		   <select id="alertsDropDown" name="selector">
				<option value="empty" id="empty">Select delivery method</option>
				<option value="cloud" id="cloud" '.$cloud.'>Cloud Alerts</option>
				<option value="gmail" id="gmail" '.$gmail.'>Gmail</option>
				<option value="standalone" id="standalone" '.$standalone.'>Standalone</option>
			</select>
			<br><br>
           </td>
           </table>
           <table width=65% halign=left>
           <tr align=left>
			<td>				
				<div class="cloudTextBox">
					'.$fg_intel_alert.'
					<table>
						<tr>
							<td width="300">
								Telegram Chat ID: <input type=text size=20 name="telegram_id" id="telegram_id" '.$required_c.' value='.$config['main']['telegram_id'].'>
							</td>
							<td align="left">
								<div class=button data-id=text1><div class=button float_1><a href=#><img src="images/hint2_s.png" title="Click for hint"></a></div></div>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="para" id="text1">For more information how to obtain your Telegram ID, please follow <a target="_blank" href="https://github.com/A3sal0n/FalconGate/wiki/Get-Telegram-Chat-ID">this link</a>.</div>
							</td>
						</tr>
					</table>
				</div>	
				<div class="gmailTextBox">
					Gmail Address: <input type=text size=20 name="mailer_address" id="mailer_address" '.$required_g.' value='.$config['main']['mailer_address'].'><br>
					Password: <input type="password" name="mailer_pwd" id="mailer_pwd" size="20" maxlength="500">
				</div>
				<div class="standaloneTextBox">Standalone</div>
			</td>
			</tr>
           </table>
		   <br>
           <input class="config_button" type="submit" value="Save" name="mailer_submit" id="mailer_submit">
           </form>');
if (isset($_GET['updated'])){
    if ($_GET['updated'] == 'True'){
        echo ('<p>Configuration saved!</p>
        <p>FalconGate process restarted...</p>');
    }
}
?>
 
<?php
require 'templates/footer.html';
?>
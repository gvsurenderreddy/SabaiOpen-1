<?php $proxystatus = exec("uci get sabai.proxy.status"); ?>
<form id="fe">
<input type='hidden' id='act' name='act'>
<div class='pageTitle'>Settings</div>
<div class='controlBox'><span class='controlBoxTitle'>Proxy</span>
	<div class='controlBoxContent'>
			<table class='fields'>
				<tr>
					<td class='title'>Proxy Status</td><td><div id='proxy'><?php echo exec("uci get sabai.proxy.status"); ?></div></td>
				</tr>
			</table>
			<input type='button' id='proxyStart' class='firstButton'value='Start' onclick='proxysave("proxystart")'>
			<input type='button' id='proxyStop' value='Stop' onclick='proxysave("proxystop")'>
		</div>
	</div>

<div class='controlBox'><span class='controlBoxTitle'>Power</span>
	<div class='controlBoxContent'>
			<input type='button' name='power' id='power' value='Off' class='firstButton' onclick='system("halt")'>
			<input type='button' name='restart' id='restart' value='Restart' onclick='system("reboot")'>
		</div>
	</div>

<div class='controlBox'><span class='controlBoxTitle'>Password</span>
	<div class='controlBoxContent'>
		<table class='fields'>
			<tr>
				<td class='title'>New Password</td>
				<td><input type='password' name = 'sabaiPassword' id='sabaiPassword'></td>
			</tr>
			<tr>
				<td class='title'>Confirm Password </td>
				<td class='title'><input type='password' name='sabaiPWConfirm' id='sabaiPWConfirm'></td>
			</tr>
		</table>
		<input type='button' id='passUpdate' class='firstButton' onclick='pass("updatepass")' value='Update' />
		<div id='saveError'> Passwords must match.</div>
	</div>
	</div>
	<br><b>
	<span id='messages'>&nbsp;</span></b>
	<pre class='noshow' id='response'></pre>
</form>
</td>
</tr>
</table>
<div id='footer'> Copyright © 2014 Sabai Technology, LLC </div>
    <div id='hideme'>
        <div class='centercolumncontainer'>
            <div class='middlecontainer'>
                <div id='hiddentext'>Please wait...</div>
                <br>
            </div>
        </div>
    </div>
<script type="text/javascript">
var f = E('fe'); 
var hidden = E('hideme'); 
var hide = E('hiddentext');
var settingsWindow, oldip='',limit=10,info=null,ini=false;
init();

$(document).ready(
            function() {
                setInterval(function() {
                    que.drop('php/info.php',setUpdate);
                    $("#proxy").text(info.proxy.status);
                }, 3000);
                   });

	function Settingsresp(res){ 
		eval(res); 
		msg(res.msg); 
		showUi(); 
	}

	function proxysave(act){ 
		hideUi("Adjusting Proxy..."); 
		E("act").value=act;  
		$.post("php/proxy.php", $("#fe").serialize(), function(res){
		// Detect if values have been passed back
    		if(res!=""){
      		Settingsresp(res);
    		};
      		showUi();
		});
	}

	function system(act){ 
		hideUi("Processing Request..."); 
		E("act").value=act;
		$.post('php/settings.php', $("#fe").serialize(), function(res){
				// Detect if values have been passed back   
    			if(res!=""){
      			Settingsresp(res);
    			};
      		showUi();
			});
		setTimeout("window.location.reload()",60000);
	}

	function pass(act){ 
		if ( $('#sabaiPassword').val() == $('#sabaiPWConfirm').val() ) {
			hideUi("Updating Credentials..."); 
			E("act").value=act;
			$.post('php/settings.php', $("#fe").serialize(), function(res){
				// Detect if values have been passed back   
    			if(res!=""){
      			Settingsresp(res);
    			};
      		showUi();
			});
			$('#saveError').hide();
		} else {
			$('#saveError').css('display', 'inline-block').css("color","#262262").css("font-weight","bold");
			}
		}



	</script>
	
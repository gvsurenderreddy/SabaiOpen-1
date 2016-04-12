<!--  TODO:
WAN PPPoE { username, password, options, mode/interval } and IPv6
DDNS: { ip, interval, services }
-->
<form id="fe">
<input type='hidden' id='act' name='act'>
<div class='pageTitle'>Network: WAN</div>
<div class='controlBox'><span class='controlBoxTitle'>WAN</span>
  <!-- this div gets populated by widget -->
  <div class='controlBoxContent' id='wansetup'>
  </div>
</div>

<div class='controlBox'>
  <span class='controlBoxTitle'>DNS</span>
  <div class='controlBoxContent'>
    <table class='controlTable'>
      <tbody>
      <tr>
        <td>DNS Servers</td>
        <td><div><ul id='dns_servers'></ul></div></td>
          <div id="editableListDescription">
            <span class ='xsmallText'>(These are the DNS servers the DHCP server will provide for devices also on the LAN)
            </span><br><br>
          </div>
        </td>
      </tr>
      </tbody>
    </table>
  </div>
</div>
<input type='button' value='Save' onclick='WANcall("save")'><span id='messages'>&nbsp;</span>
    <div id='hideme'>
        <div class='centercolumncontainer'>
            <div class='middlecontainer'>
                <div id='hiddentext'>Please wait...</div>
                <br>
            </div>
        </div>
    </div>
    </div>
    <p>
        <div id='footer'>Copyright © 2016 Sabai Technology, LLC</div>
</p>
</form>
<script>


var hidden, hide, pForm = {};

var f = E('fe'); 
var hidden = E('hideme'); 
var hide = E('hiddentext');

var wan=$.parseJSON('{<?php
          $proto=exec("uci get sabai.wan.proto");
          $ip=trim(exec("uci get sabai.wan.ipaddr"));
          $mask=trim(exec("uci get sabai.wan.netmask"));
          $gateway=trim(exec("uci get sabai.wan.gateway"));
          if (exec("uci show network | grep macaddr") != ""){
                $mac=trim(exec("uci get sabai.wan.mac"));
          } else {
            $mac=trim(exec("ifconfig eth0 | awk '/HWaddr/ { print $5 }'"));
          }
          $mtu=trim(exec("uci get sabai.wan.mtu"));
        echo "\"proto\": \"$proto\",\"ip\": \"$ip\",\"mask\": \"$mask\",\"gateway\": \"$gateway\",\"mac\": \"$mac\",\"mtu\": \"$mtu\"";
      ?>}');
var dnsraw='<?php
          $servers=exec("uci get sabai.wan.dns");
          echo "$servers"; 
          ?>';
var array = JSON.stringify(dnsraw.split(" "));
var dnsfin= "{\"servers\"" + ":" + array + "}";
var dns = $.parseJSON(dnsfin);
 
function WANcall(act){ 
  hideUi("Adjusting WAN settings..."); 
E("act").value=act;
$(document).ready( function(){
// Pass the form values to the php file 
$.post('php/wan.php', $("#fe").serialize(), function(res){
  // Detect if values have been passed back   
    if(res!=""){
      WANresp(res);
    }
      showUi();
});
 
// Important stops the page refreshing
return false;

}); 


    if(act =='clear'){ 
    setTimeout("window.location.reload()",5000);
      }; 
}

function WANresp(res){ 
  eval(res); 
  msg(res.msg); 
  showUi(); 
  if(res.sabai){ 
    limit=10; 
    getUpdate(); 
  } 
}

function spinnerConstraint(spinner){
  var curv = $(spinner).ipspinner('value');
  if( curv < $(spinner).ipspinner('option','min') ) 
    $(spinner).ipspinner('value', $(spinner).ipspinner('option','min') );
  else if( curv > $(spinner).ipspinner('option','max') ) 
    $(spinner).ipspinner('value', $(spinner).ipspinner('option','max') );
}

$.widget("jai.wansetup", {
    
  //Adding to the built-in widget constructor method - do this when widget is instantiated
  _create: function(){
    //TO DO: check to see if containing element has a unique id
    
    // BUILDING DOM ELEMENTS
    $(this.element)
    .append( $(document.createElement('table')).addClass("controlTable")
      .append( $(document.createElement('tbody')) 
        
        .append( $(document.createElement('tr'))
          .append( $(document.createElement('td')).html('WAN proto') 
          )
          .append( $(document.createElement('td') ) 
            .append(
              $(document.createElement('select'))
                .prop("id","wan_proto")
                .prop("name","wan_proto")
                .prop("class", "radioSwitchElement")
              .append( $(document.createElement('option'))
                .prop("value", "dhcp")
                .prop("text", 'DHCP')
              )
            )

          )
        ) // end proto tr
      ) // end first tbody
      .append( $(document.createElement('tbody')).addClass("wan_proto wan_proto-static") 
        .append( $(document.createElement('tr') )
          .append( $(document.createElement('td')).html('IP') )
          .append( $(document.createElement('td') )
            .append(
              $(document.createElement('input'))
                .prop("id","wan_ip")
                .prop("name","wan_ip")
                .prop("type","text")
            )
          )
        ) // end ip row
        .append( $(document.createElement('tr') )
          .append( $(document.createElement('td')).html('Network Mask') )
          .append( $(document.createElement('td') )
            .append(
              $(document.createElement('input'))
                .prop("id","wan_mask")
                .prop("name","wan_mask")
                .prop("type","text")
            )
          )
        ) // end nmask row
        .append( $(document.createElement('tr') )
          .append( $(document.createElement('td')).html('Gateway') )
          .append( $(document.createElement('td') )
            .append(
              $(document.createElement('input'))
                .prop("id","wan_gateway")
                .prop("name","wan_gateway")
                .prop("type","text")
            )
          )
        ) // end gateway row
      ) // end 2nd table body
      .append( $(document.createElement('tbody')) 
        .append( $(document.createElement('tr') )
          .append( $(document.createElement('td')).html('MTU') )
          .append( $(document.createElement('td') )
            .append(
              $(document.createElement('input'))
                .prop("id","wan_mtu")
                .prop("name","wan_mtu")
                .prop("type","text")
            )
          )
        ) //end MTU row
        .append( $(document.createElement('tr') )
          .append( $(document.createElement('td')).html('MAC') )
          .append( $(document.createElement('td') )
            .append(
              $(document.createElement('input'))
                .prop("id","wan_mac")
                .prop("name","wan_mac")
                .prop("type","text")
            )
          )
        ) //end Mac row
      ) // end bottom table body
    ) // end table

    // call ipspinner widget
    $('#wan_ip').ipspinner({
      min: '0.0.0.1', max: '255.255.255.254',
      page: Math.pow(2,(32-mask2cidr(this.options.conf.mask))),
      change: function(event,ui){ 
        spinnerConstraint(this);
      }
    }).ipspinner('value',this.options.conf.ip);

    // call maskspinner widget
    $('#wan_mask').maskspinner({
      spin: function(event,ui){ 
        $('#wan_ip').ipspinner('option','page', Math.pow(2,(32-ui.value)) ) 
      }
    }).maskspinner('value',this.options.conf.mask);


    $('#wan_mac').macspinner().macspinner('value',wan.mac);
    $('#wan_mtu').spinner({ min: 576, max: 1500 }).spinner('value',wan.mtu);
    $('#wan_gateway').ipspinner().ipspinner('value',wan.gateway);
    $('#wan_mask').maskspinner().maskspinner('value',wan.mask);
    $('#wan_ip').ipspinner().ipspinner('value',wan.ip);
    $('#wan_proto').radioswitch({ value: wan.proto, hasChildren: true });
    
    this._super();
  },
});

$(function(){
  //instatiate widgets on document ready
  $('#wansetup').wansetup({ conf: wan });
  $('#dns_servers').oldeditablelist({ list: dns.servers, fixed: false })
})



</script>

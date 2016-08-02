#!/bin/ash
# Sabai Technology - Apache v2 licence
# Copyright 2016 Sabai Technology
# Creates a json file of wan info and dhcp leases

#turn on tor in specific mode
mode=$1

#path to config files
UCI_PATH=""
config_file=sabai
proto=$(uci get sabai.vpn.proto)
device=$(uci get system.@system[0].hostname)
mode_curr=$(uci get sabai.tor.mode)
tor_stat="$(netstat -lnt | awk '$6 == "LISTEN" && $4 ~ ".9040"')"

_return(){
	echo "res={ sabai: $1, msg: '$2' };"
	exit 0;
}

_off(){
	if [ ! "$tor_stat" ]; then
		logger "NO TOR is running."
		_return 0 "NO TOR is running."
	fi

	/etc/init.d/tor stop

	uci delete privoxy.privoxy.forward_socks5t
	uci delete privoxy.privoxy.forward_socks5
	uci delete privoxy.privoxy.forward_socks4
	uci delete privoxy.privoxy.forward_socks4a
	uci delete privoxy.privoxy.forward
	uci commit privoxy
	/etc/init.d/privoxy restart

  if [ $(uci $UCI_PATH get sabai.tor.mode) = "tun" ]; then
		uci $UCI_PATH set sabai.vpn.proto="none"
		uci $UCI_PATH set sabai.vpn.status="none"
    sed -ni "/iptables -t nat -A PREROUTING ! -d .*\/.* -p .* -j REDIRECT --to-ports/!p" /etc/firewall.user
  fi

	uci $UCI_PATH set sabai.tor.mode="off"
	uci $UCI_PATH commit sabai
	cp -r /etc/config/sabai /configs/
	# must be after sabai changing
	/etc/init.d/firewall restart

	logger "TOR turned OFF."
	_return 0 "TOR turned OFF."
}

_common_settings(){
	if [ "$device" = "vpna" ]; then
		ipaddr=$(uci get network.wan.ipaddr)
	else
		ipaddr=$(uci get network.lan.ipaddr)
	fi

	# Tor's TransPort
	_trans_port="9040"

	# Privoxy port
	_privox_port="8080"

	# Tor's ProxyPort
	_tor_proxy_port="9050"

	echo "# SABAI TOR CONFIG" > /etc/tor/torrc
	echo "SocksPort $_tor_proxy_port" >> /etc/tor/torrc
	echo "SocksPolicy accept *" >> /etc/tor/torrc

	echo -e "\n" >> /etc/tor/torrc
	echo "RunAsDaemon 1" >> /etc/tor/torrc
	echo "DataDirectory /var/lib/tor" >> /etc/tor/torrc

	echo -e "\n" >> /etc/tor/torrc
	echo "CircuitBuildTimeout 30" >> /etc/tor/torrc
	echo "KeepAlivePeriod 60" >> /etc/tor/torrc
	echo "NewCircuitPeriod 15" >> /etc/tor/torrc
	echo "NumEntryGuards 8" >> /etc/tor/torrc
	echo "ConstrainedSockets 1" >> /etc/tor/torrc
	echo "ConstrainedSockSize 8192" >> /etc/tor/torrc
	echo "AvoidDiskWrites 1" >> /etc/tor/torrc

	echo -e "\n" >> /etc/tor/torrc
	echo "User tor" >> /etc/tor/torrc

	echo -e "\n" >> /etc/tor/torrc
	echo "VirtualAddrNetwork $(uci get $config_file.tor.network)/10" >> /etc/tor/torrc
	echo "AutomapHostsOnResolve 1" >> /etc/tor/torrc
	echo "TransPort $_trans_port" >> /etc/tor/torrc
	echo "DNSPort 9053" >> /etc/tor/torrc

	_forward_socks="/	127.0.0.1:9050	."
	uci set privoxy.privoxy.listen_address=":$_privox_port"
	uci set privoxy.privoxy.forward_socks5t="$_forward_socks"
	uci set privoxy.privoxy.forward_socks5="$_forward_socks"
	uci set privoxy.privoxy.forward_socks4="$_forward_socks"
	uci set privoxy.privoxy.forward_socks4a="$_forward_socks"
	uci add_list privoxy.privoxy.forward="192.168.*.*/	."
	uci add_list privoxy.privoxy.forward="10.*.*.*/	."
	uci add_list privoxy.privoxy.forward="127.*.*.*/	."
	uci add_list privoxy.privoxy.forward="localhost/     ."
	uci commit privoxy
	#	/etc/init.d/privoxy restart
	/www/bin/proxy.sh proxystop
	/www/bin/proxy.sh proxystart
}

_tun() {
	_check_vpn

	uci $UCI_PATH set sabai.tor.mode=$mode
	uci $UCI_PATH set sabai.vpn.proto="tor"
	uci $UCI_PATH set sabai.vpn.status="Anonymity"
	uci $UCI_PATH commit sabai
	cp -r /etc/config/sabai /configs/

	/etc/init.d/tor stop

  _common_settings

	if [ "$device" = "vpna" ]; then
		local net=$(ip addr show | grep eth0: -A 3 | grep inet | awk '{print $2}')
	else
		local net=$(ip addr show | grep br-lan: -A 3 | grep inet | awk '{print $2}')
	fi

  iptables -t nat -A PREROUTING ! -d "$net" -p udp --dport 53 -j REDIRECT --to-ports 9053
  iptables -t nat -A PREROUTING ! -d "$net" -p tcp --dport 53 -j REDIRECT --to-ports 9053
  iptables -t nat -A PREROUTING ! -d "$net" -p tcp --syn -j REDIRECT --to-ports 9040
  echo "iptables -t nat -A PREROUTING ! -d "$net" -p udp --dport 53 -j REDIRECT --to-ports 9053" >> /etc/firewall.user
	echo "iptables -t nat -A PREROUTING ! -d "$net" -p tcp --dport 53 -j REDIRECT --to-ports 9053" >> /etc/firewall.user
	echo "iptables -t nat -A PREROUTING ! -d "$net" -p tcp --syn -j REDIRECT --to-ports 9040" >> /etc/firewall.user

	/etc/init.d/tor start

	logger "TOR tunnel started."
	logger "ALL traffic will be anonymized; HTTP proxy is also available on port 8080."
	_return 0 "Tor tunnel started."
}

_proxy(){
	uci $UCI_PATH set sabai.tor.mode=$mode
	uci $UCI_PATH commit sabai
	cp -r /etc/config/sabai /configs/

	/etc/init.d/tor stop

  _common_settings

	/etc/init.d/tor start
	logger "TOR proxy started."
	logger "Anonymizing HTTP proxy is available on port 8080."
	_return 0 "Tor proxy started."
}

_check_vpn() {
    ifconfig > /tmp/check
    if [ "$(cat /tmp/check | grep pptp)" ]; then
        /www/bin/pptp.sh stop
    elif [ "$(cat /tmp/check | grep tun)" ]; then
        /www/bin/ovpn.sh stop
    else
        logger "No VPN is running."
    fi
}

_check() {
	if [ "$tor_stat" ]; then
		_return 0 "TOR is running."
	else
		logger "TOR is not running."
	fi
}

case $mode in
	off)	_off	;;
	proxy)	_proxy	;;
	tun)	_tun	;;
	stat)	_check ;;
esac

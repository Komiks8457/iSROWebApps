<?php
/*
To deploy this on a php server like lamp, xampp, wamp, bitnami, etc..
you need to enable urlrewrite configure .htaccess

RewriteEngine On
RewriteBase /
RewriteRule ^(.*)\.asp$ /index.php?req=$1 [QSA,PT,L]

*/
$_config['mssql']=[
	// MSSQL HOST or PORT
	"ip"=>".//",
	// MSSQL userid and password
	// For Trusted_Connection leave this null and add IUSR user in MSSQL (*for iis)
	"id"=>null,
	"pw"=>null,
	// Account database for silkroad
	"db"=>"SILKROAD_R_ACCOUNT",
	// The GB_JoymaxPortal database used by joymax
	"portal_db"=>"GB_JoymaxPortal"
];
$_config['site_info']=[
	// Set true if you are using HTTPS
	"ssl"=>false,
	// Your main domain where your webmall sits
	"domain"=>"webmall.cfxdb.com",
	// Site basic info
	"title"=>"Silkroad Online",
	"description"=>"The most updated Silkroad server with the newest files available is ready to bring this game to the next level. The future is finally here!",
	"keyword"=>"silkroad, silkroadonline, joymax, onlinesilkroad, silkroad-online",
	// This key is used to encrypt/decrypt the webmalltoken cookie.
	"secret"=>"sanay.di.nalang.kit.ana.kilala",
	// Server name will show in Reserved, Buying Item and History
	"servername"=>"TEST",
	// The url used to open when Recharge Silk is click in Buy Item page
	"chargeurl"=>"http://yourdomain.com/reloadsilk.asp",
	// Extension used in browsing your page.
	// eg. http://yourdomain.com/gateway.asp.. It can be whatever you want
	"extension"=>".asp",
	// SALTKEY, if you change this you need to change it also in GlobalManager.exe
	"vkey"=>"eset5ag.nsy-g6ky5.mp",
	// Since this project has repo in github why not use github as img cdn, this is temporary :D
	"cdn"=>"https://raw.githubusercontent.com/Komiks8457/iSROWebApps/main/webmall/webmall_php/",
	// The directory where you put the webmall, leave / if its in root dir
	// if you intend to use sub-directories you should start it with / and ends with /
	// and also edit web.config url="/index.php?req={R:1}" to your subdirectory url="/subdir/index.php?req={R:1}"
	"rootdir"=>"/"
];
// VIP Settings
$_config['vipinfo']=[
	// VIP minimum level to show the VIP category
	"level_access"=>4, //joymax default
	// VIP level names will show in webmall
	"level"=>[
		0=>"Normal",
		1=>"Iron",
		2=>"Bronze",
		3=>"Silver",
		4=>"Gold",
		5=>"Platinum",
		6=>"VIP"
	],
	// VIP Types
	"type"=>[
		0=>"General",
		1=>"VIP",
		2=>"New",
		3=>"Returne",
		4=>"Free"
	]
];
// Default pages
$_config['pages']=[
	'itembuygame',
	'gateway',
	'error'
];
// String shown in the entire webmall
$_config['message']=[
	//its all english in default, only us,eg,tr,es,de language is accepted atm.
	"us"=>[
		"Item Mall",
		"Premium Mall",
		"Silk Mall",
		"Reserverd",[
			"Item Name",
			"Server",
			"Qty",
			"Price",
			"Purchase",
			"Delete",
			"Delete All",
			"Purchase Selected",
			"Purchase All",
			"No Data"
		],
		"History",[
			"Year",
			"Month",
			"Total silks spent",
			"Total Purchase in Premium Mall (a)",
			"Total Purchase in Silk Mall (b)",
			"Total Purchase of Silks (a)+(b)",
			"* Silk received as gifts are spent first when purchasing items in game.",
			"Buy Item History",
			"Date",
			"Item",
			"Server",
			"Price",
			"Complete Receiving Item(s) In-Game",
			"No history available"
		],
		"Buy Item",[
			"Item Name",
			"ID",
			"Server",
			"Qty",
			"Price",
			"Available Balance",
			"Click the [Silk Charge] button on the top right corner to buy more credits.",
			"Item Successfully Purchased",
			"1) Start the game and enter the server you chose when you bought the item.",
			"2) Enter the game with the character that will receive the item.",
			"3) If you right-click on the icon above in the game, the purchased item list will appear. When you click [get] from the list, the selected item will be moved to the inventory.",
			"Please view purchase information.",
			"Limited-time items can only be used until their expiration date. Check the date.",
			"Interner error occur while purchasing",
			"Report this to the administrator asap.",
			"* The item will be given to the character on the selected server. Please be careful in selecting the server.",
			"Purchase",
			"Confirm",
			"Cancel",
			"Back",
			"History",
			"<font color=\"#fff200\">%s</font> is limited to %d purchase per month.",
			"You cannot buy this item more than it's monthly limit. Please try again.",
			"Monthly limit purchase for this item has been reached."
		],
		"Popular Items",
		"Search",
		"Purchase",
		"Buy Item Guide",
		"Search Result"
	],
	"es"=>[
		"Item Mall",
		"Premium Mall",
		"Silk Mall",
		"Reserverd",[
			"Item Name",
			"Server",
			"Qty",
			"Price",
			"Purchase",
			"Delete",
			"Delete All",
			"Purchase Selected",
			"Purchase All",
			"No Data"
		],
		"History",[
			"Year",
			"Month",
			"Total silks spent",
			"Total Purchase in Premium Mall (a)",
			"Total Purchase in Silk Mall (b)",
			"Total Purchase of Silks (a)+(b)",
			"* Silk received as gifts are spent first when purchasing items in game.",
			"Buy Item History",
			"Date",
			"Item",
			"Server",
			"Price",
			"Complete Receiving Item(s) In-Game",
			"No history available"
		],
		"Buy Item",[
			"Item Name",
			"ID",
			"Server",
			"Qty",
			"Price",
			"Available Balance",
			"Click the [Silk Charge] button on the top right corner to buy more credits.",
			"Item Successfully Purchased",
			"1) Start the game and enter the server you chose when you bought the item.",
			"2) Enter the game with the character that will receive the item.",
			"3) If you right-click on the icon above in the game, the purchased item list will appear. When you click [get] from the list, the selected item will be moved to the inventory.",
			"Please view purchase information.",
			"Limited-time items can only be used until their expiration date. Check the date.",
			"Interner error occur while purchasing",
			"Report this to the administrator asap.",
			"* The item will be given to the character on the selected server. Please be careful in selecting the server.",
			"Purchase",
			"Confirm",
			"Cancel",
			"Back",
			"History",
			"<font color=\"#fff200\">%s</font> is limited to %d purchase per month.",
			"You cannot buy this item more than it's monthly limit. Please try again.",
			"Monthly limit purchase for this item has been reached."
		],
		"Popular Items",
		"Search",
		"Purchase",
		"Buy Item Guide",
		"Search Result"
	],
	"de"=>[
		"Item Mall",
		"Premium Mall",
		"Silk Mall",
		"Reserverd",[
			"Item Name",
			"Server",
			"Qty",
			"Price",
			"Purchase",
			"Delete",
			"Delete All",
			"Purchase Selected",
			"Purchase All",
			"No Data"
		],
		"History",[
			"Year",
			"Month",
			"Total silks spent",
			"Total Purchase in Premium Mall (a)",
			"Total Purchase in Silk Mall (b)",
			"Total Purchase of Silks (a)+(b)",
			"* Silk received as gifts are spent first when purchasing items in game.",
			"Buy Item History",
			"Date",
			"Item",
			"Server",
			"Price",
			"Complete Receiving Item(s) In-Game",
			"No history available"
		],
		"Buy Item",[
			"Item Name",
			"ID",
			"Server",
			"Qty",
			"Price",
			"Available Balance",
			"Click the [Silk Charge] button on the top right corner to buy more credits.",
			"Item Successfully Purchased",
			"1) Start the game and enter the server you chose when you bought the item.",
			"2) Enter the game with the character that will receive the item.",
			"3) If you right-click on the icon above in the game, the purchased item list will appear. When you click [get] from the list, the selected item will be moved to the inventory.",
			"Please view purchase information.",
			"Limited-time items can only be used until their expiration date. Check the date.",
			"Interner error occur while purchasing",
			"Report this to the administrator asap.",
			"* The item will be given to the character on the selected server. Please be careful in selecting the server.",
			"Purchase",
			"Confirm",
			"Cancel",
			"Back",
			"History",
			"<font color=\"#fff200\">%s</font> is limited to %d purchase per month.",
			"You cannot buy this item more than it's monthly limit. Please try again.",
			"Monthly limit purchase for this item has been reached."
		],
		"Popular Items",
		"Search",
		"Purchase",
		"Buy Item Guide",
		"Search Result"
	],
	"tr"=>[
		"Item Mall",
		"Premium Mall",
		"Silk Mall",
		"Reserverd",[
			"Item Name",
			"Server",
			"Qty",
			"Price",
			"Purchase",
			"Delete",
			"Delete All",
			"Purchase Selected",
			"Purchase All",
			"No Data"
		],
		"History",[
			"Year",
			"Month",
			"Total silks spent",
			"Total Purchase in Premium Mall (a)",
			"Total Purchase in Silk Mall (b)",
			"Total Purchase of Silks (a)+(b)",
			"* Silk received as gifts are spent first when purchasing items in game.",
			"Buy Item History",
			"Date",
			"Item",
			"Server",
			"Price",
			"Complete Receiving Item(s) In-Game",
			"No history available"
		],
		"Buy Item",[
			"Item Name",
			"ID",
			"Server",
			"Qty",
			"Price",
			"Available Balance",
			"Click the [Silk Charge] button on the top right corner to buy more credits.",
			"Item Successfully Purchased",
			"1) Start the game and enter the server you chose when you bought the item.",
			"2) Enter the game with the character that will receive the item.",
			"3) If you right-click on the icon above in the game, the purchased item list will appear. When you click [get] from the list, the selected item will be moved to the inventory.",
			"Please view purchase information.",
			"Limited-time items can only be used until their expiration date. Check the date.",
			"Interner error occur while purchasing",
			"Report this to the administrator asap.",
			"* The item will be given to the character on the selected server. Please be careful in selecting the server.",
			"Purchase",
			"Confirm",
			"Cancel",
			"Back",
			"History",
			"<font color=\"#fff200\">%s</font> is limited to %d purchase per month.",
			"You cannot buy this item more than it's monthly limit. Please try again.",
			"Monthly limit purchase for this item has been reached."
		],
		"Popular Items",
		"Search",
		"Purchase",
		"Buy Item Guide",
		"Search Result"
	],
	"eg"=>[
		"Item Mall",
		"Premium Mall",
		"Silk Mall",
		"Reserverd",[
			"Item Name",
			"Server",
			"Qty",
			"Price",
			"Purchase",
			"Delete",
			"Delete All",
			"Purchase Selected",
			"Purchase All",
			"No Data"
		],
		"History",[
			"Year",
			"Month",
			"Total silks spent",
			"Total Purchase in Premium Mall (a)",
			"Total Purchase in Silk Mall (b)",
			"Total Purchase of Silks (a)+(b)",
			"* Silk received as gifts are spent first when purchasing items in game.",
			"Buy Item History",
			"Date",
			"Item",
			"Server",
			"Price",
			"Complete Receiving Item(s) In-Game",
			"No history available"
		],
		"Buy Item",[
			"Item Name",
			"ID",
			"Server",
			"Qty",
			"Price",
			"Available Balance",
			"Click the [Silk Charge] button on the top right corner to buy more credits.",
			"Item Successfully Purchased",
			"1) Start the game and enter the server you chose when you bought the item.",
			"2) Enter the game with the character that will receive the item.",
			"3) If you right-click on the icon above in the game, the purchased item list will appear. When you click [get] from the list, the selected item will be moved to the inventory.",
			"Please view purchase information.",
			"Limited-time items can only be used until their expiration date. Check the date.",
			"Interner error occur while purchasing",
			"Report this to the administrator asap.",
			"* The item will be given to the character on the selected server. Please be careful in selecting the server.",
			"Purchase",
			"Confirm",
			"Cancel",
			"Back",
			"History",
			"<font color=\"#fff200\">%s</font> is limited to %d purchase per month.",
			"You cannot buy this item more than it's monthly limit. Please try again.",
			"Monthly limit purchase for this item has been reached."
		],
		"Popular Items",
		"Search",
		"Purchase",
		"Buy Item Guide",
		"Search Result"
	]
];
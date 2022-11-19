<?php
class func
{
	public static function mallpackageitems($st0, $st1, $st2)
	{
		$dbo=db::mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WITH (NOLOCK) WHERE [silk_type]=? AND [ref_no]=? AND [sub_no]=?", [$st0, $st1, $st2], 2);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo;
	}
	public static function i_am_gm($jid)
	{
		return (func::tbuserinfo($jid)['sec_primary'] == 1 && func::tbuserinfo($jid)['sec_content'] == 1);
	}
	public static function getjcash($pjid)
	{
		$dbo=db::mssqlexec("EXEC [".PORTALDB."]..[X_GetJCash] ?", $pjid);
		if (!$dbo || $dbo->RowCount() == 0) return [0,0,0,0,0];
		return $dbo->FetchRow();
	}
	public static function getboughtcount($jid, $pcode)
	{
		$dbo=db::mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? AND [item_code_package]=? AND DATEPART(MONTH, [reg_date])=?", [$jid, $pcode, func::getservtime()->format('m')]);
		if ($dbo) return $dbo->RowCount();
		return -1;
	}
	public static function getvipinfo($pjid)
	{
		$dbo=db::mssqlexec("SELECT * FROM [".PORTALDB."]..[MU_VIP_Info] WITH (NOLOCK) WHERE [JID]=?", $pjid);
		if (!$dbo || $dbo->RowCount() == 0) return [0,0];

		if ($vip = $dbo->FetchRow())
		{
			$_expiry_date = new DateTime($vip['ExpireDate']);
			if ($_expiry_date->format('Y-m-d H:i:s') > func::getservtime()->format('Y-m-d H:i:s'))
			{
				return [$vip['VIPUserType'], $vip['VIPLv']];
			}
			else return [0,0];
		}
	}
	public static function tbuserinfo($jid)
	{
		$dbo=db::mssqlexec("SELECT * FROM [TB_User] WITH (NOLOCK) WHERE [JID]=?", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo->FetchRow();	
	}
	public static function getpurchasehistorymaxrow($jid)
	{
		$dbo=db::mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? ORDER BY [reg_date] DESC", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo->RowCount();
	}
	public static function gethistoryspent($jid)
	{
		$dbo=db::mssqlexec("EXEC [WEB_ITEM_HISTORY_X] 0,0,0,0,?,'count'", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return [0,0];
		return $dbo->FetchRow();
	}
	public static function gethistory($jid, $yr, $mn, $rows, $page)
	{
		$dbo=db::mssqlexec("EXEC [WEB_ITEM_HISTORY_X] 0,0,0,0,?,'get'",$jid);
		if (!$dbo || $dbo->RowCount() == 0) return [0, null];
		$maxpurchase = $dbo->RowCount();

		$dbo=db::mssqlexec("EXEC [WEB_ITEM_HISTORY_X] ?,?,?,?,?,'get'",[$page, $rows, $yr, $mn, $jid]);
		if (!$dbo || $dbo->RowCount() == 0) return [0, null];
		return [$maxpurchase, $dbo];
	}
	public static function getreserved($jid)
	{
		$dbo=db::mssqlexec("SELECT A.[idx], B.* FROM [WEB_ITEM_RESERVED] AS A WITH (NOLOCK) INNER JOIN [VW_WEB_MALL_LIST] AS B WITH (NOLOCK) ON B.[package_id] = A.[package_id] WHERE A.[userjid]=? ORDER BY A.[idx] DESC", $jid, 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;
	}
	public static function delreserved($jid, $idx=null)
	{
		if ($idx == "all")
		{
			db::mssqlexec("DELETE FROM [WEB_ITEM_RESERVED] WHERE [userjid]=?", $jid);
		}
		else if ($idx != null)
		{
			db::mssqlexec("DELETE FROM [WEB_ITEM_RESERVED] WHERE [userjid]=? AND [idx]=?", [$jid, $idx]);
		}
		else return;
	}
	public static function getmallitems($p, $ps, $st0, $st1, $st2, $kw='')
	{
		$dbo=db::mssqlexec("EXEC [WEB_ITEM_BUY_GAME_LIST_X] ?,?,?,?,?,?,?", [($p==0?1:$p), $ps, $st0, $st1, $st2, 1, $kw], 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;
	}
	public static function getcharinfo($charid)
	{
		$dbo=db::mssqlexec("SELECT * FROM [SILKROAD_R_SHARD].[DBO].[_Char] WITH (NOLOCK) WHERE [CharID]=?", $charid);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo->FetchRow();
	}
	public static function getshardcharnamejid($charname)
	{
		if (is_null($charname)) return -1;
		$dbo=db::mssqlexec("SELECT [UserJID] FROM [SILKROAD_R_ACCOUNT].[DBO].[SR_ShardCharNames] WITH (NOLOCK) WHERE [CharName]=?", $charname);
		if (!$dbo || $dbo->RowCount() == 0) return -2;
		return $dbo->FetchRow()[0];
	}
	public static function addreserved($jid, $pid)
	{
		$dbo=db::mssqlexec("EXEC [WEB_ITEM_RESERVED_X] ?,?", [$jid, $pid]);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo->FetchRow()[0];
	}
	public static function newitempurchase($jid, $st0, $price_offset, $pid, $pt_inv_id, $cp_inv_id, $servername, $tojid)
	{
		//JCISCode	JCISName
		//7000		Begin PS~CP TX By PS
		//10000		Complete PS~CP TX By PS(JCash subtracted)  --lets continue here
		//5000		Complete PS~PG TX By PS(JCash supplied)
		//8000		Fail PS~CP TX By CP(CPCash not supplied)
		//6000		Fail PS~CP TX By PS(Begin PS~CP TX Error)
		//9000		Fail PS~CP TX By PS(JCash not subtracted)
		//4000		Fail PS~PG TX By PS(JCash not supplied)
		if ($tojid < 0) return -65;
		$ip2hex = func::getipvisitor(true);
		$tb_info = func::tbuserinfo($jid);
		$pjid = $tb_info['PortalJID'];
		$package_info = func::getpackagedetail($pid);
		$package_code = $package_info['package_code'];
		$package_name = $package_info['package_name'];
		$args = [$pt_inv_id,$cp_inv_id,$tb_info['ServiceCompany'],$price_offset,$st0,$pjid,hexdec($ip2hex),$package_code,$package_name,1,$servername];
		if ($step1 = db::mssqlexec("EXEC [".PORTALDB."]..[X_DirectPaymentBeginCPTXByPS] ?,?,?,?,?,?,?,?,?,?,?", $args))
		{
			$retval = $step1->FetchRow();

			if ($retval['ReturnCode'] < 0) return $retval['ReturnCode'];

			if ($retval['ReturnCode'] == 7000)
			{
				if ($step2 = db::mssqlexec("EXEC [".PORTALDB."]..[X_DirectPaymentCompletedCPTXByPS] ?", $pt_inv_id))
				{
					$retval = $step2->FetchRow();

					if ($retval['ReturnCode'] < 0) return $retval['ReturnCode'];
					
					if ($retval['ReturnCode'] == 10000)
					{
						if ($tojid == $jid) $tojid = 0; // if recipient jid is equal to sender, let set to 0 for none gift transaction
						$args = [$jid,$st0,$price_offset,323,$servername,$pid,1,($tojid==0?'$game':'$game_gift'),func::getipvisitor(),$pt_inv_id,$cp_inv_id,$tojid];
						if ($step3 = db::mssqlexec("EXEC [WEB_ITEM_BUY_X] ?,?,?,?,?,?,?,?,?,?,?,?", $args))
						{
							$retval = $step3->FetchRow();							
							if ($retval['RetVal'] < 0)
							{
								func::writelog("WEB_ITEM_BUY_X RETURNED (".$retval['RetVal'].")", "buy_function_error.log");
								return $retval['RetVal'];
							}
							return $retval['RetVal'];
						}
						else { func::writelog("FAILED TO EXECUTE WEB_ITEM_BUY_X", "buy_function_error.log"); return -5; }
					} else { func::writelog("X_DirectPaymentCompletedCPTXByPS RETURNED (".$retval['ReturnCode'].")", "buy_function_error.log"); return -4; }
				} else { func::writelog("FAILED TO EXECUTE X_DirectPaymentCompletedCPTXByPS", "buy_function_error.log"); return -3; }
			} else { func::writelog("X_DirectPaymentBeginCPTXByPS RETURNED (".$retval['ReturnCode'].")", "buy_function_error.log"); return -2; }
		} else { func::writelog("FAILED TO EXECUTE X_DirectPaymentBeginCPTXByPS", "buy_function_error.log"); return -1; }
	}
	public static function getpackagedetail($pid)
	{
		$dbo=db::mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WITH (NOLOCK) WHERE [package_id]=? AND [service]=1", $pid, 2);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo->FetchRow();
	}
	public static function getitemscount($st0, $st1, $st2)
	{
		$dbo=db::mssqlexec("SELECT * FROM [WEB_PACKAGE_ITEM] WITH (NOLOCK) WHERE [service]=1 AND [silk_type]=? AND [shop_no]=? AND [shop_no_sub]=?", [$st0, $st1, $st2]);
		if (!$dbo || $dbo->RowCount() == 0) return 0;
		return $dbo->RowCount();
	}
	public static function popularitem()
	{
		$dbo=db::mssqlexec("SELECT A.*, B.[package_code], B.[silk_price], B.[silk_type], B.[discount_rate] FROM [WEB_ITEM_POPULAR] AS A WITH (NOLOCK) INNER JOIN [VW_WEB_MALL_LIST] AS B WITH (NOLOCK) ON B.[package_id] = A.[package_id] ORDER BY A.[idx]", null, 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;	
	}
	public static function newbestcount($type, $silk, $count)
	{
		switch ($type)
		{
			case "new":
				$dbo=db::mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WHERE [is_new]=1 AND [service]=1 AND [silk_type]=? ORDER BY [reg_date] DESC", $silk, 2, $count);
				break;
			case "best":
				$dbo=db::mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WHERE [is_best]=1 AND [service]=1 AND [silk_type]=? ORDER BY [reg_date] DESC", $silk, 2, $count);
				break;
			default: return false;
		}
		if (!$dbo || $dbo->RowCount() == 0) return [false];
		return [true, $dbo];
	}
	public static function certifykey($jid)
	{
		if ($dbo=db::mssqlexec("SELECT [Certifykey] FROM [WEB_ITEM_CERTIFYKEY] WITH (NOLOCK) WHERE [UserJID]=? ORDER BY [reg_date] DESC", $jid, null, 1))
		{
			return ($dbo->RowCount() == 0 ? -1 : $dbo->FetchRow()[0]);
		}
		return -1;		
	}
	public static function category($cat1, $cat2, $lang="us")
	{
		if (in_array($lang, ['us','tr','eg','de','es']))
		{
			if ($cat1==0 && $cat2==0)
			{
				if ($lang=="us") return ["New & Best","New","Best"];
				if ($lang=="de") return ["Neu & Besten","Neu","Besten"];
				if ($lang=="es") return ["Nueva & Mejor","Nueva","Mejor"];
				if ($lang=="tr") return ["Popüler","Yeni","En İyi"];
				if ($lang=="eg") return ["جمع","جديد","الأفضل"];
			}
			$dbo=db::mssqlexec("SELECT A.[shop_name_$lang], B.[sub_name_$lang] FROM WEB_MALL_CATEGORY AS A WITH (NOLOCK) INNER JOIN WEB_MALL_CATEGORY_SUB AS B WITH (NOLOCK) ON B.[ref_no] = A.[shop_no] WHERE A.[shop_no]=? AND B.[sub_no]=?", [$cat1,$cat2]);
			if (!$dbo || $dbo->RowCount() == 0) return 0;
			return $dbo->FetchRow();
		}
		else
		{
			//new and best
			if ($cat1==0 && $cat2==0) return ["New & Best","New","Best"];
			//expendables tab
			if ($cat1==1 && $cat2==1) return ["Expendables","Special"];
			if ($cat1==1 && $cat2==2) return ["Expendables","Scroll"];
			if ($cat1==1 && $cat2==3) return ["Expendables","Potion"];
			if ($cat1==1 && $cat2==4) return ["Expendables","Community"];
			if ($cat1==1 && $cat2==5) return ["Expendables","Others"];
			//avatar tab
			if ($cat1==2 && $cat2==1) return ["Avatar","Stall"];
			if ($cat1==2 && $cat2==2) return ["Avatar","Hat"];
			if ($cat1==2 && $cat2==3) return ["Avatar","Dress"];
			if ($cat1==2 && $cat2==4) return ["Avatar","Attach"];
			//pet tab
			if ($cat1==3 && $cat2==2) return ["Pet","Ability Pet"];
			if ($cat1==3 && $cat2==3) return ["Pet","Vehicle"];
			if ($cat1==3 && $cat2==5) return ["Pet","Pet Items"];
			//premium tab
			if ($cat1==4 && $cat2==1) return ["Premium","Premium"];
			if ($cat1==4 && $cat2==2) return ["Premium","Gear"];
			if ($cat1==4 && $cat2==3) return ["Premium","Others"];
			//alchemy tab
			if ($cat1==5 && $cat2==1) return ["Alchemy","Astral"];
			if ($cat1==5 && $cat2==2) return ["Alchemy","Immortal"];
			if ($cat1==5 && $cat2==3) return ["Alchemy","Others"];
			//fellow tab
			if ($cat1==6 && $cat2==1) return ["Fellow","Growth"];
			if ($cat1==6 && $cat2==2) return ["Fellow","Equipment"];
			if ($cat1==6 && $cat2==3) return ["Fellow","Others"];
			//vip tab
			if ($cat1==6 && $cat2==1) return ["VIP","VIP"];
		}
	}
	public static function init_common()
	{
		if (!isset($_COOKIE['dir']) || $_COOKIE['dir'] != ROOTDIR) setcookie("dir", ROOTDIR, ['samesite'=>'strict']);
		if (!isset($_COOKIE['ext']) || $_COOKIE['ext'] != EXT) setcookie("ext", EXT, ['samesite'=>'strict']);
	}
	public static function writelog($logmsg,$file="error.log")
	{
		$logdir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
		if (!is_dir($logdir)) mkdir($logdir);
		error_log(date('[Y-m-d H:i:s]: '). $logmsg . PHP_EOL, 3, $logdir . $file);
	}
	public static function sessionlog($contents,$file)
	{
		$sessiondir=$_SERVER['DOCUMENT_ROOT']."/sessions/";
		if (!is_dir($sessiondir)) mkdir($sessiondir);
		if (file_exists($sessiondir.$file)) return true;
		error_log($contents, 3, $sessiondir . $file);
		return true;
	}
	public static function matchreferer($referer)
	{
		$domain = parse_url(HTTP_DOMAIN, PHP_URL_HOST);
		return ($domain == parse_url($referer, PHP_URL_HOST));
	}
	public static function nbetween($varToCheck, $high, $low)
	{
		if($varToCheck < $low) return false;
		if($varToCheck > $high) return false;
		return true;
	}
	public static function getservtime()
	{
		$dbo=db::mssqlexec("SELECT GETDATE()");
		if (!$dbo || $dbo->RowCount() == 0) return 0;
		return date_create($dbo->FetchRow()[0]);
	}
	public static function getipvisitor($tohex=false)
	{
		$visitor_ip = '0.0.0.0';
		if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"]))
		{
			$visitor_ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
		elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
		{
			$visitor_ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$visitor_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$visitor_ip = $_SERVER['REMOTE_ADDR'];
		}
		if ($tohex) return func::ip2hex($visitor_ip);
		return $visitor_ip;
	}
	public static function ip2hex($ip) 
	{
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
			return sprintf("%08x",ip2long($ip));
		if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
			return false;
		if(($ip_n = inet_pton($ip)) === false) return false;
		$bits = 15;
		$ipbin = '';
		while ($bits >= 0)
		{
			$bin = sprintf("%02x",(ord($ip_n[$bits])));
			$ipbin = $bin.$ipbin;
			$bits--;
		}
		return $ipbin;
	} 
	public static function str_clean($str)
	{
		return (preg_match("#[^a-zA-Z0-9]#", $str) == true ? false : $str);
	}
	private static function param_encrypt($string, $key)
	{
	  $result = '';
	  $result = openssl_encrypt($string, "AES-256-CBC", $key, 0, substr(md5($key),0,-16));
	  if (!$result) return $result; //return false
	  return base64_encode($result);
	}	
	private static function param_decrypt($string, $key)
	{
	  $result = '';
	  $string = base64_decode($string);
	  $result = openssl_decrypt($string, "AES-256-CBC", $key, 0, substr(md5($key),0,-16));
	  if (!$result) return $result; //return false
	  return $result;
	}	
	public static function gentoken($params,$pass)
	{
		if (!$token_enc = func::param_encrypt($params.md5($params),$pass)) return false;
		setcookie("mlanguage1", explode("|",$params)[3], ['samesite'=>'strict']);
		setcookie("webmallkey", $token_enc, ['samesite'=>'strict']);
		return $token_enc;
	}
	public static function readtoken($token,$pass,$checkspan=true)
	{
		$token_dec = func::param_decrypt($token,$pass);
		$mdhash = substr($token_dec,(strlen($token_dec)-32),32);
		$string = substr($token_dec,0,(strlen($token_dec)-32));
		$explod = explode("|",$string);
		if (!$token_dec) return false; // wrong password
		if ($mdhash != md5($string)) return -1; // token has been hijack/edited
		if ($checkspan) { if (time() - $explod[0] > 1800) return -2; } // expiration of token
		if (count($explod) == 3) return array('jid'=>$explod[1],'key'=>$explod[2]);
		if (count($explod) == 4) return array('jid'=>$explod[1],'key'=>$explod[2],'loc'=>$explod[3]);
		if (count($explod) == 5) return array('jid'=>$explod[1],'key'=>$explod[2],'loc'=>$explod[3],'day'=>$explod[4]);
	}
	public static function str_starts_with($haystack, $needle)
	{
		return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}
	public static function str_ends_with($haystack, $needle)
	{
		return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
	}
	public static function str_contains($haystack, $needle)
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}

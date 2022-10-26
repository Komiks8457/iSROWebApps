<?php
class functions
{
	function billing($type, $uid)
	{
		if ($type==1) $dbo=$this->mssqlexec("SELECT A.*, B.[EmailAddr], B.[SHA256] FROM [TB_User] AS A INNER JOIN [MU_Email] AS B ON B.[JID] = A.[JID] WHERE A.[StrUserID]=?", $uid);
		if ($type==2) $dbo=$this->mssqlexec("SELECT A.*, B.[EmailAddr], B.[SHA256] FROM [TB_User] AS A INNER JOIN [MU_Email] AS B ON B.[JID] = A.[JID] WHERE B.[EmailAddr]=?", $uid);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo->FetchRow();
	}
	function tbuserinfo($jid)
	{
		$dbo=$this->mssqlexec("SELECT * FROM [TB_User] WITH (NOLOCK) WHERE [JID]=?", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo->FetchRow();	
	}
	function getpurchasehistorymaxrow($jid)
	{
		$dbo=$this->mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? ORDER BY [reg_date] DESC", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return -1;
		return $dbo->RowCount();
	}
	function gethistoryspent($jid)
	{
		$dbo=$this->mssqlexec("SELECT SUM(silk_own), SUM(silk_own_premium) FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=?", $jid);
		if (!$dbo || $dbo->RowCount() == 0) return [0,0];
		return $dbo->FetchRow();
	}
	function gethistory($jid, $yr, $mn, $rows, $page)
	{
		$offset = ($rows * $page) - $rows;
		
		$dbo=$this->mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? AND DATEPART(YEAR, [reg_date])=?", [$jid, $yr]);
		if (!$dbo || $dbo->RowCount() == 0) return [0, null];
		$maxpurchase = $dbo->RowCount();
		
		if ($mn == 0)
		{
			$dbo=$this->mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? AND DATEPART(YEAR, [reg_date])=? ORDER BY [reg_date] DESC", [$jid, $yr], null, $rows, $offset);
		}
		else
		{
			$dbo=$this->mssqlexec("SELECT * FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [cp_jid]=? AND DATEPART(YEAR, [reg_date])=? AND DATEPART(MONTH, [reg_date])=? ORDER BY [reg_date] DESC", [$jid, $yr, $mn], null, $rows, $offset);
		}
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return [$maxpurchase, $dbo];
	}
	function getreserved($jid)
	{
		$dbo=$this->mssqlexec("SELECT A.[idx], B.* FROM [WEB_ITEM_RESERVED] AS A WITH (NOLOCK) INNER JOIN [VW_WEB_MALL_LIST] AS B WITH (NOLOCK) ON B.[package_id] = A.[package_id] WHERE A.[userjid]=? ORDER BY A.[idx] DESC", $jid, 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;
	}
	function delreserved($jid, $idx=null)
	{
		if ($idx == "all")
		{
			$this->mssqlexec("DELETE FROM [WEB_ITEM_RESERVED] WHERE [userjid]=?", $jid);
		}
		else if ($idx != null)
		{
			$this->mssqlexec("DELETE FROM [WEB_ITEM_RESERVED] WHERE [userjid]=? AND [idx]=?", [$jid, $idx]);
		}
		else
		{
			return;
		}
	}
	function getmallitems($p, $ps, $st0, $st1, $st2, $io=1, $kw='')
	{
		$args = [$p, $ps, $st0, $st1, $st2, $io, $kw];
		//page, page_size, silk_type, shop_type1, shop_type2, is_open, keyword
		$dbo=$this->mssqlexec("EXEC [WEB_ITEM_BUY_GAME_LIST_X] ?,?,?,?,?,?,?", $args, 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;
	}
	function getcharinfo($charid)
	{
		$dbo=$this->mssqlexec("SELECT * FROM [SILKROAD_R_SHARD].[DBO].[_Char] WITH (NOLOCK) WHERE [CharID]=?", $charid);
		if (!$dbo || $dbo->RowCount() == 0) -1;
		return $dbo->FetchRow();
	}
	function addreserved($jid, $pid)
	{
		$dbo=$this->mssqlexec("EXEC [WEB_ITEM_RESERVED_X] ?,?", [$jid, $pid]);
		if (!$dbo || $dbo->RowCount() == 0) -1;
		return $dbo->FetchRow()[0];
	}
	function itempurchase($jid, $st0, $price, $pid, $section, $ip, $inv_id, $cp_inv_id)
	{
		$args = [$jid, $st0, $price, 323, 'TEST', $pid, $section, '$game', $ip, $inv_id, $cp_inv_id];
		$dbo=$this->mssqlexec("EXEC [WEB_ITEM_BUY_X] ?,?,?,?,?,?,?,?,?,?,?", $args);
		if (!$dbo || $dbo->RowCount() == 0) -1;
		return $dbo->FetchRow()[0];
	}
	function getsilkusage($jid)
	{
		$o = $dbo=$this->mssqlexec("SELECT ISNULL(SUM([silk_own_premium]), 0) FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE DATEPART(month, [reg_date]) = DATEPART(month, GETDATE()) AND [cp_jid]=?", $jid);
		$x = $dbo=$this->mssqlexec("SELECT ISNULL(SUM([silk_own_premium]), 0) FROM [WEB_ITEM_GIVE_LIST] WITH (NOLOCK) WHERE [reg_date] >= DATEADD(MONTH, -3, GETDATE()) AND [cp_jid]=?", $jid);
		return [$o->FetchRow()[0] ?? 0, $x->FetchRow()[0] ?? 0];
		
	}
	function getpackagedetail($pid)
	{
		$dbo=$this->mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WITH (NOLOCK) WHERE [package_id]=? AND [service]=1", $pid, 2);
		if (!$dbo || $dbo->RowCount() == 0) -1;
		return $dbo->FetchRow();
	}
	function getitemscount($st0, $st1, $st2)
	{
		$dbo=$this->mssqlexec("SELECT * FROM [WEB_PACKAGE_ITEM] WITH (NOLOCK) WHERE [service]=1 AND [silk_type]=? AND [shop_no]=? AND [shop_no_sub]=?", [$st0, $st1, $st2]);
		if (!$dbo || $dbo->RowCount() == 0) -1;
		return $dbo->RowCount();
	}
	function popularitem()
	{
		$dbo=$this->mssqlexec("SELECT A.*, B.[package_code], B.[silk_price], B.[silk_type] FROM [WEB_ITEM_POPULAR] AS A WITH (NOLOCK) INNER JOIN [VW_WEB_MALL_LIST] AS B WITH (NOLOCK) ON B.[package_id] = A.[package_id] ORDER BY A.[idx]", null, 2);
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return $dbo;	
	}
	function newbestcount($type, $silk, $count)
	{
		switch ($type)
		{
			case "new":
				$dbo=$this->mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WHERE [is_new]=1 AND [service]=1 AND [silk_type]=? ORDER BY [reg_date] DESC", $silk, 2, $count);
				break;
			case "best":
				$dbo=$this->mssqlexec("SELECT * FROM [VW_WEB_MALL_LIST] WHERE [is_best]=1 AND [service]=1 AND [silk_type]=? ORDER BY [reg_date] DESC", $silk, 2, $count);
				break;
			default: return false;
		}
		if (!$dbo || $dbo->RowCount() == 0) return false;
		return [true, $dbo];
	}
	function getusersilk(int $jid, int $type = 0)
	{
		switch($type)
		{
			case 0:
				$dbo=$this->mssqlexec("SELECT [silk_own] FROM [SK_Silk] WHERE [JID]=?", $jid, 1);
				break;
			case 1:
				$dbo=$this->mssqlexec("SELECT [silk_gift] FROM [SK_Silk] WHERE [JID]=?", $jid, 1);
				break;
			case 3:
				$dbo=$this->mssqlexec("SELECT [silk_own_premium] FROM [SK_Silk] WHERE [JID]=?", $jid, 1);
				break;
			case 4:
				$dbo=$this->mssqlexec("SELECT [silk_gift_premium] FROM [SK_Silk] WHERE [JID]=?", $jid, 1);
				break;
			default: break;
		}		
		if (!$dbo || $dbo->RowCount() == 0) return 0;
		return $dbo->FetchRow()[0];		
	}		
	function certifykey($jid)
	{
		if ($dbo=$this->mssqlexec("SELECT [Certifykey] FROM [WEB_ITEM_CERTIFYKEY] WITH (NOLOCK) WHERE [UserJID]=? ORDER BY [reg_date] DESC", $jid, null, 1))
		{
			return ($dbo->RowCount() == 0 ? -1 : $dbo->FetchRow()[0]);
		}
		return -1;		
	}
	function category($cat1, $cat2, $lang="us")
	{
		if (in_array($lang, ['us','tr','eg','de','es']))
		{
			if ($cat1==0 && $cat2==0)
			{
				if ($lang=="us") return ["<b>New & Best</b>","New","Best"];
				if ($lang=="de") return ["<b>Neu & Besten</b>","Neu","Besten"];
				if ($lang=="es") return ["<b>Nueva & Mejor</b>","Nueva","Mejor"];
				if ($lang=="tr") return ["<b>Popüler</b>","Yeni","En İyi"];
				if ($lang=="eg") return ["<b>جمع</b>","جديد","الأفضل"];
			}
			$dbo=$this->mssqlexec("SELECT A.[shop_name_$lang], B.[sub_name_$lang] FROM WEB_MALL_CATEGORY AS A WITH (NOLOCK) INNER JOIN WEB_MALL_CATEGORY_SUB AS B WITH (NOLOCK) ON B.[ref_no] = A.[shop_no] WHERE A.[shop_no]=? AND B.[sub_no]=?", [$cat1,$cat2]);
			if (!$dbo || $dbo->RowCount() == 0) return 0;
			return $dbo->FetchRow();
		}
		else
		{
			//new and best
			if ($cat1==0 && $cat2==0) return ["<b>New & Best</b>","New","Best"];
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
		}
	}
	function writelog($logmsg,$file="error.log")
	{
		$logdir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
		if (!is_dir($logdir)) mkdir($logdir);
		error_log(date('[Y-m-d H:i:s]: '). $logmsg . PHP_EOL, 3, $logdir . $file);
	}
	function sessionlog($contents,$file)
	{
		$sessiondir=$_SERVER['DOCUMENT_ROOT']."/sessions/";
		if (!is_dir($sessiondir)) mkdir($sessiondir);
		if (file_exists($sessiondir.$file)) return true;
		error_log($contents, 3, $sessiondir . $file);
		return true;
	}
	function matchreferer($referer)
	{
		$domain = parse_url(HTTP_DOMAIN, PHP_URL_HOST);
		return ($domain == parse_url($referer, PHP_URL_HOST));
	}
	function nbetween($varToCheck, $high, $low)
	{
		if($varToCheck < $low) return false;
		if($varToCheck > $high) return false;
		return true;
	}
	function getservtime()
	{
		$dbo=$this->mssqlexec("SELECT GETDATE()");
		if (!$dbo || $dbo->RowCount() == 0) return 0;
		return date_create($dbo->FetchRow()[0]);
	}
	function getipvisitor()
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
		return $visitor_ip;
	}
	function str_clean($str)
	{
		return (preg_match("#[^a-zA-Z0-9]#", $str) == true ? false : $str);
	}
	function param_encrypt($string, $key)
	{
	  $result = '';
	  $result = openssl_encrypt($string, "AES-256-CBC", $key, 0, substr(md5($key),0,-16));
	  if (!$result) return $result; //return false
	  return base64_encode($result);
	}	
	function param_decrypt($string, $key)
	{
	  $result = '';
	  $string = base64_decode($string);
	  $result = openssl_decrypt($string, "AES-256-CBC", $key, 0, substr(md5($key),0,-16));
	  if (!$result) return $result; //return false
	  return $result;
	}	
	function gentoken($params,$pass)
	{
		if (!$token_enc = $this->param_encrypt($params.md5($params),$pass)) return false;
		setcookie("mlanguage1", explode("|",$params)[3], ['samesite'=>'strict']);
		setcookie("webmallkey", $token_enc, ['samesite'=>'strict']);
		return $token_enc;
	}
	function readtoken($token,$pass,$checkspan=true)
	{
		$token_dec = $this->param_decrypt($token,$pass);
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
	function str_starts_with($haystack, $needle)
	{
		return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
	}
	function str_ends_with($haystack, $needle)
	{
		return $needle !== '' && substr($haystack, -strlen($needle)) === (string)$needle;
	}
	function str_contains($haystack, $needle)
	{
		return $needle !== '' && mb_strpos($haystack, $needle) !== false;
	}
}

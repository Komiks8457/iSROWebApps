<?php
require_once('adodb5/adodb.inc.php');
require_once('defines.php');
require_once('functions.php');

class db extends functions
{
	function connect_mssql($dbName)
	{
		$dbo=ADONewConnection("mssqlnative");
		$dbo->setConnectionParameter('CharacterSet','UTF-8');
		if (!$dbo->Connect(RDBHOST0, RDBUSER0, RDBPASS0, $dbName))
		{
			$this->writelog("[Connection Error]\t".$dbo->errorMsg(), "db_errors.log");
			$dbo->Close();
			die();
		}
		return $dbo;
	}
	function mssqlexec($query, $value=null, $fmode=null, $rowstoreturn=-1, $startoffset=-1)
	{
		$dbo=$this->connect_mssql(RDBNAME0);
		
		$dbo->SetFetchMode(($fmode==null ? 3 : $fmode));
				
		if (is_array($value))
		{
			if ($this->str_starts_with(strtolower($query), "select"))
			{
				if ($dbq = $dbo->selectLimit($dbo->prepare($query), $rowstoreturn, $startoffset, $value))
				{
					$dbo->Close();
					return $dbq;
				}
			}
			if ($dbq = $dbo->Execute($dbo->prepare($query), $value))
			{
				$dbo->Close();
				return $dbq;
			}
		}
		else if ($value != null && !is_array($value))
		{
			if ($this->str_starts_with(strtolower($query), "select"))
			{
				if ($dbq = $dbo->selectLimit($dbo->prepare($query), $rowstoreturn, $startoffset, [$value]))
				{
					$dbo->Close();
					return $dbq;
				}
			}
			if ($dbq = $dbo->Execute($dbo->prepare($query), [$value]))
			{
				$dbo->Close();
				return $dbq;
			}
		}
		else
		{
			if ($this->str_starts_with(strtolower($query), "select"))
			{
				if ($dbq = $dbo->selectLimit($dbo->prepare($query), $rowstoreturn, $startoffset))
				{
					$dbo->Close();
					return $dbq;
				}
			}
			if ($dbq = $dbo->Execute($query))
			{
				$dbo->Close();
				return $dbq;
			}
		}
		
		$this->writelog("[Query Error]\t".$dbo->errorMsg()."Query: ".$query." Values(".($value==null ? 'null' : (is_array($value)==true ? implode(",", $value) : $value)).")", "db_errors.log");
		$dbo->Close();
		die();
	}
}
?>

<?php
error_reporting(E_ALL & ~E_NOTICE);
$page = ($_SERVER["REQUEST_URI"] <> ''? $_SERVER["REQUEST_URI"] : $_SERVER["SCRIPT_NAME"]);
if(strpos($page, '/restore.php') !== 0)
	die();

if(isset($_SERVER["BX_PERSONAL_ROOT"]) && $_SERVER["BX_PERSONAL_ROOT"] <> "")
	define("BX_PERSONAL_ROOT", $_SERVER["BX_PERSONAL_ROOT"]);
else
	define("BX_PERSONAL_ROOT", "/app");

if (!defined("BX_DIR_PERMISSIONS"))
	define("BX_DIR_PERMISSIONS", 0755);

if (!defined("BX_FILE_PERMISSIONS"))
	define("BX_FILE_PERMISSIONS", 0644);

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", getmicrotime());

if (function_exists('mb_internal_encoding'))
	mb_internal_encoding('ISO-8859-1');

@set_time_limit(0);

$lang = 'ru';
if ($lang=='ru')
	header("Content-type:text/html; charset=UTF-8");

$mArr_ru =  array(
			"WINDOW_TITLE" => "Распаковка",
			"TITLE1" => "Шаг 1: Распаковка архива",
			"TITLE_PROCESS1" => "Шаг 1: Выполняется распаковка архива",
			"TITLE_PROCESS2" => "Шаг 2: Выполняется восстановление базы данных",
			"TITLE2" => "Шаг 2: Восстановление базы данных",
			"SELECT_LANG" => "Выберите язык",
			"ARC_NAME" => "Имя архива",
			"MAX_TIME" => "Шаг выполнения (сек.)",
			"ERR_NO_ARC" => "Не выбран архив для распаковки!",
			"BUT_TEXT1" => "Распаковать",
			"DUMP_NAME" => "Файл резервной копии базы",
			"USER_NAME" => "Имя пользователя",
			"USER_PASS" => "Пароль",
			"BASE_NAME" => "Имя базы данных",
			"BASE_HOST" => "Адрес базы данных",
			"BASE_RESTORE" => "Восстановить",
			"ERR_NO_DUMP" => "Не выбран архив базы данных для восстановления!",
			"ERR_EXTRACT" => "Ошибка:",
			"ERR_DUMP_RESTORE" => "Ошибка восстановления базы данных:",
			"ERR_DB_CONNECT" => "Ошибка соединения с базой данных:",
			"ERR_CREATE_DB" => "Ошибка создания базы",
			"FINISH" => "Операция выполнена успешно",
			"FINISH_MSG" => "Операция восстановления системы завершена успешно!",
			"EXTRACT_FINISH_TITLE" => "Распаковка архива",
			"EXTRACT_FINISH_MSG" => "Распаковка архива завершена успешно!",
			"BASE_CREATE_DB" => "Создать базу данных",
			"EXTRACT_FINISH_DELL" => "Обязательно удалите скрипт restore.php и файл резервной копии из корневой директории сайта.",
			"EXTRACT_FULL_FINISH_DELL" => "Обязательно удалите скрипт restore.php, файл резервной копии из корневой директории сайта, а также дамп базы.",
			"BUT_DELL" => "Удалить",
			"FINISH_ERR_DELL" => "Не удалось удалить все файлы! Обязательно удалите их вручную.",
			"FINISH_ERR_DELL_TITLE" => "Ошибка удаления файлов!"
			);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title><?= getMsg("WINDOW_TITLE", $lang)?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<style type="text/css">
		.tablebody1 {background-color:#f9fafd; padding:5px;font-family:Verdana,Arial, Helvetica, sans-serif;color:#616263;font-size:10px;}
		.tabletitle1 {background-color:#ffffff; font-family:Arial;color:#000000;font-size:17px;font-weight: bold;}
		.tableborder1 {background-color:#aeb8d7; padding:0;}
		.tableborder2 {background-color:#cccccc; padding:0;}
		.selectitem {width: 200;}
	</style>
</head>

<body style="margin-top:0; margin-bottom:0; margin-right:0; margin-left:0;">

<table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr><td colspan="3" weight="100%" id="top">&nbsp;</td></tr>
<tr><td id="left_td">&nbsp;</td><td id="main">

<form name="restore" id="restore" action="restore.php" method="POST">
<input type="hidden" name="Step" id="Step_id" value="">
<script language="JavaScript">
	
	var size_x = document.body.clientWidth;
	document.getElementById('left_td').width = (size_x - 500)/2;	
	function reloadPage(val)
	{
			document.getElementById('Step_id').value = val;
			document.getElementById('restore').action='restore.php';
			document.getElementById('restore').submit();
	}

	function selectLang()
	{
		document.getElementById('restore').action='restore.php';
		document.getElementById('restore').submit();
	}
</script>
<?

$Step = IntVal(@$_REQUEST["Step"]);
if ($Step <= 0)
	$Step = 1;

if($Step == 3)
{
	$max_exec_time = @$_REQUEST["time"];
	$d_pos = intVal(@$_REQUEST["d_pos"]);
	if ($d_pos < 0)
		$d_pos = 0;
	
	$oDB = new CDBRestore(@$_REQUEST["db_host"], @$_REQUEST["db_name"], @$_REQUEST["db_user"], @$_REQUEST["db_pass"], @$_REQUEST["dump_name"], START_EXEC_TIME, $max_exec_time, $d_pos);

	if(!$oDB->Connect())
	{
		echo showMsg(getMsg("ERR_DB_CONNECT", $lang), $oDB->getError());
		$Step = 2;
	}
}

if($Step == 1)
{
	?>
		<table width="500"  border="0" cellspacing="0" cellpadding="0">
		<tr><td colspan="6" class="tabletitle1" align="Left" nowrap="nowrap" valign="center"><?= getMsg("TITLE1", $lang)?></td></tr>
		<tr>
			<td colspan="6" class="tableborder2" height="1"></td>
		</tr>
		<tr>
			<td colspan="6" class="" align="center" nowrap="nowrap" valign="center">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="6" class="tableborder1" height="1"></td>
		</tr>
		<tr>
			<td class="tableborder1" width="1"></td>
			<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>
		<tr>
			<td width="1" class="tableborder1"></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("ARC_NAME", $lang)?></td>
			<td class="tablebody1" width="240"><select class="selectitem" name="arc_name"><? $option = getArcList(); echo ((strlen($option) > 0) ? $option : "<option>"); ?></select></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>
		<tr>
			<td width="1" class="tableborder1"></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("MAX_TIME", $lang)?></td>
			<td class="tablebody1" width="240"><input class="selectitem" type="text" name="time" id="time_id" value="<?echo isset($_REQUEST["time"]) ? intVal(@$_REQUEST["time"]) : 30;?>"></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>
		<tr>
			<td class="tableborder1" width="1"></td>
			<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>
		<tr>
			<td width="1" class="tableborder1"></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tablebody1" ></td>
			<td class="tablebody1" width="240"><input type="button" class="selectitem" id="start_button" value="<?= getMsg("BUT_TEXT1", $lang)?>" onClick="if(document.restore.arc_name.value=='') alert('<?= getMsg("ERR_NO_ARC", $lang)?>'); else return reloadPage(2);"></td>
			<td class="tablebody1" width="19">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>
		<tr>
			<td class="tableborder1" width="1"></td>
			<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
			<td class="tableborder1" width="1"></td>
		</tr>

		<tr>
			<td colspan="6" class="tableborder1" height="1"></td>
		</tr>
		</table>
	<?
}
elseif($Step == 2)
{
	$arc_name = htmlspecialchars(@$_REQUEST["arc_name"]);
	$max_exec_time = intVal(@$_REQUEST["time"]);
	$pos = intVal(@$_REQUEST["pos"]);
	if ($pos < 0)
		$pos = 0;
	
	$oArc = new CArchiver($_SERVER["DOCUMENT_ROOT"]."/".$arc_name, true, START_EXEC_TIME, $max_exec_time, $pos);

	if(!$oArc->extractFiles($_SERVER["DOCUMENT_ROOT"]."/") && $oArc->end_time)
	{
		$pos = $oArc->getFilePos();
		?>
		
		<input type="hidden" name="time" id="time_id" value="<?= $max_exec_time?>">
		<input type="hidden" name="pos" id="pos_id" value="<?= $pos?>">
		<input type="hidden" name="arc_name" id="arc_name_id" value="<?= $arc_name?>">
			<script>
				reloadPage(2);
			</script>
		<?
	}
	else
	{
		if(count($oArc->GetErrors()) > 0)
		{
			$earr = array();
			$earr = $oArc->GetErrors();
			$e_str = "";
			foreach($earr as $val)
			{
				
				$e_str .= $val[1]."<br>";
			}
			
			echo showMsg(getMsg("ERR_EXTRACT", $lang), $e_str);
		}
		else
		{
			$strDName = "";
			$strDName = getDumpList();
			$arc_name = @$_REQUEST["arc_name"];

			if($strDName != "")
			{
			?>	
				<table width="500"  border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td colspan="6" class="tabletitle1" align="Left" nowrap="nowrap" valign="center"><?= getMsg("TITLE2", $lang)?></td>
				</tr>
				<tr>
					<td colspan="6" class="tableborder2" height="1"></td>
				</tr>
				<tr>
					<td colspan="6" class="" align="center" nowrap="nowrap" valign="center">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="6" class="tableborder1" height="1"></td>
				</tr>
				<tr>
					<td class="tableborder1" width="1"></td>
					<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("DUMP_NAME", $lang)?></td>
					<td class="tablebody1" width="240"><select class="selectitem" name="dump_name"><?= $strDName;?></select></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("USER_NAME", $lang)?></td>
					<td class="tablebody1" width="240"><input type="text" class="selectitem" name="db_user" id="db_user_id" value="<?echo (strlen(@$_REQUEST["db_user"])>0) ? htmlspecialchars(@$_REQUEST["db_user"]) : ""?>"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("USER_PASS", $lang)?></td>
					<td class="tablebody1" width="240"><input type="password" class="selectitem" name="db_pass" id="db_pass_id" value="<?echo (strlen(@$_REQUEST["db_pass"])>0) ? htmlspecialchars(@$_REQUEST["db_pass"]) : ""?>"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("BASE_NAME", $lang)?></td>
					<td class="tablebody1" width="240"><input type="text" class="selectitem" name="db_name" id="db_name_id" value="<?echo (strlen(@$_REQUEST["db_name"])>0) ? htmlspecialchars(@$_REQUEST["db_name"]) : ""?>"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("BASE_HOST", $lang)?></td>
					<td class="tablebody1" width="240"><input type="text" class="selectitem" name="db_host" id="db_host_id" value="<?echo (strlen(@$_REQUEST["db_host"])>0) ? htmlspecialchars(@$_REQUEST['db_host']) : "localhost"?>"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap"><?= getMsg("BASE_CREATE_DB", $lang)?></td>
 					<td class="tablebody1" width="240"><input type="checkbox" name="create_db" id="create_db_id" value="Y" <? if(@$_REQUEST["create_db"]=="Y") echo "checked";?>></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>

				<tr>
					<td class="tableborder1" width="1"></td>
					<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td width="1" class="tableborder1"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tablebody1" align="right" nowrap="nowrap">&nbsp;</td>
					<td class="tablebody1" width="240"><input type="button" class="selectitem" id="start_button" value="<?= getMsg("BASE_RESTORE", $lang)?>" onClick="if(document.restore.dump_name.value=='') alert('<?= getMsg("ERR_NO_DUMP", $lang)?>'); else return reloadPage(3);"></td>
					<td class="tablebody1" width="19">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>
				<tr>
					<td class="tableborder1" width="1"></td>
					<td colspan="4" class="tablebody1" height="5">&nbsp;</td>
					<td class="tableborder1" width="1"></td>
				</tr>

				<tr>
					<td colspan="6" class="tableborder1" height="1"></td>
				</tr>

				</table>

				<input type="hidden" name="time" id="time_id" value="<?= intVal($max_exec_time)?>">
				<input type="hidden" name="arc_name" id="arc_name_id" value="<?= $arc_name?>">
				<?
			}
			else
			{
				echo showMsg(getMsg("EXTRACT_FINISH_TITLE", $lang), getMsg("EXTRACT_FINISH_MSG", $lang), 1);
			}
		}
	}
	$oArc->_close();
}
elseif($Step == 3)
{
	$max_exec_time = @$_REQUEST["time"];
	$d_pos = intVal(@$_REQUEST["d_pos"]);
	if ($d_pos < 0)
		$d_pos = 0;
	
	if ($d_pos==0) // start
	{
		$dbconn = $_SERVER['DOCUMENT_ROOT']."/app/config/database.php";
		if(file_exists($dbconn))
		{
			$arReplace = array(
				'DB_HOST' => 'db_host',
				'DB_USER' => 'db_user',
				'DB_PASSWORD' => 'db_pass',
				'DB_BASE' => 'db_name'
			);
			include($dbconn);
			$arFile = file($dbconn);
			foreach($arFile as $line)
			{
				if (preg_match("#^[ \t]*".'\$'."(DB_[a-zA-Z]+)#",$line,$regs))
				{
					$key = $regs[1];
					$new_val = $_REQUEST[$arReplace[$key]];
					if (isset($new_val) && $$key != $new_val)
					{
						$strFile.='#'.$line.
						'$'.$key.' = "'.addslashes($new_val).'";'."\n\n";
					}
					else
						$strFile.=$line;
				}
				else
					$strFile.=$line;
			}
			$f = fopen($dbconn,"wb");
			fputs($f,$strFile);
			fclose($f);
		}

		if($oDB->restore() && !$oDB->is_end())
		{
			$d_pos = $oDB->getPos();
			$oDB->close();
			$arc_name = @$_REQUEST["arc_name"];
			?>
				<input type="hidden" name="time" id="time_id" value="<?= $max_exec_time?>">
				<input type="hidden" name="arc_name" id="arc_name_id" value="<?= $arc_name?>">
				<input type="hidden" name="d_pos" id="d_pos_id" value="<?= $d_pos?>">
				<input type="hidden" name="db_user" id="db_user_id" value="<?= @$_REQUEST["db_user"]?>">
				<input type="hidden" name="db_pass" id="db_pass_id" value="<?= strlen(@$_REQUEST["db_pass"]) > 0 ? htmlspecialchars(@$_REQUEST["db_pass"]) : ""?>">
				<input type="hidden" name="db_name" id="db_name_id" value="<?= @$_REQUEST["db_name"]?>">
				<input type="hidden" name="db_host" id="db_host_id" value="<?= @$_REQUEST["db_host"]?>">
				<input type="hidden" name="dump_name" id="dump_name_id" value="<?= @$_REQUEST["dump_name"]?>">

			<script>
				reloadPage(3);
			</script>
		<?	

		}
		else
		{
			if($oDB->getError() != "")
				echo showMsg(getMsg("ERR_DUMP_RESTORE", $lang), $oDB->getError());
			else
				echo showMsg(getMsg("FINISH", $lang), getMsg("FINISH_MSG", $lang), 2);
		}
	}
}
elseif($Step == 4)
{
	$Warn_a = unlink($_SERVER["DOCUMENT_ROOT"]."/".@$_REQUEST["arc_name"]);
	$Warn_b = unlink($_SERVER["DOCUMENT_ROOT"]."/restore.php");
	
	if(!$Warn_a || !$Warn_b)
		echo showMsg(getMsg("FINISH_ERR_DELL_TITLE", $lang), getMsg("FINISH_ERR_DELL", $lang));
	else
	{
		echo showMsg(getMsg("FINISH", $lang), getMsg("FINISH_MSG", $lang));
		echo '<script>window.setTimeout(function(){document.location="/";},3000);</script>';
	}
	
}
elseif($Step == 5)
{
	$Warn_a = unlink($_SERVER["DOCUMENT_ROOT"]."/".@$_REQUEST["arc_name"]);
	$Warn_b = unlink($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/backup/".@$_REQUEST["dump_name"]);
	$Warn_c = unlink($_SERVER["DOCUMENT_ROOT"]."/restore.php");
	@unlink($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/backup/".str_replace('.sql','_after_connect.sql',@$_REQUEST["dump_name"]));
	
	if(!$Warn_a || !$Warn_b || !$Warn_c)
		echo showMsg(getMsg("FINISH_ERR_DELL_TITLE", $lang), getMsg("FINISH_ERR_DELL", $lang));
	else
	{
		echo showMsg(getMsg("FINISH", $lang), getMsg("FINISH_MSG", $lang));
		echo '<script>window.setTimeout(function(){document.location="/";},3000);</script>';
	}
}

?>
</td><td id="right_td">&nbsp;</td></tr>
<tr><td colspan="3">&nbsp;</td><tr>
</table>
</form>

<script language="JavaScript">
	var size_y = document.body.clientHeight;
	var table_h = document.getElementById('main').clientHeight;
	document.getElementById('right_td').width = (size_x - 500)/2;
	document.getElementById('top').height = (size_y - table_h)/2;
</script>

</body>
</html>

<?

class CArchiver
{
	var $_strArchiveName = "";
	var $_bCompress = false;
	var $_strSeparator = " ";
	var $_dFile = 0;
	var $_arErrors = array();
	var $max_execution_time = 0;
	var $start = 0;
	
	var $end =false;
	var $end_time = false;
	var $file_pos = 0;

	function CArchiver($strArchiveName, $bCompress = false, $start, $max_execution_time, $pos)
	{
		$this->_bCompress = false;
		$this->max_execution_time = $max_execution_time;
		$this->start = $start;
		$this->file_pos = $pos;
		if (!$bCompress)
		{
			if (@file_exists($strArchiveName))
			{
				if ($fp = @fopen($strArchiveName, "rb"))
				{
					$data = fread($fp, 2);
					fclose($fp);
					if ($data == "\37\213")
					{
						$this->_bCompress = True;
					}
				}
			}
			else
			{
				if (substr($strArchiveName, -2) == 'gz')
				{
					$this->_bCompress = True;
				}
			}
		}
		else
		{
			$this->_bCompress = True;
		}

		$this->_strArchiveName = $strArchiveName;
		$this->_arErrors = array();
	}

	function GetErrors()
	{
		return $this->_arErrors;
	}

	function extractFiles($strPath)
	{
		$this->_arErrors = array();
		$v_result = true;
		
		if ($v_result = $this->_openRead())
		{
			if($this->file_pos > 0)
			{
				if ($this->_bCompress)
					@gzseek($this->_dFile, $this->file_pos-512);
				else
					@fseek($this->_dFile, $this->file_pos-512);
			}
			$v_result = $this->_extractList($strPath, '');
	
		}
		return $v_result;
	}

	function _extractList($p_path, $p_remove_path)
	{
		$v_result = true;
		$v_nb = 0;
		$v_extract_all = true;
		$v_listing = false;

		$p_path = str_replace("\\", "/", $p_path);

		if ($p_path == '' || (substr($p_path, 0, 1) != '/' && substr($p_path, 0, 3) != "../" && !strpos($p_path, ':')))
			$p_path = "./".$p_path;

		$p_remove_path = str_replace("\\", "/", $p_remove_path);
		
		if (($p_remove_path != '') && (substr($p_remove_path, -1) != '/'))
			$p_remove_path .= '/';

		$p_remove_path_size = strlen($p_remove_path);

		clearstatcache();

		while (strlen($v_binary_data = $this->_readBlock()) != 0)
		{
			if((getmicrotime() - $this->start) < round($this->max_execution_time * 0.8))
			{
				$v_extract_file = FALSE;
					
				if (!$this->_readHeader($v_binary_data, $v_header))
					return false;

				if ($v_header['filename'] == '')
					continue;

				// ----- Look for long filename
				if ($v_header['typeflag'] == 'L')
					if (!$this->_readLongHeader($v_header))
						return false;

				if (($p_remove_path != '') && (substr($v_header['filename'], 0, $p_remove_path_size) == $p_remove_path))
					$v_header['filename'] = substr($v_header['filename'], $p_remove_path_size);

				if (($p_path != './') && ($p_path != '/'))
				{
					while (substr($p_path, -1) == '/')
						$p_path = substr($p_path, 0, strlen($p_path)-1);

					if (substr($v_header['filename'], 0, 1) == '/')
						$v_header['filename'] = $p_path.$v_header['filename'];
					else
						$v_header['filename'] = $p_path.'/'.$v_header['filename'];
				}

				if (file_exists($v_header['filename']))
				{
					if ((is_dir($v_header['filename'])) && ($v_header['typeflag'] == ''))
					{
						$this->_arErrors[] = array("DIR_EXISTS", "File '".$v_header['filename']."' already exists as a directory");
						return false;
					}
					if ((is_file($v_header['filename'])) && ($v_header['typeflag'] == "5"))
					{
						$this->_arErrors[] = array("FILE_EXISTS", "Directory '".$v_header['filename']."' already exists as a file");
						return false;
					}
					if (!is_writeable($v_header['filename']))
					{
						$this->_arErrors[] = array("FILE_PERMS", "File '".$v_header['filename']."' already exists and is write protected");
						return false;
					}
				}
				elseif (($v_result = $this->_dirCheck(($v_header['typeflag'] == "5" ? $v_header['filename'] : dirname($v_header['filename'])))) != 1)
				{
					$this->_arErrors[] = array("NO_DIR", "Unable to create path for '".$v_header['filename']."'");
					return false;
				}

				if ($v_header['typeflag'] == "5")
				{
					if (!file_exists($v_header['filename']))
					{
						if (!@mkdir($v_header['filename'], BX_DIR_PERMISSIONS))
						{
							$this->_arErrors[] = array("ERR_CREATE_DIR", "Unable to create directory '".$v_header['filename']."'");
							return false;
						}
					}
				}
				else
				{
					$bSkip = false;
					if ($v_header['filename']==$_SERVER['DOCUMENT_ROOT'].'/.htaccess' && file_exists($v_header['filename']))
					{ // skip /.htaccess
						$bSkip = true;
						$n = floor($v_header['size']/512);
						for ($i = 0; $i < $n; $i++)
							$v_content = $this->_readBlock();
						if (($v_header['size'] % 512) != 0)
							$v_content = $this->_readBlock();
					}
					elseif (($v_dest_file = @fopen($v_header['filename'], "wb")) == 0)
					{
						$this->_arErrors[] = array("ERR_CREATE_FILE", "Error while opening '".$v_header['filename']."' in write binary mode");
						return false;
					}
					else
					{
						$n = floor($v_header['size']/512);
						for ($i = 0; $i < $n; $i++)
						{
							$v_content = $this->_readBlock();
							fwrite($v_dest_file, $v_content, 512);
						}
						if (($v_header['size'] % 512) != 0)
						{
							$v_content = $this->_readBlock();
							fwrite($v_dest_file, $v_content, ($v_header['size'] % 512));
						}

						@fclose($v_dest_file);
						chmod($v_header['filename'], BX_FILE_PERMISSIONS);
						touch($v_header['filename'], $v_header['mtime']);
						// To be completed
						//chmod($v_header[filename], DecOct($v_header[mode]));
					}

					clearstatcache();

					if (!$bSkip && filesize($v_header['filename']) != $v_header['size'])
					{
						$this->_arErrors[] = array("ERR_SIZE_CHECK", "Extracted file '".$v_header['filename']."' have incorrect file size '".filesize($v_filename)."' (".$v_header['size']." expected). Archive may be corrupted");
						return false;
					}
				}

				if (($v_file_dir = dirname($v_header['filename'])) == $v_header['filename'])
					$v_file_dir = '';

				if ((substr($v_header['filename'], 0, 1) == '/') && ($v_file_dir == ''))
					$v_file_dir = '/';
			}
			else
			{
				$this->end_time = true;
				return false;
			}
		}
		return true;
	}

	function _readBlock()
	{
		$v_block = "";
		if (is_resource($this->_dFile))
		{
			if ($this->_bCompress)
				$v_block = @gzread($this->_dFile, 512);
			else
				$v_block = @fread($this->_dFile, 512);
		}
		return $v_block;
	}

	function _readHeader($v_binary_data, &$v_header)
	{
		if (strlen($v_binary_data)==0)
		{
			$v_header['filename'] = '';
			return true;
		}

		if (strlen($v_binary_data) != 512)
		{
			$v_header['filename'] = '';
			$this->_arErrors[] = array("INV_BLOCK_SIZE", "Invalid block size : ".strlen($v_binary_data)."");
			return false;
		}

		$v_checksum = 0;
		for ($i = 0; $i < 148; $i++)
			$v_checksum+=ord(substr($v_binary_data, $i, 1));
		for ($i = 148; $i < 156; $i++)
			$v_checksum += ord(' ');
		for ($i = 156; $i < 512; $i++)
			$v_checksum+=ord(substr($v_binary_data, $i, 1));

		$v_data = unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $v_binary_data);

		$v_header['checksum'] = OctDec(trim($v_data['checksum']));
		if ($v_header['checksum'] != $v_checksum)
		{
			$v_header['filename'] = '';

			if (($v_checksum == 256) && ($v_header['checksum'] == 0))
				return true;

			$this->_arErrors[] = array("INV_BLOCK_CHECK", "Invalid checksum for file '".$v_data['filename']."' : ".$v_checksum." calculated, ".$v_header['checksum']." expected");
			return false;
		}

		// ----- Extract the properties
		$v_header['filename'] = trim($v_data['filename']);
		$v_header['mode'] = OctDec(trim($v_data['mode']));
		$v_header['uid'] = OctDec(trim($v_data['uid']));
		$v_header['gid'] = OctDec(trim($v_data['gid']));
		$v_header['size'] = OctDec(trim($v_data['size']));
		$v_header['mtime'] = OctDec(trim($v_data['mtime']));
		if (($v_header['typeflag'] = $v_data['typeflag']) == "5")
			$v_header['size'] = 0;
		
		return true;
	}

	function _readLongHeader(&$v_header)
	{
		$v_filename = '';
		$n = floor($v_header['size']/512);
		for ($i = 0; $i < $n; $i++)
		{
			$v_content = $this->_readBlock();
			$v_filename .= $v_content;
   		}
		if (($tail = $v_header['size'] % 512) != 0)
		{
			$v_content = $this->_readBlock();
			$v_filename .= substr($v_content, 0, $tail);
		}

		$v_binary_data = $this->_readBlock();

		if (!$this->_readHeader($v_binary_data, $v_header))
			return false;

		$v_header['filename'] = $v_filename;

		return true;
	}

	function &_parseFileParams(&$vFileList)
	{
		if (isset($vFileList) && is_array($vFileList))
			return $vFileList;
		elseif (isset($vFileList) && strlen($vFileList)>0)
			return explode($this->_strSeparator, $vFileList);
		else
			return array();
	}

	function _openRead()
	{
		if ($this->_bCompress)
			$this->_dFile = @gzopen($this->_strArchiveName, "rb");
		else
			$this->_dFile = @fopen($this->_strArchiveName, "rb");

		if (!$this->_dFile)
		{
			$this->_arErrors[] = array("ERR_OPEN", "Unable to open '".$this->_strArchiveName."' in read mode");
			return false;
		}

		return true;
	}

	function _close()
	{
		if (is_resource($this->_dFile))
		{
			if ($this->_bCompress)
				@gzclose($this->_dFile);
			else
				@fclose($this->_dFile);

			$this->_dFile = 0;
		}

		return true;
	}

	function _normalizePath($strPath)
	{
		$strResult = "";

		if (strlen($strPath)>0)
		{
			$strPath = str_replace("\\", "/", $strPath);
			$arPath = explode('/', $strPath);

			for ($i = count($arPath)-1; $i>=0; $i--)
			{
				if ($arPath[$i] == ".")
					;
				elseif ($arPath[$i] == "..")
					$i--;
				elseif (($arPath[$i] == '') && ($i!=(count($arPath)-1)) && ($i!=0))
					;
				else
					$strResult = $arPath[$i].($i!=(count($arPath)-1) ? '/'.$strResult : '');
			}
		}
		return $strResult;
	}

	function _dirCheck($p_dir)
	{
		if ((@is_dir($p_dir)) || ($p_dir == ''))
			return true;

		$p_parent_dir = dirname($p_dir);

		if (($p_parent_dir != $p_dir) &&
			($p_parent_dir != '') &&
			(!$this->_dirCheck($p_parent_dir)))
			return false;

		if (!@mkdir($p_dir, BX_DIR_PERMISSIONS))
		{
			$this->_arErrors[] = array("CANT_CREATE_PATH", "Unable to create directory '".$p_dir."'");
			return false;
		}

		return true;
	}
	
	function endTime()
	{
		return $this->end_time;
	}

	function getFilePos()
	{
		if (is_resource($this->_dFile))
		{
			if ($this->_bCompress)
				return @gztell($this->_dFile);
			else
				return @ftell($this->_dFile);
		}
	}
}

class CDBRestore
{
	var $type = "";
	var $DBHost ="";
	var $DBName = "";
	var $DBLogin = "";
	var $DBPassword = "";
	var $DBdump = "";
	var $db_Conn = "";
	var $db_Error = "";
	var $f_end = false;
	var $start;
	var $d_pos;
	var $_dFile;
	
	function CDBRestore($DBHost, $DBName, $DBLogin, $DBPassword, $DBdump, $start, $max_exec_time, $d_pos)
	{
		$this->DBHost = $DBHost;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBdump = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/backup/".$DBdump;
		$this->start = $start;
		$this->max_exec_time = $max_exec_time;
		$this->d_pos = $d_pos;
	}

	//Соединяется с базой данных
	function Connect()
	{
		
		$this->type="MYSQL";
		if (!defined("DBPersistent")) define("DBPersistent",false);
		if (DBPersistent)
		{
			$this->db_Conn = @mysql_pconnect($this->DBHost, $this->DBLogin, $this->DBPassword);
		}
		else
		{
			$this->db_Conn = @mysql_connect($this->DBHost, $this->DBLogin, $this->DBPassword);
		}
		if(!($this->db_Conn))
		{
			if (DBPersistent) $s = "mysql_pconnect"; else $s = "mysql_connect";
			if(($str_err = mysql_error()) != "")
				$this->db_Error .= "<br><font color=#ff0000>Error! ".$s."('-', '-', '-')</font><br>".$str_err."<br>";
			return false;
		}

		$after_file = str_replace('.sql','_after_connect.sql',$this->DBdump);
		if (file_exists($after_file))
		{
			$rs = fopen($after_file,'rb');
			$str = fread($rs,filesize($after_file));
			fclose($rs);
			$arSql = explode(';',$str);
			foreach($arSql as $sql)
				mysql_query($sql);
		}

		
		if (@$_REQUEST["create_db"]=="Y")
		{
			if(!@mysql_query("CREATE DATABASE ".@$_REQUEST["db_name"], $this->db_Conn))
			{
				$this->db_Error = getMsg("ERR_CREATE_DB", $lang).' '.mysql_error();
				return false;
			}
		}

		if(!mysql_select_db($this->DBName, $this->db_Conn))
		{
			if(($str_err = mysql_error($this->db_Conn)) != "")
				$this->db_Error = "<br><font color=#ff0000>Error! mysql_select_db($this->DBName)</font><br>".$str_err."<br>";
			return false;
		}
	
	
		
		return true;
	}

	function readSql()
	{
		$cache ="";
	
		while(!feof($this->_dFile) && (substr($cache, (strlen($cache)-2), 1) != ";"))
			$cache .= fgets($this->_dFile);
	
		if(!feof($this->_dFile))
			return $cache;
		else
		{
			$this->f_end = true;
			return false;
		}
	}
	
	function restore()
	{
		$this->_dFile = @fopen($this->DBdump, 'r');
		//$this->_dFile_tmp = @fopen($this->DBdump.'.tmp', 'a+');
		//fwrite($this->_dFile_tmp, $this->DBdump.'_'.filesize($this->DBdump));

		if($this->d_pos > 0)
			@fseek($this->_dFile, $this->d_pos);
		
		$sql = "";
		
		while(($sql = $this->readSql()) && (getmicrotime() - $this->start) < round($this->max_exec_time * 0.6))
		{
			//fwrite($this->_dFile_tmp, $sql);
			$result = @mysql_query($sql, $this->db_Conn);
			
			if(!$result)
			{
				$this->db_Error .= mysql_error();
				return false;
			}
			$sql = "";
		}

		if($sql != "")
		{
			$result = @mysql_query($sql, $this->db_Conn);
			
			if(!$result)
			{
				$this->db_Error .= mysql_error();
				return false;
			}
			$sql = "";
		}
		$result = @mysql_query("UPDATE user_user SET password=MD5('demo') WHERE login='admin'", $this->db_Conn);

		return true;
	}
	
	function getError()
	{
		return $this->db_Error;
	}
	
	function getPos()
	{
		if (is_resource($this->_dFile))
		{
			return @ftell($this->_dFile);
		}
	}
	
	function close()
	{
		unset($this->_dFile);
		return true;
	}

	function is_end()
	{
		return $this->f_end;
	}
}


function getmicrotime()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function getDumpList()
{
	$dump = "";
	$handle = @opendir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/backup");
	while (false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == "..")
			continue;

		if(is_dir($_SERVER["DOCUMENT_ROOT"]."/".$file))
			continue;

		if (strpos($file,'_after_connect.sql'))
			continue;

		if(substr($file, strlen($file) - 3, 3) == "sql")
			$dump .= "<option value=\"$file\"> ".$file;
	}

	return $dump;
}

function getMsg($str_index, $str_lang)
{
	global $mArr_ru;
	return $mArr_ru[$str_index];
}

function getArcList()
{
	 $arc = "";
	$handle = @opendir($_SERVER["DOCUMENT_ROOT"]);
	while (false !== ($file = @readdir($handle)))
	{
		if($file == "." || $file == "..")
			continue;

		if(is_dir($_SERVER["DOCUMENT_ROOT"]."/".$file))
			continue;

		if(substr($file, strlen($file) - 6, 6) == "tar.gz" || substr($file, strlen($file) - 3, 3) == "tar")
			$arc .= "<option value=\"$file\"> ".$file;
	}

	return $arc;
}

function showMsg($title, $msg, $type = 0)
{
	global $lang;

	$del_pos = "";

	if($type == 1)
	{
		$del_pos = "<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td colspan=\"4\" class=\"tablebody1\" height=\"5\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\" colspan=\"2\" algin=\"center\" valign=\"center\">".getMsg("EXTRACT_FINISH_DELL", $lang)."</td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td width=\"1\" class=\"tableborder1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\">&nbsp;</td>
			<td class=\"tablebody1\" width=\"240\"><input type=\"button\" class=\"selectitem\" id=\"del_button\" value=\"".getMsg("BUT_DELL", $lang)."\" onClick=\"reloadPage(4);\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>";
	}
	elseif($type == 2)
	{
		$del_pos = "<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td colspan=\"4\" class=\"tablebody1\" height=\"5\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\" colspan=\"2\" algin=\"center\" valign=\"center\">".getMsg("EXTRACT_FULL_FINISH_DELL", $lang)."</td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td width=\"1\" class=\"tableborder1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\">&nbsp;</td>
			<td class=\"tablebody1\" width=\"240\"><input type=\"button\" class=\"selectitem\" id=\"del_button\" value=\"".getMsg("BUT_DELL", $lang)."\" onClick=\"reloadPage(5);\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>";
	}


	$res = "<table width=\"500\"  border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		<tr><td colspan=\"6\" class=\"tabletitle1\" align=\"Left\" nowrap=\"nowrap\" valign=\"center\">$title</td></tr>
		<tr>
			<td colspan=\"6\" class=\"tableborder2\" height=\"1\"></td>
		</tr>
		<tr>
			<td colspan=\"6\" align=\"center\" nowrap=\"nowrap\" valign=\"center\">&nbsp;</td>
		</tr>
		<tr>
			<td colspan=\"6\" class=\"tableborder1\" height=\"1\"></td>
		</tr>
		<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td colspan=\"4\" class=\"tablebody1\" height=\"5\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\" colspan=\"2\" algin=\"center\" valign=\"center\">$msg</td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		$del_pos
		<tr>
			<td width=\"1\" class=\"tableborder1\"></td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tablebody1\">&nbsp;</td>
			<td class=\"tablebody1\" width=\"240\">&nbsp;</td>
			<td class=\"tablebody1\" width=\"19\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td class=\"tableborder1\" width=\"1\"></td>
			<td colspan=\"4\" class=\"tablebody1\" height=\"5\">&nbsp;</td>
			<td class=\"tableborder1\" width=\"1\"></td>
		</tr>
		<tr>
			<td colspan=\"6\" class=\"tableborder1\" height=\"1\"></td>
		</tr>
		</table>
		<input type=\"hidden\" name=\"arc_name\" id=\"arc_name_id\" value=\"".@$_REQUEST["arc_name"]."\">
		<input type=\"hidden\" name=\"dump_name\" id=\"dump_name_id\" value=\"".@$_REQUEST["dump_name"]."\">";

	return $res;
}

?>

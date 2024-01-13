<?php
/** Adminer Editor - Compact database editor
* @link https://www.adminer.org/
* @author Jakub Vrana, https://www.vrana.cz/
* @copyright 2009 Jakub Vrana
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 4.8.1
*/function
adminer_errors($_b,$Ab){return!!preg_match('~^(Trying to access array offset on value of type null|Undefined array key)~',$Ab);}error_reporting(6135);set_error_handler('adminer_errors',E_WARNING);$Mb=!preg_match('~^(unsafe_raw)?$~',ini_get("filter.default"));if($Mb||ini_get("filter.default_flags")){foreach(array('_GET','_POST','_COOKIE','_SERVER')as$W){$We=filter_input_array(constant("INPUT$W"),FILTER_UNSAFE_RAW);if($We)$$W=$We;}}if(function_exists("mb_internal_encoding"))mb_internal_encoding("8bit");function
connection(){global$f;return$f;}function
adminer(){global$b;return$b;}function
version(){global$ca;return$ca;}function
idf_unescape($t){if(!preg_match('~^[`\'"]~',$t))return$t;$Dc=substr($t,-1);return
str_replace($Dc.$Dc,$Dc,substr($t,1,-1));}function
escape_string($W){return
substr(q($W),1,-1);}function
number($W){return
preg_replace('~[^0-9]+~','',$W);}function
number_type(){return'((?<!o)int(?!er)|numeric|real|float|double|decimal|money)';}function
remove_slashes($Cd,$Mb=false){if(function_exists("get_magic_quotes_gpc")&&get_magic_quotes_gpc()){while(list($x,$W)=each($Cd)){foreach($W
as$yc=>$V){unset($Cd[$x][$yc]);if(is_array($V)){$Cd[$x][stripslashes($yc)]=$V;$Cd[]=&$Cd[$x][stripslashes($yc)];}else$Cd[$x][stripslashes($yc)]=($Mb?$V:stripslashes($V));}}}}function
bracket_escape($t,$ua=false){static$Le=array(':'=>':1',']'=>':2','['=>':3','"'=>':4');return
strtr($t,($ua?array_flip($Le):$Le));}function
min_version($ff,$Mc="",$g=null){global$f;if(!$g)$g=$f;$be=$g->server_info;if($Mc&&preg_match('~([\d.]+)-MariaDB~',$be,$A)){$be=$A[1];$ff=$Mc;}return(version_compare($be,$ff)>=0);}function
charset($f){return(min_version("5.5.3",0,$f)?"utf8mb4":"utf8");}function
script($ie,$Ke="\n"){return"<script".nonce().">$ie</script>$Ke";}function
script_src($bf){return"<script src='".h($bf)."'".nonce()."></script>\n";}function
nonce(){return' nonce="'.get_nonce().'"';}function
target_blank(){return' target="_blank" rel="noreferrer noopener"';}function
h($R){return
str_replace("\0","&#0;",htmlspecialchars($R,ENT_QUOTES,'utf-8'));}function
nl_br($R){return
str_replace("\n","<br>",$R);}function
checkbox($D,$X,$Fa,$Ac="",$gd="",$Ia="",$Bc=""){$L="<input type='checkbox' name='$D' value='".h($X)."'".($Fa?" checked":"").($Bc?" aria-labelledby='$Bc'":"").">".($gd?script("qsl('input').onclick = function () { $gd };",""):"");return($Ac!=""||$Ia?"<label".($Ia?" class='$Ia'":"").">$L".h($Ac)."</label>":$L);}function
optionlist($E,$Wd=null,$df=false){$L="";foreach($E
as$yc=>$V){$kd=array($yc=>$V);if(is_array($V)){$L.='<optgroup label="'.h($yc).'">';$kd=$V;}foreach($kd
as$x=>$W)$L.='<option'.($df||is_string($x)?' value="'.h($x).'"':'').(($df||is_string($x)?(string)$x:$W)===$Wd?' selected':'').'>'.h($W);if(is_array($V))$L.='</optgroup>';}return$L;}function
html_select($D,$E,$X="",$fd=true,$Bc=""){if($fd)return"<select name='".h($D)."'".($Bc?" aria-labelledby='$Bc'":"").">".optionlist($E,$X)."</select>".(is_string($fd)?script("qsl('select').onchange = function () { $fd };",""):"");$L="";foreach($E
as$x=>$W)$L.="<label><input type='radio' name='".h($D)."' value='".h($x)."'".($x==$X?" checked":"").">".h($W)."</label>";return$L;}function
select_input($c,$E,$X="",$fd="",$vd=""){$ye=($E?"select":"input");return"<$ye$c".($E?"><option value=''>$vd".optionlist($E,$X,true)."</select>":" size='10' value='".h($X)."' placeholder='$vd'>").($fd?script("qsl('$ye').onchange = $fd;",""):"");}function
confirm($B="",$Xd="qsl('input')"){return
script("$Xd.onclick = function () { return confirm('".($B?js_escape($B):'Are you sure?')."'); };","");}function
print_fieldset($s,$Fc,$if=false){echo"<fieldset><legend>","<a href='#fieldset-$s'>$Fc</a>",script("qsl('a').onclick = partial(toggle, 'fieldset-$s');",""),"</legend>","<div id='fieldset-$s'".($if?"":" class='hidden'").">\n";}function
bold($Aa,$Ia=""){return($Aa?" class='active $Ia'":($Ia?" class='$Ia'":""));}function
odd($L=' class="odd"'){static$r=0;if(!$L)$r=-1;return($r++%2?$L:'');}function
js_escape($R){return
addcslashes($R,"\r\n'\\/");}function
json_row($x,$W=null){static$Nb=true;if($Nb)echo"{";if($x!=""){echo($Nb?"":",")."\n\t\"".addcslashes($x,"\r\n\t\"\\/").'": '.($W!==null?'"'.addcslashes($W,"\r\n\"\\/").'"':'null');$Nb=false;}else{echo"\n}\n";$Nb=true;}}function
ini_bool($rc){$W=ini_get($rc);return(preg_match('~^(on|true|yes)$~i',$W)||(int)$W);}function
sid(){static$L;if($L===null)$L=(SID&&!($_COOKIE&&ini_bool("session.use_cookies")));return$L;}function
set_password($Y,$P,$U,$H){$_SESSION["pwds"][$Y][$P][$U]=($_COOKIE["adminer_key"]&&is_string($H)?array(encrypt_string($H,$_COOKIE["adminer_key"])):$H);}function
get_password(){$L=get_session("pwds");if(is_array($L))$L=($_COOKIE["adminer_key"]?decrypt_string($L[0],$_COOKIE["adminer_key"]):false);return$L;}function
q($R){global$f;return$f->quote($R);}function
get_vals($J,$d=0){global$f;$L=array();$K=$f->query($J);if(is_object($K)){while($M=$K->fetch_row())$L[]=$M[$d];}return$L;}function
get_key_vals($J,$g=null,$ee=true){global$f;if(!is_object($g))$g=$f;$L=array();$K=$g->query($J);if(is_object($K)){while($M=$K->fetch_row()){if($ee)$L[$M[0]]=$M[1];else$L[]=$M[0];}}return$L;}function
get_rows($J,$g=null,$k="<p class='error'>"){global$f;$Ta=(is_object($g)?$g:$f);$L=array();$K=$Ta->query($J);if(is_object($K)){while($M=$K->fetch_assoc())$L[]=$M;}elseif(!$K&&!is_object($g)&&$k&&defined("PAGE_HEADER"))echo$k.error()."\n";return$L;}function
unique_array($M,$u){foreach($u
as$pc){if(preg_match("~PRIMARY|UNIQUE~",$pc["type"])){$L=array();foreach($pc["columns"]as$x){if(!isset($M[$x]))continue
2;$L[$x]=$M[$x];}return$L;}}}function
escape_key($x){if(preg_match('(^([\w(]+)('.str_replace("_",".*",preg_quote(idf_escape("_"))).')([ \w)]+)$)',$x,$A))return$A[1].idf_escape(idf_unescape($A[2])).$A[3];return
idf_escape($x);}function
where($Z,$m=array()){global$f,$w;$L=array();foreach((array)$Z["where"]as$x=>$W){$x=bracket_escape($x,1);$d=escape_key($x);$L[]=$d.($w=="sql"&&is_numeric($W)&&preg_match('~\.~',$W)?" LIKE ".q($W):($w=="mssql"?" LIKE ".q(preg_replace('~[_%[]~','[\0]',$W)):" = ".unconvert_field($m[$x],q($W))));if($w=="sql"&&preg_match('~char|text~',$m[$x]["type"])&&preg_match("~[^ -@]~",$W))$L[]="$d = ".q($W)." COLLATE ".charset($f)."_bin";}foreach((array)$Z["null"]as$x)$L[]=escape_key($x)." IS NULL";return
implode(" AND ",$L);}function
where_check($W,$m=array()){parse_str($W,$Ea);remove_slashes(array(&$Ea));return
where($Ea,$m);}function
where_link($r,$d,$X,$id="="){return"&where%5B$r%5D%5Bcol%5D=".urlencode($d)."&where%5B$r%5D%5Bop%5D=".urlencode(($X!==null?$id:"IS NULL"))."&where%5B$r%5D%5Bval%5D=".urlencode($X);}function
convert_fields($e,$m,$O=array()){$L="";foreach($e
as$x=>$W){if($O&&!in_array(idf_escape($x),$O))continue;$oa=convert_field($m[$x]);if($oa)$L.=", $oa AS ".idf_escape($x);}return$L;}function
cookie($D,$X,$Ic=2592000){global$aa;return
header("Set-Cookie: $D=".urlencode($X).($Ic?"; expires=".gmdate("D, d M Y H:i:s",time()+$Ic)." GMT":"")."; path=".preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]).($aa?"; secure":"")."; HttpOnly; SameSite=lax",false);}function
restart_session(){if(!ini_bool("session.use_cookies"))session_start();}function
stop_session($Pb=false){$cf=ini_bool("session.use_cookies");if(!$cf||$Pb){session_write_close();if($cf&&@ini_set("session.use_cookies",false)===false)session_start();}}function&get_session($x){return$_SESSION[$x][DRIVER][SERVER][$_GET["username"]];}function
set_session($x,$W){$_SESSION[$x][DRIVER][SERVER][$_GET["username"]]=$W;}function
auth_url($Y,$P,$U,$h=null){global$ob;preg_match('~([^?]*)\??(.*)~',remove_from_uri(implode("|",array_keys($ob))."|username|".($h!==null?"db|":"").session_name()),$A);return"$A[1]?".(sid()?SID."&":"").($Y!="server"||$P!=""?urlencode($Y)."=".urlencode($P)."&":"")."username=".urlencode($U).($h!=""?"&db=".urlencode($h):"").($A[2]?"&$A[2]":"");}function
is_ajax(){return($_SERVER["HTTP_X_REQUESTED_WITH"]=="XMLHttpRequest");}function
redirect($_,$B=null){if($B!==null){restart_session();$_SESSION["messages"][preg_replace('~^[^?]*~','',($_!==null?$_:$_SERVER["REQUEST_URI"]))][]=$B;}if($_!==null){if($_=="")$_=".";header("Location: $_");exit;}}function
query_redirect($J,$_,$B,$Kd=true,$Eb=true,$Hb=false,$Ce=""){global$f,$k,$b;if($Eb){$me=microtime(true);$Hb=!$f->query($J);$Ce=format_time($me);}$ke="";if($J)$ke=$b->messageQuery($J,$Ce,$Hb);if($Hb){$k=error().$ke.script("messagesPrint();");return
false;}if($Kd)redirect($_,$B.$ke);return
true;}function
queries($J){global$f;static$Fd=array();static$me;if(!$me)$me=microtime(true);if($J===null)return
array(implode("\n",$Fd),format_time($me));$Fd[]=(preg_match('~;$~',$J)?"DELIMITER ;;\n$J;\nDELIMITER ":$J).";";return$f->query($J);}function
apply_queries($J,$xe,$Bb='table'){foreach($xe
as$S){if(!queries("$J ".$Bb($S)))return
false;}return
true;}function
queries_redirect($_,$B,$Kd){list($Fd,$Ce)=queries(null);return
query_redirect($Fd,$_,$B,$Kd,false,!$Kd,$Ce);}function
format_time($me){return
sprintf('%.3f s',max(0,microtime(true)-$me));}function
relative_uri(){return
str_replace(":","%3a",preg_replace('~^[^?]*/([^?]*)~','\1',$_SERVER["REQUEST_URI"]));}function
remove_from_uri($qd=""){return
substr(preg_replace("~(?<=[?&])($qd".(SID?"":"|".session_name()).")=[^&]*&~",'',relative_uri()."&"),0,-1);}function
pagination($G,$bb){return" ".($G==$bb?$G+1:'<a href="'.h(remove_from_uri("page").($G?"&page=$G".($_GET["next"]?"&next=".urlencode($_GET["next"]):""):"")).'">'.($G+1)."</a>");}function
get_file($x,$fb=false){$Kb=$_FILES[$x];if(!$Kb)return
null;foreach($Kb
as$x=>$W)$Kb[$x]=(array)$W;$L='';foreach($Kb["error"]as$x=>$k){if($k)return$k;$D=$Kb["name"][$x];$Ie=$Kb["tmp_name"][$x];$Ua=file_get_contents($fb&&preg_match('~\.gz$~',$D)?"compress.zlib://$Ie":$Ie);if($fb){$me=substr($Ua,0,3);if(function_exists("iconv")&&preg_match("~^\xFE\xFF|^\xFF\xFE~",$me,$Ld))$Ua=iconv("utf-16","utf-8",$Ua);elseif($me=="\xEF\xBB\xBF")$Ua=substr($Ua,3);$L.=$Ua."\n\n";}else$L.=$Ua;}return$L;}function
upload_error($k){$Qc=($k==UPLOAD_ERR_INI_SIZE?ini_get("upload_max_filesize"):0);return($k?'Unable to upload a file.'.($Qc?" ".sprintf('Maximum allowed file size is %sB.',$Qc):""):'File does not exist.');}function
repeat_pattern($I,$Gc){return
str_repeat("$I{0,65535}",$Gc/65535)."$I{0,".($Gc%65535)."}";}function
is_utf8($W){return(preg_match('~~u',$W)&&!preg_match('~[\0-\x8\xB\xC\xE-\x1F]~',$W));}function
shorten_utf8($R,$Gc=80,$se=""){if(!preg_match("(^(".repeat_pattern("[\t\r\n -\x{10FFFF}]",$Gc).")($)?)u",$R,$A))preg_match("(^(".repeat_pattern("[\t\r\n -~]",$Gc).")($)?)",$R,$A);return
h($A[1]).$se.(isset($A[2])?"":"<i>‚Ä¶</i>");}function
format_number($W){return
strtr(number_format($W,0,".",','),preg_split('~~u','0123456789',-1,PREG_SPLIT_NO_EMPTY));}function
friendly_url($W){return
preg_replace('~[^a-z0-9_]~i','-',$W);}function
hidden_fields($Cd,$oc=array(),$zd=''){$L=false;foreach($Cd
as$x=>$W){if(!in_array($x,$oc)){if(is_array($W))hidden_fields($W,array(),$x);else{$L=true;echo'<input type="hidden" name="'.h($zd?$zd."[$x]":$x).'" value="'.h($W).'">';}}}return$L;}function
hidden_fields_get(){echo(sid()?'<input type="hidden" name="'.session_name().'" value="'.h(session_id()).'">':''),(SERVER!==null?'<input type="hidden" name="'.DRIVER.'" value="'.h(SERVER).'">':""),'<input type="hidden" name="username" value="'.h($_GET["username"]).'">';}function
table_status1($S,$Ib=false){$L=table_status($S,$Ib);return($L?$L:array("Name"=>$S));}function
column_foreign_keys($S){global$b;$L=array();foreach($b->foreignKeys($S)as$Tb){foreach($Tb["source"]as$W)$L[$W][]=$Tb;}return$L;}function
enum_input($Pe,$c,$l,$X,$xb=null){global$b;preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$Nc);$L=($xb!==null?"<label><input type='$Pe'$c value='$xb'".((is_array($X)?in_array($xb,$X):$X===0)?" checked":"")."><i>".'empty'."</i></label>":"");foreach($Nc[1]as$r=>$W){$W=stripcslashes(str_replace("''","'",$W));$Fa=(is_int($X)?$X==$r+1:(is_array($X)?in_array($r+1,$X):$X===$W));$L.=" <label><input type='$Pe'$c value='".($r+1)."'".($Fa?' checked':'').'>'.h($b->editVal($W,$l)).'</label>';}return$L;}function
input($l,$X,$p){global$Re,$b,$w;$D=h(bracket_escape($l["field"]));echo"<td class='function'>";if(is_array($X)&&!$p){$na=array($X);if(version_compare(PHP_VERSION,5.4)>=0)$na[]=JSON_PRETTY_PRINT;$X=call_user_func_array('json_encode',$na);$p="json";}$Od=($w=="mssql"&&$l["auto_increment"]);if($Od&&!$_POST["save"])$p=null;$Yb=(isset($_GET["select"])||$Od?array("orig"=>'original'):array())+$b->editFunctions($l);$c=" name='fields[$D]'";if($l["type"]=="enum")echo
h($Yb[""])."<td>".$b->editInput($_GET["edit"],$l,$c,$X);else{$ec=(in_array($p,$Yb)||isset($Yb[$p]));echo(count($Yb)>1?"<select name='function[$D]'>".optionlist($Yb,$p===null||$ec?$p:"")."</select>".on_help("getTarget(event).value.replace(/^SQL\$/, '')",1).script("qsl('select').onchange = functionChange;",""):h(reset($Yb))).'<td>';$tc=$b->editInput($_GET["edit"],$l,$c,$X);if($tc!="")echo$tc;elseif(preg_match('~bool~',$l["type"]))echo"<input type='hidden'$c value='0'>"."<input type='checkbox'".(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?" checked='checked'":"")."$c value='1'>";elseif($l["type"]=="set"){preg_match_all("~'((?:[^']|'')*)'~",$l["length"],$Nc);foreach($Nc[1]as$r=>$W){$W=stripcslashes(str_replace("''","'",$W));$Fa=(is_int($X)?($X>>$r)&1:in_array($W,explode(",",$X),true));echo" <label><input type='checkbox' name='fields[$D][$r]' value='".(1<<$r)."'".($Fa?' checked':'').">".h($b->editVal($W,$l)).'</label>';}}elseif(preg_match('~blob|bytea|raw|file~',$l["type"])&&ini_bool("file_uploads"))echo"<input type='file' name='fields-$D'>";elseif(($_e=preg_match('~text|lob|memo~i',$l["type"]))||preg_match("~\n~",$X)){if($_e&&$w!="sqlite")$c.=" cols='50' rows='12'";else{$N=min(12,substr_count($X,"\n")+1);$c.=" cols='30' rows='$N'".($N==1?" style='height: 1.2em;'":"");}echo"<textarea$c>".h($X).'</textarea>';}elseif($p=="json"||preg_match('~^jsonb?$~',$l["type"]))echo"<textarea$c cols='50' rows='12' class='jush-js'>".h($X).'</textarea>';else{$Sc=(!preg_match('~int~',$l["type"])&&preg_match('~^(\d+)(,(\d+))?$~',$l["length"],$A)?((preg_match("~binary~",$l["type"])?2:1)*$A[1]+($A[3]?1:0)+($A[2]&&!$l["unsigned"]?1:0)):($Re[$l["type"]]?$Re[$l["type"]]+($l["unsigned"]?0:1):0));if($w=='sql'&&min_version(5.6)&&preg_match('~time~',$l["type"]))$Sc+=7;echo"<input".((!$ec||$p==="")&&preg_match('~(?<!o)int(?!er)~',$l["type"])&&!preg_match('~\[\]~',$l["full_type"])?" type='number'":"")." value='".h($X)."'".($Sc?" data-maxlength='$Sc'":"").(preg_match('~char|binary~',$l["type"])&&$Sc>20?" size='40'":"")."$c>";}echo$b->editHint($_GET["edit"],$l,$X);$Nb=0;foreach($Yb
as$x=>$W){if($x===""||!$W)break;$Nb++;}if($Nb)echo
script("mixin(qsl('td'), {onchange: partial(skipOriginal, $Nb), oninput: function () { this.onchange(); }});");}}function
process_input($l){global$b,$i;$t=bracket_escape($l["field"]);$p=$_POST["function"][$t];$X=$_POST["fields"][$t];if($l["type"]=="enum"){if($X==-1)return
false;if($X=="")return"NULL";return+$X;}if($l["auto_increment"]&&$X=="")return
null;if($p=="orig")return(preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?idf_escape($l["field"]):false);if($p=="NULL")return"NULL";if($l["type"]=="set")return
array_sum((array)$X);if($p=="json"){$p="";$X=json_decode($X,true);if(!is_array($X))return
false;return$X;}if(preg_match('~blob|bytea|raw|file~',$l["type"])&&ini_bool("file_uploads")){$Kb=get_file("fields-$t");if(!is_string($Kb))return
false;return$i->quoteBinary($Kb);}return$b->processInput($l,$X,$p);}function
fields_from_edit(){global$i;$L=array();foreach((array)$_POST["field_keys"]as$x=>$W){if($W!=""){$W=bracket_escape($W);$_POST["function"][$W]=$_POST["field_funs"][$x];$_POST["fields"][$W]=$_POST["field_vals"][$x];}}foreach((array)$_POST["fields"]as$x=>$W){$D=bracket_escape($x,1);$L[$D]=array("field"=>$D,"privileges"=>array("insert"=>1,"update"=>1),"null"=>1,"auto_increment"=>($x==$i->primary),);}return$L;}function
search_tables(){global$b,$f;$_GET["where"][0]["val"]=$_POST["query"];$Zd="<ul>\n";foreach(table_status('',true)as$S=>$T){$D=$b->tableName($T);if(isset($T["Engine"])&&$D!=""&&(!$_POST["tables"]||in_array($S,$_POST["tables"]))){$K=$f->query("SELECT".limit("1 FROM ".table($S)," WHERE ".implode(" AND ",$b->selectSearchProcess(fields($S),array())),1));if(!$K||$K->fetch_row()){$Ad="<a href='".h(ME."select=".urlencode($S)."&where[0][op]=".urlencode($_GET["where"][0]["op"])."&where[0][val]=".urlencode($_GET["where"][0]["val"]))."'>$D</a>";echo"$Zd<li>".($K?$Ad:"<p class='error'>$Ad: ".error())."\n";$Zd="";}}}echo($Zd?"<p class='message'>".'No tables.':"</ul>")."\n";}function
dump_headers($mc,$Vc=false){global$b;$L=$b->dumpHeaders($mc,$Vc);$nd=$_POST["output"];if($nd!="text")header("Content-Disposition: attachment; filename=".$b->dumpFilename($mc).".$L".($nd!="file"&&preg_match('~^[0-9a-z]+$~',$nd)?".$nd":""));session_write_close();ob_flush();flush();return$L;}function
dump_csv($M){foreach($M
as$x=>$W){if(preg_match('~["\n,;\t]|^0|\.\d*0$~',$W)||$W==="")$M[$x]='"'.str_replace('"','""',$W).'"';}echo
implode(($_POST["format"]=="csv"?",":($_POST["format"]=="tsv"?"\t":";")),$M)."\r\n";}function
apply_sql_function($p,$d){return($p?($p=="unixepoch"?"DATETIME($d, '$p')":($p=="count distinct"?"COUNT(DISTINCT ":strtoupper("$p("))."$d)"):$d);}function
get_temp_dir(){$L=ini_get("upload_tmp_dir");if(!$L){if(function_exists('sys_get_temp_dir'))$L=sys_get_temp_dir();else{$n=@tempnam("","");if(!$n)return
false;$L=dirname($n);unlink($n);}}return$L;}function
file_open_lock($n){$o=@fopen($n,"r+");if(!$o){$o=@fopen($n,"w");if(!$o)return;chmod($n,0660);}flock($o,LOCK_EX);return$o;}function
file_write_unlock($o,$cb){rewind($o);fwrite($o,$cb);ftruncate($o,strlen($cb));flock($o,LOCK_UN);fclose($o);}function
password_file($Wa){$n=get_temp_dir()."/adminer.key";$L=@file_get_contents($n);if($L||!$Wa)return$L;$o=@fopen($n,"w");if($o){chmod($n,0660);$L=rand_string();fwrite($o,$L);fclose($o);}return$L;}function
rand_string(){return
md5(uniqid(mt_rand(),true));}function
select_value($W,$z,$l,$Ae){global$b;if(is_array($W)){$L="";foreach($W
as$yc=>$V)$L.="<tr>".($W!=array_values($W)?"<th>".h($yc):"")."<td>".select_value($V,$z,$l,$Ae);return"<table cellspacing='0'>$L</table>";}if(!$z)$z=$b->selectLink($W,$l);if($z===null){if(is_mail($W))$z="mailto:$W";if(is_url($W))$z=$W;}$L=$b->editVal($W,$l);if($L!==null){if(!is_utf8($L))$L="\0";elseif($Ae!=""&&is_shortable($l))$L=shorten_utf8($L,max(0,+$Ae));else$L=h($L);}return$b->selectVal($L,$z,$l,$W);}function
is_mail($ub){$pa='[-a-z0-9!#$%&\'*+/=?^_`{|}~]';$nb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';$I="$pa+(\\.$pa+)*@($nb?\\.)+$nb";return
is_string($ub)&&preg_match("(^$I(,\\s*$I)*\$)i",$ub);}function
is_url($R){$nb='[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])';return
preg_match("~^(https?)://($nb?\\.)+$nb(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i",$R);}function
is_shortable($l){return
preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~',$l["type"]);}function
count_rows($S,$Z,$v,$q){global$w;$J=" FROM ".table($S).($Z?" WHERE ".implode(" AND ",$Z):"");return($v&&($w=="sql"||count($q)==1)?"SELECT COUNT(DISTINCT ".implode(", ",$q).")$J":"SELECT COUNT(*)".($v?" FROM (SELECT 1$J GROUP BY ".implode(", ",$q).") x":$J));}function
slow_query($J){global$b,$Je,$i;$h=$b->database();$De=$b->queryTimeout();$ge=$i->slowQuery($J,$De);if(!$ge&&support("kill")&&is_object($g=connect())&&($h==""||$g->select_db($h))){$_c=$g->result(connection_id());echo'<script',nonce(),'>
var timeout = setTimeout(function () {
	ajax(\'',js_escape(ME),'script=kill\', function () {
	}, \'kill=',$_c,'&token=',$Je,'\');
}, ',1000*$De,');
</script>
';}else$g=null;ob_flush();flush();$L=@get_key_vals(($ge?$ge:$J),$g,false);if($g){echo
script("clearTimeout(timeout);");ob_flush();flush();}return$L;}function
get_token(){$Id=rand(1,1e6);return($Id^$_SESSION["token"]).":$Id";}function
verify_token(){list($Je,$Id)=explode(":",$_POST["token"]);return($Id^$_SESSION["token"])==$Je;}function
lzw_decompress($za){$lb=256;$_a=8;$Ka=array();$Pd=0;$Qd=0;for($r=0;$r<strlen($za);$r++){$Pd=($Pd<<8)+ord($za[$r]);$Qd+=8;if($Qd>=$_a){$Qd-=$_a;$Ka[]=$Pd>>$Qd;$Pd&=(1<<$Qd)-1;$lb++;if($lb>>$_a)$_a++;}}$kb=range("\0","\xFF");$L="";foreach($Ka
as$r=>$Ja){$tb=$kb[$Ja];if(!isset($tb))$tb=$mf.$mf[0];$L.=$tb;if($r)$kb[]=$mf.$tb[0];$mf=$tb;}return$L;}function
on_help($Pa,$fe=0){return
script("mixin(qsl('select, input'), {onmouseover: function (event) { helpMouseover.call(this, event, $Pa, $fe) }, onmouseout: helpMouseout});","");}function
edit_form($S,$m,$M,$Ze){global$b,$w,$Je,$k;$we=$b->tableName(table_status1($S,true));page_header(($Ze?'Edit':'Insert'),$k,array("select"=>array($S,$we)),$we);$b->editRowPrint($S,$m,$M,$Ze);if($M===false)echo"<p class='error'>".'No rows.'."\n";echo'<form action="" method="post" enctype="multipart/form-data" id="form">
';if(!$m)echo"<p class='error'>".'You have no privileges to update this table.'."\n";else{echo"<table cellspacing='0' class='layout'>".script("qsl('table').onkeydown = editingKeydown;");foreach($m
as$D=>$l){echo"<tr><th>".$b->fieldName($l);$gb=$_GET["set"][bracket_escape($D)];if($gb===null){$gb=$l["default"];if($l["type"]=="bit"&&preg_match("~^b'([01]*)'\$~",$gb,$Ld))$gb=$Ld[1];}$X=($M!==null?($M[$D]!=""&&$w=="sql"&&preg_match("~enum|set~",$l["type"])?(is_array($M[$D])?array_sum($M[$D]):+$M[$D]):(is_bool($M[$D])?+$M[$D]:$M[$D])):(!$Ze&&$l["auto_increment"]?"":(isset($_GET["select"])?false:$gb)));if(!$_POST["save"]&&is_string($X))$X=$b->editVal($X,$l);$p=($_POST["save"]?(string)$_POST["function"][$D]:($Ze&&preg_match('~^CURRENT_TIMESTAMP~i',$l["on_update"])?"now":($X===false?null:($X!==null?'':'NULL'))));if(!$_POST&&!$Ze&&$X==$l["default"]&&preg_match('~^[\w.]+\(~',$X))$p="SQL";if(preg_match("~time~",$l["type"])&&preg_match('~^CURRENT_TIMESTAMP~i',$X)){$X="";$p="now";}input($l,$X,$p);echo"\n";}if(!support("table"))echo"<tr>"."<th><input name='field_keys[]'>".script("qsl('input').oninput = fieldChange;")."<td class='function'>".html_select("field_funs[]",$b->editFunctions(array("null"=>isset($_GET["select"]))))."<td><input name='field_vals[]'>"."\n";echo"</table>\n";}echo"<p>\n";if($m){echo"<input type='submit' value='".'Save'."'>\n";if(!isset($_GET["select"])){echo"<input type='submit' name='insert' value='".($Ze?'Save and continue edit':'Save and insert next')."' title='Ctrl+Shift+Enter'>\n",($Ze?script("qsl('input').onclick = function () { return !ajaxForm(this.form, '".'Saving'."‚Ä¶', this); };"):"");}}echo($Ze?"<input type='submit' name='delete' value='".'Delete'."'>".confirm()."\n":($_POST||!$m?"":script("focus(qsa('td', qs('#form'))[1].firstChild);")));if(isset($_GET["select"]))hidden_fields(array("check"=>(array)$_POST["check"],"clone"=>$_POST["clone"],"all"=>$_POST["all"]));echo'<input type="hidden" name="referer" value="',h(isset($_POST["referer"])?$_POST["referer"]:$_SERVER["HTTP_REFERER"]),'">
<input type="hidden" name="save" value="1">
<input type="hidden" name="token" value="',$Je,'">
</form>
';}if(isset($_GET["file"])){if($_SERVER["HTTP_IF_MODIFIED_SINCE"]){header("HTTP/1.1 304 Not Modified");exit;}header("Expires: ".gmdate("D, d M Y H:i:s",time()+365*24*60*60)." GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: immutable");if($_GET["file"]=="favicon.ico"){header("Content-Type: image/x-icon");echo
lzw_decompress("\0\0\0` \0Ñ\0\n @\0¥CÑË\"\0`E„Q∏‡ˇá?¿tvM'îJd¡d\\åb0\0ƒ\"ô¿f”à§Ós5õœÁ—AùXPaJì0Ñ•ë8Ñ#RäT©ëz`à#.©«cÌX√˛»Ä?¿-\0°Im?†.´M∂Ä\0»Ø(Ãâ˝¿/(%å\0");}elseif($_GET["file"]=="default.css"){header("Content-Type: text/css; charset=utf-8");echo
lzw_decompress("\n1ÃáìŸåﬁl7úáB1Ñ4vb0òÕfsëºÍn2BÃ—±Ÿòﬁn:á#(ºb.\rDc)»»a7EÑë§¬l¶√±îËi1Ãésò¥Á-4ôáf”	»Œi7Ü≥π§»t4Ö¶”yËZf4ù∞iñAT´VVêÈf:œ¶,:1¶Q›ºÒb2`«#˛>:7GÔó1—ÿ“s∞ôLóXD*bv<‹å#£e@÷:4Áß!foê∑∆t:<•‹Âíæôo‚‹\ni√≈',Èªa_§:πiÔÖ¥¡Bv¯|N˚4.5NfÅi¢vp–h∏∞l®Í°÷ö‹O¶ÅâÓ= £OFQ–ƒk\$•”iıô¿¬d2T„°p‡ 6Ñã˛á°-ÿZÄéÉ†ﬁ6Ω£Äh:¨aÃ,é£ÎÓ2ç#8–ê±#íò6n‚ÓÜÒJà¢h´tÖå±ä‰4O42ÙΩokﬁæ*r†©Ä@p@Ü!ƒæœ√Ù˛?–6¿âr[çL¡ã:2Bàjß!HbÛ√P‰=!1Vâ\"à≤0Öø\nS∆∆œD7√ÏD⁄õ√C!Ü!õ‡¶G åß »+í=tCÊ©.C§¿:+» =™™∫≤°±Â%™cÌ1MR/îE»í4Ñ©†2∞‰±†„`¬8(·”π[W‰—=âySÅb∞=÷-‹πBS+…Ø»‹˝•¯@pL4Yd„Ñqä¯„¶Í¢6£3ƒ¨Ø∏Ac‹åËŒ®åkÇ[&>ˆï®Z¡pkm]óu-c:ÿ∏àNtÊŒ¥p“ùåä8Ë=ø#ò·[.‹ﬁØç~†çÅmÀyáPP·|I÷õ˘¿ÏQ™9v[ñQïÑ\nñŸrÙ'gá+ê·T—2Ö≠V¡ız‰4ç£8˜è(	æEy*#j¨2]≠ïR“¡ë•)É¿[N≠R\$ä<>:Û≠>\$;ñ>†Ã\rªÑŒHÕ√T»\nw°N Âwÿ£¶Ï<ÔÀGw‡ˆˆπ\\YÛ_†Rt^å>é\r}åŸS\rzÈ4=µ\nLî%J„ã\",Z†8∏ûôêi˜0u©?®˚—Ù°s3#®Ÿâ†:Û¶˚ç„Ωñ»ﬁE]x›“Ås^8é£K^…˜*0—ﬁwﬁ‡»ﬁ~è„ˆ:Ì—iÿ˛èv2wΩˇ±˚^7ê„Ú7£c›—u+U%é{P‹*4ÃºÈLX./!ºâ1C≈ﬂqx!Hπ„Fd˘≠L®§®ƒ†œ`6ÎË5ÆôfÄ∏ƒÜ®=H¯l åV1ìõ\0a2◊;Å‘6Ü‡ˆ˛_Ÿáƒ\0&ÙZ‹S†d)KE'íÄnµê[X©≥\0Z…ä‘F[Pëﬁò@‡ﬂ!âÒY¬,`…\"⁄∑Å¬0Ee9yF>À‘9b∫ñåÊF5:¸àî\0}ƒ¥äá(\$û”áÎÄ37Hˆ£Ë MæA∞≤6Rï˙{Mq›7G†⁄CôCÍm2¢(åCt>[Ï-t¿/&Cõ]ÍetGÙÃ¨4@r>«¬Â<öSqï/Â˙îQÎçhmçö¿–∆Ù„ÙùL¿‹#ËÙKÀ|ÆôÑ6fKP›\r%t‘”V=\"†SH\$ù} ∏Å)w°,W\0F≥™u@ÿb¶9Ç\rr∞2√#¨DåîXÉ≥⁄yOI˘>ªÖnÅÜ«¢%„˘ê'ã›_¡Ät\rœÑzƒ\\1òhlº]Q5Mp6kÜ–ƒqh√\$£H~Õ|“›!*4åÒÚ€`SÎ˝≤S tÌPP\\g±Ë7á\n-ä:Ë¢™p¥ïîàlãBû¶Óî7”®cÉ(wO0\\:ï–wî¡ùp4àìÚ{T⁄˙jO§6H√ä∂r’•êq\n¶…%%∂y']\$ÇîaëZ”.fc’q*-ÍFW∫˙kçÑzÉ∞µjëé∞lg·å:á\$\"ﬁNº\r#…d‚√Ç¬ˇ–sc·¨Ã†ÑÉ\"j™\r¿∂ñ¶à’íºPhã1/ÇúDA)†≤›[¿kn¡p76¡Y¥âR{·M§P˚∞Ú@\n-∏a∑6˛ﬂ[ªzJH,ñdl†B£hêo≥çÏÚ¨+á#Dr^µ^µŸeöºEΩΩñ ƒúaPâÙıJG£z‡ÒtÒ†2«XŸ¢¥¡øV∂◊ﬂ‡ﬁ»≥â—B_%K=E©∏bÂºæﬂ¬ßkU(.!‹Æ8∏ú¸…I.@éKÕxn˛¨¸:√PÛ32´îmÌH		C*Ï:v‚T≈\nRπÉïµã0u¬ÌÉÊÓ“ß]ŒØòäîP/µJQd•{Lñﬁ≥:Y¡è2bºúT Òù 3”4Üó‰cÍ•V=êøÜL4Œ–rƒ!ﬂBY≥6Õ≠MeLä™‹Áúˆ˘i¿o–9< Gî§∆ï–ôMhm^ØU€N¿å∑ÚTr5HiMî/¨nÉÌù≥T†ç[-<__Ó3/Xr(<áØäÜÆ…ÙìÃu“ñGNX20Â\r\$^áç:'9Ë∂OÖÌ;◊kèºÜµf†ñN'a∂î«≠b≈,ÀV§ÙÖ´1µÔHI!%6@˙œ\$“EG⁄ú¨1ù(mU™ÂÖr’ΩÔﬂÂ`°–iN+√úÒ)öú‰0lÿ“f0√Ω[U‚¯V Ë-:I^†ò\$ÿs´b\reáëug…h™~9€ﬂàùbòµÙ¬»f‰+0¨‘ hXr›¨©!\$óe,±w+Ñ˜åÎå3ÜÃ_‚AÖkö˘\nk√rı õcuWdYˇ\\◊={.Ûƒçòê¢gªâp8út\rRZøvçJ:≤>˛£Y|+≈@¿áÉ€Cêt\rÄÅjtÅΩ6≤%¬?‡Ù«éÒí>˘/•Õ«Œ9F`◊ï‰Úv~K§ê·ˆ—R–WãzëÍlm™wL«9Yï*q¨xƒzÒËSeÆ›õ≥Ë˜£~öD‡Õ·ñ˜ùxòæÎ…üi7ï2ƒ¯—O›ªí˚_{Ò˙53‚˙têòõ_üız‘3˘d)ãCØ¬\$?K”™PÅ%œœT&˛ò&\0P◊NAé^≠~¢É†p∆ ˆœúì‘ı\r\$ﬁÔ–÷Ïb*+D6Í∂¶œàﬁÌJ\$(»olﬁÕh&îÏKBS>∏ãˆ;z∂¶x≈oz>Ìú⁄oƒZ\n ã[œvıÇÀ»úµ∞2ıOxŸêV¯0f˚Ä˙Øﬁ2Bl…bk–6ZkµhXcdÍ0*¬KT‚ØH=≠ïœÄëp0älVÈıË‚\rºå•ném¶Ô)(è ˙");}elseif($_GET["file"]=="functions.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress("f:õågCIº‹\n8ú≈3)∞À7úÖÜ81– x:\nOg#)–Ír7\n\"ÜË¥`¯|2ÃgSiñH)N¶Së‰ß\ráù\"0πƒ@‰)ü`(\$s6O!”ËúV/=ùå' T4Ê=ÑòiSòç6IO†G#“X∑VCç∆s°†Z1.–hp8,≥[¶H‰µ~Czß…Â2πlæc3öÕÈs£ëŸIÜb‚4\nÈF8T‡ÜIò›©U*fzπ‰r0ûE∆Å¿ÿyé∏ÒféY.:ÊÉIå (ÿc∑·Œã!ç_lôÌ^∑^(∂öN{Sñì)rÀq¡YìñlŸ¶3ä3⁄\nò+G•”Íy∫ÌÜÀi∂¬ÓxV3w≥uh„^rÿ¿∫¥a€î˙πçcÿË\rì®Î(.¬à∫ÅCh“<\r)Ë—£°`Ê7£ÌÚ43'm5å£»\nÅP‹:2£Pª™éãq Úˇ≈Cì}ƒ´à˙ ¡Í38ãBÿ0éhRâ»r(ú0•°b\\0åHr44å¡Bç!°p«\$érZZÀ2‹â.…É(\\é5√|\nC(Œ\"èÄPÖ¯.ç–NÃRT Œì¿Ê>ÅHNÖÅ8HP·\\¨7Jp~Ñ‹˚2%°–OC®1„.ÉßC8ŒáH»Ú*àj∞Ö·˜S(π/°Ï¨6KUú á°<2âpOIÑÙ’`ç‘‰‚≥àdOÅH†ﬁ5ç-¸∆4å„pX25-“¢Ú€à∞z7£∏\"(∞P†\\32:]U⁄ËÌ‚ﬂÖ!]∏<∑A€€§í–ﬂi⁄∞ãl\r‘\0v≤Œ#J8´œwmûÌ…§®<ä…†Ê¸%m;p#„`XùDå¯˜iZç¯N0åêï»9¯®Âç†¡Ë`ÖéwJçDøæ2“9tå¢*¯ŒyÏÀNiIh\\9∆’Ë–:ÉÄÊ·xÔ≠µyl*ö»àŒÊY†‹á¯Í8íW≥‚?µéÅﬁõ3Ÿ !\"6Âõn[¨ \r≠*\$∂∆ßænzx∆9\rÏ|*3◊£pﬁÔª∂û:(p\\;‘Àmz¢¸ß9Û–—¬å¸8NÖ¡êj2çΩ´Œ\r…HÓH&å≤(√zÑ¡7i€k£ ãä§Çc§ãeÚû˝ßtúÃÃ2:SHÛ»†√/)ñxﬁ@ÈÂtâri9•ΩıÎú8œ¿ÀÔy“∑Ω∞éVƒ+^W⁄¶≠¨kZÊYól∑ £ÅÅå4÷»∆ã™∂¿¨Ç\\E»{Ó7\0πpÜÄïDÄÑiî-TÊ˛⁄˚0l∞%=¡†–ÀÉ9(Ñ5\n\nÄn,4á\0Ëa}‹É.∞ˆRsÔÇ™\02B\\€b1üS±\0003,‘XPHJspÂdìKÉ CA!∞2*Wü‘Ò⁄2\$‰+¬f^\nÑ1åÅ¥ÚzEÉ Iv§\\‰ú2…†.*A∞ôîE(d±·∞√bÍ¬‹Ñê∆9áÇ‚Ä¡Dhê&≠™?ƒH∞sèQò2íx~n√ÅJãT2˘&„‡eRúΩôG“QéêTwÍ›ëªıPà‚„\\†)6¶Ù‚ú¬Úsh\\3®\0R	¿'\r+*;RH‡.ì!—[Õ'~≠%t< Áp‹K#¬ëÊ!ÒlﬂÃLeå≥úŸ,ƒ¿Æ&·\$	¡Ω`îñCXöâ”Ü0÷≠Âº˚≥ƒ:MÈh	Á⁄úG‰—!&3†DÅ<!Ëê23Ñ√?h§J©e ⁄h·\r°mïòNi∏£¥éíÜ NÿHl7°ÆvÇÍWIÂ.¥¡-”5÷ßeyè\rEJ\ni*º\$@⁄RU0,\$UøEÜ¶‘‘¬™u)@(tŒSJk·p!Ä~≠Ç‡d`Ã>Øï\n√;#\rp9Üj…π‹]&Nc(rÄàïTQU™ΩS∑⁄\08n`´óyïb§≈ûL‹O5ÇÓ,§Úûë>éÇÜx‚‚±f‰¥í‚ÿê+Åñ\"—IÄ{kM»[\r%∆[	§eÙa‘1! ËˇÌ≥‘Æ©F@´b)Rü£72àÓ0°\nW®ô±L≤‹ú“Ætd’+ÅÌ‹0wgl¯0n@ÚÍ…¢’iÌM´É\nAßM5nÏ\$E≥◊±N€·l©›ü◊Ï%™1 A‹˚∫˙˜›kÒrÓiFB˜œ˘ol,muNx-Õ_†÷§C( ÅêfÈl\r1p[9x(i¥B“ñ≤€zQl¸∫8C‘	¥©XU Tb£›I›`ïp+V\0Óã—;ãCbŒ¿XÒ+œíçsÔ¸]H˜“[·kãx¨G*ÙÜè]∑awn˙!≈6ÇÚ‚€–mSÌæìIﬁÕKÀ~/ù”•7ﬁ˘eeN…Úç™S´/;dÂAÜ>}l~ûœÍ ®%^¥fÁÿ¢p⁄úDEÓ√a∑Çt\nx=√k–éÑ*d∫ÍTó∫¸˚j2ü…júù\në†… ,òe=ëÜM84Ù˚‘aïj@ÓT√sè‘‰nf©›\nÓ6™\rdúº0ﬁÌÙYä'%‘ìÌﬁ~	Å“®Ü<÷ÀñAÓãñHøGÇÅ8ÒøùŒÉ\$z´{∂ª≤u2*Ü‡añ¿>ª(wåK.bPÇ{ÖÉo˝î¬¥´zµ#Î2ˆ8=…8>™§≥A,∞e∞¿Ö+ÏCËßxı*√·“-b=máôü,ãaí√lzkùÅÔ\$Wı,êmèJiÊ ß·˜Å+ãË˝0∞[Øˇ.R sK˘«‰XÁ›ZLÀÁ2ê`Ã(ÔC‡vZ°‹›¿∂Ë\$Å◊π,ÂD?H±÷NxXÙÛ)íÓéM®â\$Û,çÕ*\n—£\$<qˇ≈üh!øπSì‚É¿üxsA!ò:¥K•¡}¡≤ì˘¨£úR˛öA2k∑Xép\n<˜˛¶˝ÎlÏßŸ3Ø¯¶»ïVV¨}£g&Y›ç!Ü+Û;<∏Y«ÛüYE3r≥ŸéÒõCÌo5¶≈˘¢’≥œkk˛Ö¯∞÷€£´œt˜íU¯Ö≠)˚[˝ﬂ¡Ó}Ôÿu¥´lÁ¢:Dü¯+œè _o„‰h140÷· 0¯Øb‰Kò„¨í†ˆ˛ÈªlG™Ñ#™ö©ÍéÜ¶©Ï|UdÊ∂IK´Í¬7‡^Ï‡∏@∫ÆO\0H≈Hiä6\rá€©‹\\cg\0ˆ„Î2éBƒ*e‡ê\nÄö	Özrê!ênWz&ê {Hñ'\$X †w@“8ÎDGr*Îƒ›HÂ'p#éƒÆÄ¶‘\nd¸Ä˜,Ù•ó,¸;g~Ø\0–#ÄÃé≤Eè¬\r÷I`úÓ'É%E“.†]` –õÖÓ%&–Óm∞˝\r‚ﬁ%4SÑv#\n†ûfH\$%Î-¬#≠∆—qB‚ÌÊ†¿¬Q-Ùc2äßÇ&¬¿Ã]‡ô Ëqh\rÒl]‡Æs†–—h‰7±n#±ÇÇ⁄-‡jEØFrÁ§l&d¿ÿŸÂzÏF6∏êà¡\"†ûì|øß¢s@ﬂ±ÆÂz)0rp⁄è\0ÇX\0§ŸË|DL<!∞ÙoÑ*áD∂{.B<E™ãã0nB(Ô é|\r\nÏ^©ç‡ç h≥!Ç÷Ír\$ßí(^™~èËﬁ¬/pèq≤ÃB®≈Oöà˙,\\µ®#RRŒè%Î‰Õd–Hjƒ`¬†ÙÆÃ≠ VÂ bSídßiéEÇ¯Ôoh¥r<i/k\$-ü\$oîº+∆≈ãŒ˙l“ﬁO≥&ev∆íºi“jMPA'u'éŒí( M(h/+´ÚWDæSo∑.n∑.n∏ÏÍ(ú(\"≠¿ßhˆ&pÜ®/À/1DÃäÁjÂ®∏EËﬁ&‚¶Äè,'l\$/.,ƒd®ÖÇWÄbbO3ÛB≥sH†:J`!ì.Ä™Çá¿˚•†è,F¿—7(á»‘ø≥˚1älÂs ÷“éë≤ó≈¢q¢X\r¿öÆÉ~RÈ∞±`Æ“ûÛÆY*‰:R®˘rJ¥∑%Lœ+n∏\"à¯\r¶ŒÕáH!qbæ2‚Li±%”ﬁŒ®Wj#9”‘ObE.I:Ö6¡7\0À6+§%∞.»Öﬁ≥a7E8VSÂ?(DG®”≥BÎ%;Ú¨˘‘/<í¥˙•¿\r Ï¥>˚M¿∞@∂æÄH†Ds–∞Z[tH£Enx(å©R†xÒè˚@Ø˛GkjWî>Ã¬⁄#T/8Æc8ÈQ0ÀË_‘IIGIIí!•äYEdÀE¥^ètdÈth¬`DV!CÊ8é•\r≠¥übì3©!3‚@Ÿ33N}‚ZBÛ3	œ3‰30⁄‹M(Í>Ç }‰\\—tÍÇf†fåÀ‚I\rÆÄÛ337 X‘\"tdŒ,\nbtNO`P‚;≠‹ï“≠¿‘Ø\$\nÇûﬂ‰Z—≠5U5WUµ^ho˝‡ÊtŸPM/5K4Ej≥KQ&53GXìXx)“<5DÖè\r˚VÙ\nﬂr¢5b‹Ä\\J\">ßË1S\r[-¶ Du¿\r“‚ß√)00ÛYı»À¢∑k{\nµƒ#µﬁ\r≥^∑ã|Ëu‹ªUÂ_nÔU4…Uä~Yt”\rIö√@‰è≥ôR Û3:“uePMSË0TµwWØX»ÚÚD®Ú§KOU‹‡ïá;Uı\n†OYçÈYÕQ,M[\0˜_™DöÕ»W†æJ*Ï\rg(]‡®\r\"ZCâ©6uÍè+µYÛàY6√¥ê0™qı(ŸÛ8}êÛ3AX3T†h9j∂j‡fıMtÂPJbqMP5>è»¯∂©Yák%&\\Ç1d¢ÿE4¿ µYnê Ì\$<•U]”â1âmb÷∂ê^“ıö†Í\"NVÈﬂp∂Îpı±eM⁄ﬁ◊WÈ‹¢Ó\\‰)\n À\nf7\n◊2¥ır8ãó=Ek7tVöáµû7P¶∂L…Ìa6ÚÚv@'Ç6i‡Ôj&>±‚;≠„`“ˇa	\0p⁄®(µJ—Î)´\\ø™n˚Úƒ¨m\0º®2ÄÙeqJˆ≠PçÙtåÎ±fj¸¬\"[\0®∑Ü¢X,<\\åÓ∂◊‚˜Ê∑+mdÜÂ~‚‡öÖ—s%o∞¥mn◊),◊ÑÊ‘á≤\r4∂¬8\r±Œ∏◊mEÇH]Ç¶ò¸÷HW≠M0DÔﬂÄóÂ~èÀÅòKòÓE}¯∏¥‡|fÿ^ì‹◊\r>‘-z]2sÇxDòd[sátéS¢∂\0Qf-K`≠¢Çt‡ÿÑwTØ9ÄÊZÄ‡	¯\nB£9 Nbñ„<⁄B˛I5o◊oJÒp¿œJNdÂÀ\rçhﬁç√ê2ê\"‡xÊHC‡›çñ:ç¯˝9Yn16∆Ùzr+z±˘˛\\í˜ïúÙm ﬁ±T ˆÚ†˜@Y2lQ<2O+•%ìÕ.”Éh˘0AﬁÒ∏ä√Zãè2R¶¿1£ä/ØhH\r®XÖ»aNB&ß ƒM@÷[xåá Æ•Íñ‚8&L⁄VÕúv‡±*öj§€öGHÂ»\\ŸÆ	ô≤∂&s€\0Qö†\\\"Ëb†∞	‡ƒ\rBsõ…wùÇ	ùŸ·ûBN`ö7ßCo(Ÿ√‡®\n√®ùì®1ö9Ã*Eò ÒSÖ”Uê0U∫ tö'|îmô∞ﬁ?h[¢\$.#…5	 Â	pÑ‡yB‡@RÙ]£ÖÍ@|Ñß{ô¿ P\0xÙ/¶ w¢%§EsBdøßöCUö~O◊∑‡P‡@X‚]‘Öç®Z3®•1¶•{©eLYâ°å⁄ê¢\\í(*R`†	‡¶\nÖä‡é∫ÃQCF»*éππê‡Èú¨⁄pÜX|`N®Çæ\$Ä[Üâí@ÕU¢‡¶∂‡Z•`Zd\"\\\"ÖÇ¢£)´áIà:ËtöÏoDÊ\0[≤®‡±Ç-©ì†gÌ≥âôÆ*`hu%£,Äî¨„Iµ7ƒ´≤HÛµm§6ﬁ}Æ∫N÷Õ≥\$ªMµUYf&1˘é¿õe]pz•ß⁄I§≈m∂G/£ ∫w ‹!ï\\#5•4I•dπE¬hqÄÂ¶˜—¨kÁx|⁄k•qDöbÖz?ß∫â>˙Éæ:Üì[ËL“∆¨Z∞XöÆ:ûπÑ∑⁄ç«jﬂw5	∂YÅæ0 ©¬ì≠Ø\$\0C¢ÜdSg∏ÎÇ†{ù@î\n`û	¿√¸C ¢∑ªM∫µ‚ª≤# t}xŒNÑ˜∫á{∫€∞)Í˚CÉ FKZﬁjô¬\0PFYîB‰pFkñõ0<⁄> D<JEôög\rı.ì2ñ¸8ÈU@*Œ5fk™ÃJDÏ»…4çïTDU76…/¥ËØ@∑ÇK+Ñ√ˆJÆ∫√¬Ì@”=å‹WIOD≥85MöçN∫\$RÙ\0¯5®\r‡˘_™úÏEúÒœI´œ≥NÁl£“Ây\\Ùëà«qUÄ–Q˚†™\n@í®Ä€∫√pö¨®P€±´7‘ΩN\r˝R{*çqm›\$\0Rî◊‘ìä≈Âq–√à+U@ﬁB§ÁOf*ÜCÀ¨∫MCé‰`_ Ë¸ÚΩÀµNÍÊT‚5Ÿ¶C◊ª© ∏‡\\W√e&_Xå_ÿçhÂó¬∆Bú3¿å€%‹FW£˚Å|ôGﬁõ'≈[Ø≈Ç¿∞Ÿ’V†–#^\rÁ¶GRÄæòÄP±›FgÅ¢˚ÓØ¿Yi ˚•«z\n‚®ﬁ+ﬂ^/ì®ÄÇº•Ω\\ï6Ëﬂbºdmh◊‚@qÌç’Ah÷),J≠◊Wñ«cm˜em]é”èeœkZb0ﬂÂ˛ûÅYÒ]ymäËáfÿeπB;π”ÍO…¿wüapDW˚å…‹”{õ\0ò¿-2/bN¨s÷ΩﬁæRaìœÆh&qt\n\"’iˆRm¸hzœe¯Ü‡‹FS7µ–PPÚ‰ñ§‚‹:Bßà‚’sm∂≠Y d¸ﬁÚ7}3?*Çt˙ÚÈœlT⁄}ò~ÄÑèÄ‰=cû˝¨÷ﬁ«	û⁄3Ö;T≤Lﬁ5*	Ò~#µAïæÉëséx-7˜éf5`ÿ#\"N”b˜ØGòüãı@‹e¸[Ô¯Å§ÃsëòÄ∏-ßòM6ß£qqö hÄe5Ö\0“¢¿±˙*‡b¯IS‹…‹FŒÆ9}˝p”-¯˝`{˝±…ñkPò0T<Ñ©Z9‰0<’ö\r≠Ä;!√àg∫\r\nK‘\nïá\0¡∞*Ω\nb7(¿_∏@,Óe2\r¿]ñKÖ+\0…ˇp C\\—¢,0¨^ÓM–ßö∫©ì@ä;X\rï?\$\rájí+ˆ/¥¨BˆÊP†Ωâ˘®J{\"aÕ6ò‰âúπ|Â£\n\0ª‡\\5ìÅ–	156ˇÜ .›[¬UÿØ\0dË≤8YÁ:!—≤ë=∫¿X.≤uC™äåˆ!S∫∏áoÖp”B›¸€7∏≠≈Ø°Rh≠\\hãE=˙y:< :u≥Û2µ80ìsi¶üTsB€@\$ ÕÈ@«u	»Q∫ê¶.ÙÇT0M\\/ÍÄd+∆É\në°=‘∞då≈ÎA¢∏¢)\r@@¬h3ÄñŸ8.eZa|.‚7ùYk–c¿òÒñ'D#á®YÚ@Xçqñ=M°Ô44öB AM§ØdU\"ãHw4Ó(>Ç¨8®≤√C∏?e_`–≈X:ƒA9√∏ôÅÙp´G–‰áGy6Ω√FìXrâ°l˜1°ΩÿªêB¢√Ö9Rz©ıhBÑ{çûÄô\0ÎÂ^Ç√-‚0©%Dú5F\"\"‡⁄‹ ¬ô˙iƒ`ÀŸnAf® \"tDZ\"_‡V\$ü™!/ÖDÄ·öÜøµã¥àŸ¶°ÃÄF,25…jõTÎ·óy\0ÖNºx\rÁYl¶è#ë∆Eq\nÕ»B2ú\nÏ‡6∑Öƒ4”◊î!/¬\nÛÉâQ∏Ω*Æ;)bR∏Z0\0ƒCDoåÀûé48¿ï¥µá–eë\n„¶S%\\˙PIkêá(0¡åu/ôãG≤∆πäåº\\À}†4FpëûG˚_˜G?)g»otÅ∫[vû÷\0∞∏?b¿;™À`(ï€å‡∂NS)\n„x=Ë–+@Í‹7Éèj˙0èó,1√Özôì≠ç>0àâGc„LÖVXÙÉ±€ %¿Ö¡ÑQ+¯éÈo∆Fı»È‹∂–>Q-„cë⁄«lâ°≥§w‡Ãz5GëÍÇ@(hëc”Hı«r?àöNb˛@…®ˆ«¯∞Ólx3ãU`Ñrw™©‘U√‘Ùtÿ8‘=¿l#Úıèlˇ‰®â8•E\"åÉòôO6\nò¬1e£`\\hKfóV/–∑PaYKÁOÃ˝ Èè‡xë	âOjÑÛèr7•F;¥ÍÅBªëÍ£ÌÃíáº>Ê–¶≤V\rƒñƒ|©'Jµz´ºöî#íPB‰íY5\0NC§^\n~LrRí‘[ÃüR√¨Òg¿eZ\0xõ^ªi<Q„/)”%@ êíôfB≤Hf {%P‡\"\"Ωç¯@™˛ç)ÚíëìDE(iM2ÇSí*ÉyÚS¡\"‚Ò eÃí1å´◊ò\n4` ©>¶èQ*¶‹y∞nîíû•T‰u‘ù‚‰î—~%Å+WÅ≤XKãå£Q°[ îû‡lêPYy#DŸ¨D<´FL˙≥’@¡6']∆ãá˚\rFƒ`±!ï%\nè0êc–Ù¿À©%c8WrpGÉ.TúDoæUL2ÿ*È|\$¨:ËúròΩ@ÊÒË&“4ÅãHä> ë†ç%0*åZc(@‹]ÛÃQ:*¨ì‚(&\"xç'JO≥1π∫`>7	#Ÿ\"O4PX¸±î|B4ªÈâ[ òÈŸò\$nÔà1`ÙÍGSAı÷ÀAHª†\"Ü)‡©„S®˚fî…¶¡∫-\"ÀW˙+…ñ∫\0s-[îfoŸßÕD´êxÛÊ∏ıæ=Cö.ıì9≥≠ŒfÔ¿c¡\0¬ã7°?√ì95¥÷¶Z«0≠ÓfÏ≠®‡¯ÎH?R'q>o⁄ @aDüç˘G[;G¥DπBBdƒ°óq†ñ•2§|1πÏqô≤‰¿ŒÂ≤w<‹#™ßEYΩ^öß†≠Q\\Î[XÒÂË≈>?vÔû[ áÊäI…Õ— ÑôúÃg\0«)¥ÖÆgÖuå–g42j√∫'ÛéT‰êÑãÕvy,uí€DÜ=péH\\Éî^b‰ÏqÿÑƒit√≈XÖ¿£FP…@P˙•Täæi2#∞gÄóD·ÆôÒ%9ô@Ç");}elseif($_GET["file"]=="jush.js"){header("Content-Type: text/javascript; charset=utf-8");echo
lzw_decompress('');}else{header("Content-Type: image/gif");switch($_GET["file"]){case"plus.gif":echo'';break;case"cross.gif":echo'';break;case"up.gif":echo'';break;case"down.gif":echo'';break;case"arrow.gif":echo'';break;}}exit;}if($_GET["script"]=="version"){$o=file_open_lock(get_temp_dir()."/adminer.version");if($o)file_write_unlock($o,serialize(array("signature"=>$_POST["signature"],"version"=>$_POST["version"])));exit;}global$b,$f,$i,$ob,$rb,$zb,$k,$Yb,$bc,$aa,$sc,$w,$ba,$Cc,$ed,$ud,$pe,$fc,$Je,$Ne,$Re,$Ye,$ca;if(!$_SERVER["REQUEST_URI"])$_SERVER["REQUEST_URI"]=$_SERVER["ORIG_PATH_INFO"];if(!strpos($_SERVER["REQUEST_URI"],'?')&&$_SERVER["QUERY_STRING"]!="")$_SERVER["REQUEST_URI"].="?$_SERVER[QUERY_STRING]";if($_SERVER["HTTP_X_FORWARDED_PREFIX"])$_SERVER["REQUEST_URI"]=$_SERVER["HTTP_X_FORWARDED_PREFIX"].$_SERVER["REQUEST_URI"];$aa=($_SERVER["HTTPS"]&&strcasecmp($_SERVER["HTTPS"],"off"))||ini_bool("session.cookie_secure");@ini_set("session.use_trans_sid",false);if(!defined("SID")){session_cache_limiter("");session_name("adminer_sid");$rd=array(0,preg_replace('~\?.*~','',$_SERVER["REQUEST_URI"]),"",$aa);if(version_compare(PHP_VERSION,'5.2.0')>=0)$rd[]=true;call_user_func_array('session_set_cookie_params',$rd);session_start();}remove_slashes(array(&$_GET,&$_POST,&$_COOKIE),$Mb);if(function_exists("get_magic_quotes_runtime")&&get_magic_quotes_runtime())set_magic_quotes_runtime(false);@set_time_limit(0);@ini_set("zend.ze1_compatibility_mode",false);@ini_set("precision",15);function
get_lang(){return'en';}function
lang($Me,$bd=null){if(is_array($Me)){$xd=($bd==1?0:1);$Me=$Me[$xd];}$Me=str_replace("%d","%s",$Me);$bd=format_number($bd);return
sprintf($Me,$bd);}if(extension_loaded('pdo')){class
Min_PDO{var$_result,$server_info,$affected_rows,$errno,$error,$pdo;function
__construct(){global$b;$xd=array_search("SQL",$b->operators);if($xd!==false)unset($b->operators[$xd]);}function
dsn($pb,$U,$H,$E=array()){$E[PDO::ATTR_ERRMODE]=PDO::ERRMODE_SILENT;$E[PDO::ATTR_STATEMENT_CLASS]=array('Min_PDOStatement');try{$this->pdo=new
PDO($pb,$U,$H,$E);}catch(Exception$Cb){auth_error(h($Cb->getMessage()));}$this->server_info=@$this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION);}function
quote($R){return$this->pdo->quote($R);}function
query($J,$Se=false){$K=$this->pdo->query($J);$this->error="";if(!$K){list(,$this->errno,$this->error)=$this->pdo->errorInfo();if(!$this->error)$this->error='Unknown error.';return
false;}$this->store_result($K);return$K;}function
multi_query($J){return$this->_result=$this->query($J);}function
store_result($K=null){if(!$K){$K=$this->_result;if(!$K)return
false;}if($K->columnCount()){$K->num_rows=$K->rowCount();return$K;}$this->affected_rows=$K->rowCount();return
true;}function
next_result(){if(!$this->_result)return
false;$this->_result->_offset=0;return@$this->_result->nextRowset();}function
result($J,$l=0){$K=$this->query($J);if(!$K)return
false;$M=$K->fetch();return$M[$l];}}class
Min_PDOStatement
extends
PDOStatement{var$_offset=0,$num_rows;function
fetch_assoc(){return$this->fetch(PDO::FETCH_ASSOC);}function
fetch_row(){return$this->fetch(PDO::FETCH_NUM);}function
fetch_field(){$M=(object)$this->getColumnMeta($this->_offset++);$M->orgtable=$M->table;$M->orgname=$M->name;$M->charsetnr=(in_array("blob",(array)$M->flags)?63:0);return$M;}}}$ob=array();function
add_driver($s,$D){global$ob;$ob[$s]=$D;}class
Min_SQL{var$_conn;function
__construct($f){$this->_conn=$f;}function
select($S,$O,$Z,$q,$F=array(),$y=1,$G=0,$Ad=false){global$b,$w;$v=(count($q)<count($O));$J=$b->selectQueryBuild($O,$Z,$q,$F,$y,$G);if(!$J)$J="SELECT".limit(($_GET["page"]!="last"&&$y!=""&&$q&&$v&&$w=="sql"?"SQL_CALC_FOUND_ROWS ":"").implode(", ",$O)."\nFROM ".table($S),($Z?"\nWHERE ".implode(" AND ",$Z):"").($q&&$v?"\nGROUP BY ".implode(", ",$q):"").($F?"\nORDER BY ".implode(", ",$F):""),($y!=""?+$y:null),($G?$y*$G:0),"\n");$me=microtime(true);$L=$this->_conn->query($J);if($Ad)echo$b->selectQuery($J,$me,!$L);return$L;}function
delete($S,$Gd,$y=0){$J="FROM ".table($S);return
queries("DELETE".($y?limit1($S,$J,$Gd):" $J$Gd"));}function
update($S,$Q,$Gd,$y=0,$ae="\n"){$ef=array();foreach($Q
as$x=>$W)$ef[]="$x = $W";$J=table($S)." SET$ae".implode(",$ae",$ef);return
queries("UPDATE".($y?limit1($S,$J,$Gd,$ae):" $J$Gd"));}function
insert($S,$Q){return
queries("INSERT INTO ".table($S).($Q?" (".implode(", ",array_keys($Q)).")\nVALUES (".implode(", ",$Q).")":" DEFAULT VALUES"));}function
insertUpdate($S,$N,$_d){return
false;}function
begin(){return
queries("BEGIN");}function
commit(){return
queries("COMMIT");}function
rollback(){return
queries("ROLLBACK");}function
slowQuery($J,$De){}function
convertSearch($t,$W,$l){return$t;}function
value($W,$l){return(method_exists($this->_conn,'value')?$this->_conn->value($W,$l):(is_resource($W)?stream_get_contents($W):$W));}function
quoteBinary($Sd){return
q($Sd);}function
warnings(){return'';}function
tableHelp($D){}}class
Adminer{var$operators=array("<=",">=");var$_values=array();function
name(){return"<a href='https://www.adminer.org/editor/'".target_blank()." id='h1'>".'Editor'."</a>";}function
credentials(){return
array(SERVER,$_GET["username"],get_password());}function
connectSsl(){}function
permanentLogin($Wa=false){return
password_file($Wa);}function
bruteForceKey(){return$_SERVER["REMOTE_ADDR"];}function
serverName($P){}function
database(){global$f;if($f){$eb=$this->databases(false);return(!$eb?$f->result("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1)"):$eb[(information_schema($eb[0])?1:0)]);}}function
schemas(){return
schemas();}function
databases($Ob=true){return
get_databases($Ob);}function
queryTimeout(){return
5;}function
headers(){}function
csp(){return
csp();}function
head(){return
true;}function
css(){$L=array();$n="adminer.css";if(file_exists($n))$L[]=$n;return$L;}function
loginForm(){echo"<table cellspacing='0' class='layout'>\n",$this->loginFormField('username','<tr><th>'.'Username'.'<td>','<input type="hidden" name="auth[driver]" value="server"><input name="auth[username]" id="username" value="'.h($_GET["username"]).'" autocomplete="username" autocapitalize="off">'.script("focus(qs('#username'));")),$this->loginFormField('password','<tr><th>'.'Password'.'<td>','<input type="password" name="auth[password]" autocomplete="current-password">'."\n"),"</table>\n","<p><input type='submit' value='".'Login'."'>\n",checkbox("auth[permanent]",1,$_COOKIE["adminer_permanent"],'Permanent login')."\n";}function
loginFormField($D,$ic,$X){return$ic.$X;}function
login($Jc,$H){return
true;}function
tableName($ve){return
h($ve["Comment"]!=""?$ve["Comment"]:$ve["Name"]);}function
fieldName($l,$F=0){return
h(preg_replace('~\s+\[.*\]$~','',($l["comment"]!=""?$l["comment"]:$l["field"])));}function
selectLinks($ve,$Q=""){$a=$ve["Name"];if($Q!==null)echo'<p class="tabs"><a href="'.h(ME.'edit='.urlencode($a).$Q).'">'.'New item'."</a>\n";}function
foreignKeys($S){return
foreign_keys($S);}function
backwardKeys($S,$ue){$L=array();foreach(get_rows("SELECT TABLE_NAME, CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_SCHEMA = ".q($this->database())."
AND REFERENCED_TABLE_NAME = ".q($S)."
ORDER BY ORDINAL_POSITION",null,"")as$M)$L[$M["TABLE_NAME"]]["keys"][$M["CONSTRAINT_NAME"]][$M["COLUMN_NAME"]]=$M["REFERENCED_COLUMN_NAME"];foreach($L
as$x=>$W){$D=$this->tableName(table_status($x,true));if($D!=""){$Ud=preg_quote($ue);$ae="(:|\\s*-)?\\s+";$L[$x]["name"]=(preg_match("(^$Ud$ae(.+)|^(.+?)$ae$Ud\$)iu",$D,$A)?$A[2].$A[3]:$D);}else
unset($L[$x]);}return$L;}function
backwardKeysPrint($wa,$M){foreach($wa
as$S=>$va){foreach($va["keys"]as$Oa){$z=ME.'select='.urlencode($S);$r=0;foreach($Oa
as$d=>$W)$z.=where_link($r++,$d,$M[$W]);echo"<a href='".h($z)."'>".h($va["name"])."</a>";$z=ME.'edit='.urlencode($S);foreach($Oa
as$d=>$W)$z.="&set".urlencode("[".bracket_escape($d)."]")."=".urlencode($M[$W]);echo"<a href='".h($z)."' title='".'New item'."'>+</a> ";}}}function
selectQuery($J,$me,$Hb=false){return"<!--\n".str_replace("--","--><!-- ",$J)."\n(".format_time($me).")\n-->\n";}function
rowDescription($S){foreach(fields($S)as$l){if(preg_match("~varchar|character varying~",$l["type"]))return
idf_escape($l["field"]);}return"";}function
rowDescriptions($N,$Sb){$L=$N;foreach($N[0]as$x=>$W){if(list($S,$s,$D)=$this->_foreignColumn($Sb,$x)){$nc=array();foreach($N
as$M)$nc[$M[$x]]=q($M[$x]);$jb=$this->_values[$S];if(!$jb)$jb=get_key_vals("SELECT $s, $D FROM ".table($S)." WHERE $s IN (".implode(", ",$nc).")");foreach($N
as$C=>$M){if(isset($M[$x]))$L[$C][$x]=(string)$jb[$M[$x]];}}}return$L;}function
selectLink($W,$l){}function
selectVal($W,$z,$l,$md){$L=$W;$z=h($z);if(preg_match('~blob|bytea~',$l["type"])&&!is_utf8($W)){$L=lang(array('%d byte','%d bytes'),strlen($md));if(preg_match("~^(GIF|\xFF\xD8\xFF|\x89PNG\x0D\x0A\x1A\x0A)~",$md))$L="<img src='$z' alt='$L'>";}if(like_bool($l)&&$L!="")$L=(preg_match('~^(1|t|true|y|yes|on)$~i',$W)?'yes':'no');if($z)$L="<a href='$z'".(is_url($z)?target_blank():"").">$L</a>";if(!$z&&!like_bool($l)&&preg_match(number_type(),$l["type"]))$L="<div class='number'>$L</div>";elseif(preg_match('~date~',$l["type"]))$L="<div class='datetime'>$L</div>";return$L;}function
editVal($W,$l){if(preg_match('~date|timestamp~',$l["type"])&&$W!==null)return
preg_replace('~^(\d{2}(\d+))-(0?(\d+))-(0?(\d+))~','$1-$3-$5',$W);return$W;}function
selectColumnsPrint($O,$e){}function
selectSearchPrint($Z,$e,$u){$Z=(array)$_GET["where"];echo'<fieldset id="fieldset-search"><legend>'.'Search'."</legend><div>\n";$zc=array();foreach($Z
as$x=>$W)$zc[$W["col"]]=$x;$r=0;$m=fields($_GET["select"]);foreach($e
as$D=>$ib){$l=$m[$D];if(preg_match("~enum~",$l["type"])||like_bool($l)){$x=$zc[$D];$r--;echo"<div>".h($ib)."<input type='hidden' name='where[$r][col]' value='".h($D)."'>:",(like_bool($l)?" <select name='where[$r][val]'>".optionlist(array(""=>"",'no','yes'),$Z[$x]["val"],true)."</select>":enum_input("checkbox"," name='where[$r][val][]'",$l,(array)$Z[$x]["val"],($l["null"]?0:null))),"</div>\n";unset($e[$D]);}elseif(is_array($E=$this->_foreignKeyOptions($_GET["select"],$D))){if($m[$D]["null"])$E[0]='('.'empty'.')';$x=$zc[$D];$r--;echo"<div>".h($ib)."<input type='hidden' name='where[$r][col]' value='".h($D)."'><input type='hidden' name='where[$r][op]' value='='>: <select name='where[$r][val]'>".optionlist($E,$Z[$x]["val"],true)."</select></div>\n";unset($e[$D]);}}$r=0;foreach($Z
as$W){if(($W["col"]==""||$e[$W["col"]])&&"$W[col]$W[val]"!=""){echo"<div><select name='where[$r][col]'><option value=''>(".'anywhere'.")".optionlist($e,$W["col"],true)."</select>",html_select("where[$r][op]",array(-1=>"")+$this->operators,$W["op"]),"<input type='search' name='where[$r][val]' value='".h($W["val"])."'>".script("mixin(qsl('input'), {onkeydown: selectSearchKeydown, onsearch: selectSearchSearch});","")."</div>\n";$r++;}}echo"<div><select name='where[$r][col]'><option value=''>(".'anywhere'.")".optionlist($e,null,true)."</select>",script("qsl('select').onchange = selectAddRow;",""),html_select("where[$r][op]",array(-1=>"")+$this->operators),"<input type='search' name='where[$r][val]'></div>",script("mixin(qsl('input'), {onchange: function () { this.parentNode.firstChild.onchange(); }, onsearch: selectSearchSearch});"),"</div></fieldset>\n";}function
selectOrderPrint($F,$e,$u){$ld=array();foreach($u
as$x=>$pc){$F=array();foreach($pc["columns"]as$W)$F[]=$e[$W];if(count(array_filter($F,'strlen'))>1&&$x!="PRIMARY")$ld[$x]=implode(", ",$F);}if($ld){echo'<fieldset><legend>'.'Sort'."</legend><div>","<select name='index_order'>".optionlist(array(""=>"")+$ld,($_GET["order"][0]!=""?"":$_GET["index_order"]),true)."</select>","</div></fieldset>\n";}if($_GET["order"])echo"<div style='display: none;'>".hidden_fields(array("order"=>array(1=>reset($_GET["order"])),"desc"=>($_GET["desc"]?array(1=>1):array()),))."</div>\n";}function
selectLimitPrint($y){echo"<fieldset><legend>".'Limit'."</legend><div>";echo
html_select("limit",array("","50","100"),$y),"</div></fieldset>\n";}function
selectLengthPrint($Ae){}function
selectActionPrint($u){echo"<fieldset><legend>".'Action'."</legend><div>","<input type='submit' value='".'Select'."'>","</div></fieldset>\n";}function
selectCommandPrint(){return
true;}function
selectImportPrint(){return
true;}function
selectEmailPrint($vb,$e){if($vb){print_fieldset("email",'E-mail',$_POST["email_append"]);echo"<div>",script("qsl('div').onkeydown = partialArg(bodyKeydown, 'email');"),"<p>".'From'.": <input name='email_from' value='".h($_POST?$_POST["email_from"]:$_COOKIE["adminer_email"])."'>\n",'Subject'.": <input name='email_subject' value='".h($_POST["email_subject"])."'>\n","<p><textarea name='email_message' rows='15' cols='75'>".h($_POST["email_message"].($_POST["email_append"]?'{$'."$_POST[email_addition]}":""))."</textarea>\n","<p>".script("qsl('p').onkeydown = partialArg(bodyKeydown, 'email_append');","").html_select("email_addition",$e,$_POST["email_addition"])."<input type='submit' name='email_append' value='".'Insert'."'>\n";echo"<p>".'Attachments'.": <input type='file' name='email_files[]'>".script("qsl('input').onchange = emailFileChange;"),"<p>".(count($vb)==1?'<input type="hidden" name="email_field" value="'.h(key($vb)).'">':html_select("email_field",$vb)),"<input type='submit' name='email' value='".'Send'."'>".confirm(),"</div>\n","</div></fieldset>\n";}}function
selectColumnsProcess($e,$u){return
array(array(),array());}function
selectSearchProcess($m,$u){global$i;$L=array();foreach((array)$_GET["where"]as$x=>$Z){$La=$Z["col"];$hd=$Z["op"];$W=$Z["val"];if(($x<0?"":$La).$W!=""){$Ra=array();foreach(($La!=""?array($La=>$m[$La]):$m)as$D=>$l){if($La!=""||is_numeric($W)||!preg_match(number_type(),$l["type"])){$D=idf_escape($D);if($La!=""&&$l["type"]=="enum")$Ra[]=(in_array(0,$W)?"$D IS NULL OR ":"")."$D IN (".implode(", ",array_map('intval',$W)).")";else{$Be=preg_match('~char|text|enum|set~',$l["type"]);$X=$this->processInput($l,(!$hd&&$Be&&preg_match('~^[^%]+$~',$W)?"%$W%":$W));$Ra[]=$i->convertSearch($D,$W,$l).($X=="NULL"?" IS".($hd==">="?" NOT":"")." $X":(in_array($hd,$this->operators)||$hd=="="?" $hd $X":($Be?" LIKE $X":" IN (".str_replace(",","', '",$X).")")));if($x<0&&$W=="0")$Ra[]="$D IS NULL";}}}$L[]=($Ra?"(".implode(" OR ",$Ra).")":"1 = 0");}}return$L;}function
selectOrderProcess($m,$u){$qc=$_GET["index_order"];if($qc!="")unset($_GET["order"][1]);if($_GET["order"])return
array(idf_escape(reset($_GET["order"])).($_GET["desc"]?" DESC":""));foreach(($qc!=""?array($u[$qc]):$u)as$pc){if($qc!=""||$pc["type"]=="INDEX"){$dc=array_filter($pc["descs"]);$ib=false;foreach($pc["columns"]as$W){if(preg_match('~date|timestamp~',$m[$W]["type"])){$ib=true;break;}}$L=array();foreach($pc["columns"]as$x=>$W)$L[]=idf_escape($W).(($dc?$pc["descs"][$x]:$ib)?" DESC":"");return$L;}}return
array();}function
selectLimitProcess(){return(isset($_GET["limit"])?$_GET["limit"]:"50");}function
selectLengthProcess(){return"100";}function
selectEmailProcess($Z,$Sb){if($_POST["email_append"])return
true;if($_POST["email"]){$Yd=0;if($_POST["all"]||$_POST["check"]){$l=idf_escape($_POST["email_field"]);$re=$_POST["email_subject"];$B=$_POST["email_message"];preg_match_all('~\{\$([a-z0-9_]+)\}~i',"$re.$B",$Nc);$N=get_rows("SELECT DISTINCT $l".($Nc[1]?", ".implode(", ",array_map('idf_escape',array_unique($Nc[1]))):"")." FROM ".table($_GET["select"])." WHERE $l IS NOT NULL AND $l != ''".($Z?" AND ".implode(" AND ",$Z):"").($_POST["all"]?"":" AND ((".implode(") OR (",array_map('where_check',(array)$_POST["check"]))."))"));$m=fields($_GET["select"]);foreach($this->rowDescriptions($N,$Sb)as$M){$Nd=array('{\\'=>'{');foreach($Nc[1]as$W)$Nd['{$'."$W}"]=$this->editVal($M[$W],$m[$W]);$ub=$M[$_POST["email_field"]];if(is_mail($ub)&&send_mail($ub,strtr($re,$Nd),strtr($B,$Nd),$_POST["email_from"],$_FILES["email_files"]))$Yd++;}}cookie("adminer_email",$_POST["email_from"]);redirect(remove_from_uri(),lang(array('%d e-mail has been sent.','%d e-mails have been sent.'),$Yd));}return
false;}function
selectQueryBuild($O,$Z,$q,$F,$y,$G){return"";}function
messageQuery($J,$Ce,$Hb=false){return" <span class='time'>".@date("H:i:s")."</span><!--\n".str_replace("--","--><!-- ",$J)."\n".($Ce?"($Ce)\n":"")."-->";}function
editRowPrint($S,$m,$M,$Ze){}function
editFunctions($l){$L=array();if($l["null"]&&preg_match('~blob~',$l["type"]))$L["NULL"]='empty';$L[""]=($l["null"]||$l["auto_increment"]||like_bool($l)?"":"*");if(preg_match('~date|time~',$l["type"]))$L["now"]='now';if(preg_match('~_(md5|sha1)$~i',$l["field"],$A))$L[]=strtolower($A[1]);return$L;}function
editInput($S,$l,$c,$X){if($l["type"]=="enum")return(isset($_GET["select"])?"<label><input type='radio'$c value='-1' checked><i>".'original'."</i></label> ":"").enum_input("radio",$c,$l,($X||isset($_GET["select"])?$X:0),($l["null"]?"":null));$E=$this->_foreignKeyOptions($S,$l["field"],$X);if($E!==null)return(is_array($E)?"<select$c>".optionlist($E,$X,true)."</select>":"<input value='".h($X)."'$c class='hidden'>"."<input value='".h($E)."' class='jsonly'>"."<div></div>".script("qsl('input').oninput = partial(whisper, '".ME."script=complete&source=".urlencode($S)."&field=".urlencode($l["field"])."&value=');
qsl('div').onclick = whisperClick;",""));if(like_bool($l))return'<input type="checkbox" value="1"'.(preg_match('~^(1|t|true|y|yes|on)$~i',$X)?' checked':'')."$c>";$jc="";if(preg_match('~time~',$l["type"]))$jc='HH:MM:SS';if(preg_match('~date|timestamp~',$l["type"]))$jc='[yyyy]-mm-dd'.($jc?" [$jc]":"");if($jc)return"<input value='".h($X)."'$c> ($jc)";if(preg_match('~_(md5|sha1)$~i',$l["field"]))return"<input type='password' value='".h($X)."'$c>";return'';}function
editHint($S,$l,$X){return(preg_match('~\s+(\[.*\])$~',($l["comment"]!=""?$l["comment"]:$l["field"]),$A)?h(" $A[1]"):'');}function
processInput($l,$X,$p=""){if($p=="now")return"$p()";$L=$X;if(preg_match('~date|timestamp~',$l["type"])&&preg_match('(^'.str_replace('\$1','(?P<p1>\d*)',preg_replace('~(\\\\\\$([2-6]))~','(?P<p\2>\d{1,2})',preg_quote('$1-$3-$5'))).'(.*))',$X,$A))$L=($A["p1"]!=""?$A["p1"]:($A["p2"]!=""?($A["p2"]<70?20:19).$A["p2"]:gmdate("Y")))."-$A[p3]$A[p4]-$A[p5]$A[p6]".end($A);$L=($l["type"]=="bit"&&preg_match('~^[0-9]+$~',$X)?$L:q($L));if($X==""&&like_bool($l))$L="'0'";elseif($X==""&&($l["null"]||!preg_match('~char|text~',$l["type"])))$L="NULL";elseif(preg_match('~^(md5|sha1)$~',$p))$L="$p($L)";return
unconvert_field($l,$L);}function
dumpOutput(){return
array();}function
dumpFormat(){return
array('csv'=>'CSV,','csv;'=>'CSV;','tsv'=>'TSV');}function
dumpDatabase($h){}function
dumpTable($S,$qe,$xc=0){echo"\xef\xbb\xbf";}function
dumpData($S,$qe,$J){global$f;$K=$f->query($J,1);if($K){while($M=$K->fetch_assoc()){if($qe=="table"){dump_csv(array_keys($M));$qe="INSERT";}dump_csv($M);}}}function
dumpFilename($mc){return
friendly_url($mc);}function
dumpHeaders($mc,$Vc=false){$Fb="csv";header("Content-Type: text/csv; charset=utf-8");return$Fb;}function
importServerPath(){}function
homepage(){return
true;}function
navigation($Uc){global$ca;echo'<h1>
',$this->name(),' <span class="version">',$ca,'</span>
<a href="https://www.adminer.org/editor/#download"',target_blank(),' id="version">',(version_compare($ca,$_COOKIE["adminer_version"])<0?h($_COOKIE["adminer_version"]):""),'</a>
</h1>
';if($Uc=="auth"){$Nb=true;foreach((array)$_SESSION["pwds"]as$Y=>$ce){foreach($ce[""]as$U=>$H){if($H!==null){if($Nb){echo"<ul id='logins'>",script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});");$Nb=false;}echo"<li><a href='".h(auth_url($Y,"",$U))."'>".($U!=""?h($U):"<i>".'empty'."</i>")."</a>\n";}}}}else{$this->databasesPrint($Uc);if($Uc!="db"&&$Uc!="ns"){$T=table_status('',true);if(!$T)echo"<p class='message'>".'No tables.'."\n";else$this->tablesPrint($T);}}}function
databasesPrint($Uc){}function
tablesPrint($xe){echo"<ul id='tables'>",script("mixin(qs('#tables'), {onmouseover: menuOver, onmouseout: menuOut});");foreach($xe
as$M){echo'<li>';$D=$this->tableName($M);if(isset($M["Engine"])&&$D!="")echo"<a href='".h(ME).'select='.urlencode($M["Name"])."'".bold($_GET["select"]==$M["Name"]||$_GET["edit"]==$M["Name"],"select")." title='".'Select data'."'>$D</a>\n";}echo"</ul>\n";}function
_foreignColumn($Sb,$d){foreach((array)$Sb[$d]as$Rb){if(count($Rb["source"])==1){$D=$this->rowDescription($Rb["table"]);if($D!=""){$s=idf_escape($Rb["target"][0]);return
array($Rb["table"],$s,$D);}}}}function
_foreignKeyOptions($S,$d,$X=null){global$f;if(list($ze,$s,$D)=$this->_foreignColumn(column_foreign_keys($S),$d)){$L=&$this->_values[$ze];if($L===null){$T=table_status($ze);$L=($T["Rows"]>1000?"":array(""=>"")+get_key_vals("SELECT $s, $D FROM ".table($ze)." ORDER BY 2"));}if(!$L&&$X!==null)return$f->result("SELECT $D FROM ".table($ze)." WHERE $s = ".q($X));return$L;}}}$b=(function_exists('adminer_object')?adminer_object():new
Adminer);$ob=array("server"=>"MySQL")+$ob;if(!defined("DRIVER")){define("DRIVER","server");if(extension_loaded("mysqli")){class
Min_DB
extends
MySQLi{var$extension="MySQLi";function
__construct(){parent::init();}function
connect($P="",$U="",$H="",$db=null,$wd=null,$he=null){global$b;mysqli_report(MYSQLI_REPORT_OFF);list($kc,$wd)=explode(":",$P,2);$le=$b->connectSsl();if($le)$this->ssl_set($le['key'],$le['cert'],$le['ca'],'','');$L=@$this->real_connect(($P!=""?$kc:ini_get("mysqli.default_host")),($P.$U!=""?$U:ini_get("mysqli.default_user")),($P.$U.$H!=""?$H:ini_get("mysqli.default_pw")),$db,(is_numeric($wd)?$wd:ini_get("mysqli.default_port")),(!is_numeric($wd)?$wd:$he),($le?64:0));$this->options(MYSQLI_OPT_LOCAL_INFILE,false);return$L;}function
set_charset($Da){if(parent::set_charset($Da))return
true;parent::set_charset('utf8');return$this->query("SET NAMES $Da");}function
result($J,$l=0){$K=$this->query($J);if(!$K)return
false;$M=$K->fetch_array();return$M[$l];}function
quote($R){return"'".$this->escape_string($R)."'";}}}elseif(extension_loaded("mysql")&&!((ini_bool("sql.safe_mode")||ini_bool("mysql.allow_local_infile"))&&extension_loaded("pdo_mysql"))){class
Min_DB{var$extension="MySQL",$server_info,$affected_rows,$errno,$error,$_link,$_result;function
connect($P,$U,$H){if(ini_bool("mysql.allow_local_infile")){$this->error=sprintf('Disable %s or enable %s or %s extensions.',"'mysql.allow_local_infile'","MySQLi","PDO_MySQL");return
false;}$this->_link=@mysql_connect(($P!=""?$P:ini_get("mysql.default_host")),("$P$U"!=""?$U:ini_get("mysql.default_user")),("$P$U$H"!=""?$H:ini_get("mysql.default_password")),true,131072);if($this->_link)$this->server_info=mysql_get_server_info($this->_link);else$this->error=mysql_error();return(bool)$this->_link;}function
set_charset($Da){if(function_exists('mysql_set_charset')){if(mysql_set_charset($Da,$this->_link))return
true;mysql_set_charset('utf8',$this->_link);}return$this->query("SET NAMES $Da");}function
quote($R){return"'".mysql_real_escape_string($R,$this->_link)."'";}function
select_db($db){return
mysql_select_db($db,$this->_link);}function
query($J,$Se=false){$K=@($Se?mysql_unbuffered_query($J,$this->_link):mysql_query($J,$this->_link));$this->error="";if(!$K){$this->errno=mysql_errno($this->_link);$this->error=mysql_error($this->_link);return
false;}if($K===true){$this->affected_rows=mysql_affected_rows($this->_link);$this->info=mysql_info($this->_link);return
true;}return
new
Min_Result($K);}function
multi_query($J){return$this->_result=$this->query($J);}function
store_result(){return$this->_result;}function
next_result(){return
false;}function
result($J,$l=0){$K=$this->query($J);if(!$K||!$K->num_rows)return
false;return
mysql_result($K->_result,0,$l);}}class
Min_Result{var$num_rows,$_result,$_offset=0;function
__construct($K){$this->_result=$K;$this->num_rows=mysql_num_rows($K);}function
fetch_assoc(){return
mysql_fetch_assoc($this->_result);}function
fetch_row(){return
mysql_fetch_row($this->_result);}function
fetch_field(){$L=mysql_fetch_field($this->_result,$this->_offset++);$L->orgtable=$L->table;$L->orgname=$L->name;$L->charsetnr=($L->blob?63:0);return$L;}function
__destruct(){mysql_free_result($this->_result);}}}elseif(extension_loaded("pdo_mysql")){class
Min_DB
extends
Min_PDO{var$extension="PDO_MySQL";function
connect($P,$U,$H){global$b;$E=array(PDO::MYSQL_ATTR_LOCAL_INFILE=>false);$le=$b->connectSsl();if($le){if(!empty($le['key']))$E[PDO::MYSQL_ATTR_SSL_KEY]=$le['key'];if(!empty($le['cert']))$E[PDO::MYSQL_ATTR_SSL_CERT]=$le['cert'];if(!empty($le['ca']))$E[PDO::MYSQL_ATTR_SSL_CA]=$le['ca'];}$this->dsn("mysql:charset=utf8;host=".str_replace(":",";unix_socket=",preg_replace('~:(\d)~',';port=\1',$P)),$U,$H,$E);return
true;}function
set_charset($Da){$this->query("SET NAMES $Da");}function
select_db($db){return$this->query("USE ".idf_escape($db));}function
query($J,$Se=false){$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,!$Se);return
parent::query($J,$Se);}}}class
Min_Driver
extends
Min_SQL{function
insert($S,$Q){return($Q?parent::insert($S,$Q):queries("INSERT INTO ".table($S)." ()\nVALUES ()"));}function
insertUpdate($S,$N,$_d){$e=array_keys(reset($N));$zd="INSERT INTO ".table($S)." (".implode(", ",$e).") VALUES\n";$ef=array();foreach($e
as$x)$ef[$x]="$x = VALUES($x)";$se="\nON DUPLICATE KEY UPDATE ".implode(", ",$ef);$ef=array();$Gc=0;foreach($N
as$Q){$X="(".implode(", ",$Q).")";if($ef&&(strlen($zd)+$Gc+strlen($X)+strlen($se)>1e6)){if(!queries($zd.implode(",\n",$ef).$se))return
false;$ef=array();$Gc=0;}$ef[]=$X;$Gc+=strlen($X)+2;}return
queries($zd.implode(",\n",$ef).$se);}function
slowQuery($J,$De){if(min_version('5.7.8','10.1.2')){if(preg_match('~MariaDB~',$this->_conn->server_info))return"SET STATEMENT max_statement_time=$De FOR $J";elseif(preg_match('~^(SELECT\b)(.+)~is',$J,$A))return"$A[1] /*+ MAX_EXECUTION_TIME(".($De*1000).") */ $A[2]";}}function
convertSearch($t,$W,$l){return(preg_match('~char|text|enum|set~',$l["type"])&&!preg_match("~^utf8~",$l["collation"])&&preg_match('~[\x80-\xFF]~',$W['val'])?"CONVERT($t USING ".charset($this->_conn).")":$t);}function
warnings(){$K=$this->_conn->query("SHOW WARNINGS");if($K&&$K->num_rows){ob_start();select($K);return
ob_get_clean();}}function
tableHelp($D){$Lc=preg_match('~MariaDB~',$this->_conn->server_info);if(information_schema(DB))return
strtolower(($Lc?"information-schema-$D-table/":str_replace("_","-",$D)."-table.html"));if(DB=="mysql")return($Lc?"mysql$D-table/":"system-database.html");}}function
idf_escape($t){return"`".str_replace("`","``",$t)."`";}function
table($t){return
idf_escape($t);}function
connect(){global$b,$Re,$pe;$f=new
Min_DB;$Ya=$b->credentials();if($f->connect($Ya[0],$Ya[1],$Ya[2])){$f->set_charset(charset($f));$f->query("SET sql_quote_show_create = 1, autocommit = 1");if(min_version('5.7.8',10.2,$f)){$pe['Strings'][]="json";$Re["json"]=4294967295;}return$f;}$L=$f->error;if(function_exists('iconv')&&!is_utf8($L)&&strlen($Sd=iconv("windows-1250","utf-8",$L))>strlen($L))$L=$Sd;return$L;}function
get_databases($Ob){$L=get_session("dbs");if($L===null){$J=(min_version(5)?"SELECT SCHEMA_NAME FROM information_schema.SCHEMATA ORDER BY SCHEMA_NAME":"SHOW DATABASES");$L=($Ob?slow_query($J):get_vals($J));restart_session();set_session("dbs",$L);stop_session();}return$L;}function
limit($J,$Z,$y,$cd=0,$ae=" "){return" $J$Z".($y!==null?$ae."LIMIT $y".($cd?" OFFSET $cd":""):"");}function
limit1($S,$J,$Z,$ae="\n"){return
limit($J,$Z,1,0,$ae);}function
db_collation($h,$Na){global$f;$L=null;$Wa=$f->result("SHOW CREATE DATABASE ".idf_escape($h),1);if(preg_match('~ COLLATE ([^ ]+)~',$Wa,$A))$L=$A[1];elseif(preg_match('~ CHARACTER SET ([^ ]+)~',$Wa,$A))$L=$Na[$A[1]][-1];return$L;}function
engines(){$L=array();foreach(get_rows("SHOW ENGINES")as$M){if(preg_match("~YES|DEFAULT~",$M["Support"]))$L[]=$M["Engine"];}return$L;}function
logged_user(){global$f;return$f->result("SELECT USER()");}function
tables_list(){return
get_key_vals(min_version(5)?"SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME":"SHOW TABLES");}function
count_tables($eb){$L=array();foreach($eb
as$h)$L[$h]=count(get_vals("SHOW TABLES IN ".idf_escape($h)));return$L;}function
table_status($D="",$Ib=false){$L=array();foreach(get_rows($Ib&&min_version(5)?"SELECT TABLE_NAME AS Name, ENGINE AS Engine, TABLE_COMMENT AS Comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ".($D!=""?"AND TABLE_NAME = ".q($D):"ORDER BY Name"):"SHOW TABLE STATUS".($D!=""?" LIKE ".q(addcslashes($D,"%_\\")):""))as$M){if($M["Engine"]=="InnoDB")$M["Comment"]=preg_replace('~(?:(.+); )?InnoDB free: .*~','\1',$M["Comment"]);if(!isset($M["Engine"]))$M["Comment"]="";if($D!="")return$M;$L[$M["Name"]]=$M;}return$L;}function
is_view($T){return$T["Engine"]===null;}function
fk_support($T){return
preg_match('~InnoDB|IBMDB2I~i',$T["Engine"])||(preg_match('~NDB~i',$T["Engine"])&&min_version(5.6));}function
fields($S){$L=array();foreach(get_rows("SHOW FULL COLUMNS FROM ".table($S))as$M){preg_match('~^([^( ]+)(?:\((.+)\))?( unsigned)?( zerofill)?$~',$M["Type"],$A);$L[$M["Field"]]=array("field"=>$M["Field"],"full_type"=>$M["Type"],"type"=>$A[1],"length"=>$A[2],"unsigned"=>ltrim($A[3].$A[4]),"default"=>($M["Default"]!=""||preg_match("~char|set~",$A[1])?(preg_match('~text~',$A[1])?stripslashes(preg_replace("~^'(.*)'\$~",'\1',$M["Default"])):$M["Default"]):null),"null"=>($M["Null"]=="YES"),"auto_increment"=>($M["Extra"]=="auto_increment"),"on_update"=>(preg_match('~^on update (.+)~i',$M["Extra"],$A)?$A[1]:""),"collation"=>$M["Collation"],"privileges"=>array_flip(preg_split('~, *~',$M["Privileges"])),"comment"=>$M["Comment"],"primary"=>($M["Key"]=="PRI"),"generated"=>preg_match('~^(VIRTUAL|PERSISTENT|STORED)~',$M["Extra"]),);}return$L;}function
indexes($S,$g=null){$L=array();foreach(get_rows("SHOW INDEX FROM ".table($S),$g)as$M){$D=$M["Key_name"];$L[$D]["type"]=($D=="PRIMARY"?"PRIMARY":($M["Index_type"]=="FULLTEXT"?"FULLTEXT":($M["Non_unique"]?($M["Index_type"]=="SPATIAL"?"SPATIAL":"INDEX"):"UNIQUE")));$L[$D]["columns"][]=$M["Column_name"];$L[$D]["lengths"][]=($M["Index_type"]=="SPATIAL"?null:$M["Sub_part"]);$L[$D]["descs"][]=null;}return$L;}function
foreign_keys($S){global$f,$ed;static$I='(?:`(?:[^`]|``)+`|"(?:[^"]|"")+")';$L=array();$Xa=$f->result("SHOW CREATE TABLE ".table($S),1);if($Xa){preg_match_all("~CONSTRAINT ($I) FOREIGN KEY ?\\(((?:$I,? ?)+)\\) REFERENCES ($I)(?:\\.($I))? \\(((?:$I,? ?)+)\\)(?: ON DELETE ($ed))?(?: ON UPDATE ($ed))?~",$Xa,$Nc,PREG_SET_ORDER);foreach($Nc
as$A){preg_match_all("~$I~",$A[2],$ie);preg_match_all("~$I~",$A[5],$ze);$L[idf_unescape($A[1])]=array("db"=>idf_unescape($A[4]!=""?$A[3]:$A[4]),"table"=>idf_unescape($A[4]!=""?$A[4]:$A[3]),"source"=>array_map('idf_unescape',$ie[0]),"target"=>array_map('idf_unescape',$ze[0]),"on_delete"=>($A[6]?$A[6]:"RESTRICT"),"on_update"=>($A[7]?$A[7]:"RESTRICT"),);}}return$L;}function
view($D){global$f;return
array("select"=>preg_replace('~^(?:[^`]|`[^`]*`)*\s+AS\s+~isU','',$f->result("SHOW CREATE VIEW ".table($D),1)));}function
collations(){$L=array();foreach(get_rows("SHOW COLLATION")as$M){if($M["Default"])$L[$M["Charset"]][-1]=$M["Collation"];else$L[$M["Charset"]][]=$M["Collation"];}ksort($L);foreach($L
as$x=>$W)asort($L[$x]);return$L;}function
information_schema($h){return(min_version(5)&&$h=="information_schema")||(min_version(5.5)&&$h=="performance_schema");}function
error(){global$f;return
h(preg_replace('~^You have an error.*syntax to use~U',"Syntax error",$f->error));}function
create_database($h,$Ma){return
queries("CREATE DATABASE ".idf_escape($h).($Ma?" COLLATE ".q($Ma):""));}function
drop_databases($eb){$L=apply_queries("DROP DATABASE",$eb,'idf_escape');restart_session();set_session("dbs",null);return$L;}function
rename_database($D,$Ma){$L=false;if(create_database($D,$Ma)){$xe=array();$hf=array();foreach(tables_list()as$S=>$Pe){if($Pe=='VIEW')$hf[]=$S;else$xe[]=$S;}$L=(!$xe&&!$hf)||move_tables($xe,$hf,$D);drop_databases($L?array(DB):array());}return$L;}function
auto_increment(){$ta=" PRIMARY KEY";if($_GET["create"]!=""&&$_POST["auto_increment_col"]){foreach(indexes($_GET["create"])as$pc){if(in_array($_POST["fields"][$_POST["auto_increment_col"]]["orig"],$pc["columns"],true)){$ta="";break;}if($pc["type"]=="PRIMARY")$ta=" UNIQUE";}}return" AUTO_INCREMENT$ta";}function
alter_table($S,$D,$m,$Qb,$Qa,$yb,$Ma,$sa,$sd){$ma=array();foreach($m
as$l)$ma[]=($l[1]?($S!=""?($l[0]!=""?"CHANGE ".idf_escape($l[0]):"ADD"):" ")." ".implode($l[1]).($S!=""?$l[2]:""):"DROP ".idf_escape($l[0]));$ma=array_merge($ma,$Qb);$ne=($Qa!==null?" COMMENT=".q($Qa):"").($yb?" ENGINE=".q($yb):"").($Ma?" COLLATE ".q($Ma):"").($sa!=""?" AUTO_INCREMENT=$sa":"");if($S=="")return
queries("CREATE TABLE ".table($D)." (\n".implode(",\n",$ma)."\n)$ne$sd");if($S!=$D)$ma[]="RENAME TO ".table($D);if($ne)$ma[]=ltrim($ne);return($ma||$sd?queries("ALTER TABLE ".table($S)."\n".implode(",\n",$ma).$sd):true);}function
alter_indexes($S,$ma){foreach($ma
as$x=>$W)$ma[$x]=($W[2]=="DROP"?"\nDROP INDEX ".idf_escape($W[1]):"\nADD $W[0] ".($W[0]=="PRIMARY"?"KEY ":"").($W[1]!=""?idf_escape($W[1])." ":"")."(".implode(", ",$W[2]).")");return
queries("ALTER TABLE ".table($S).implode(",",$ma));}function
truncate_tables($xe){return
apply_queries("TRUNCATE TABLE",$xe);}function
drop_views($hf){return
queries("DROP VIEW ".implode(", ",array_map('table',$hf)));}function
drop_tables($xe){return
queries("DROP TABLE ".implode(", ",array_map('table',$xe)));}function
move_tables($xe,$hf,$ze){global$f;$Md=array();foreach($xe
as$S)$Md[]=table($S)." TO ".idf_escape($ze).".".table($S);if(!$Md||queries("RENAME TABLE ".implode(", ",$Md))){$hb=array();foreach($hf
as$S)$hb[table($S)]=view($S);$f->select_db($ze);$h=idf_escape(DB);foreach($hb
as$D=>$gf){if(!queries("CREATE VIEW $D AS ".str_replace(" $h."," ",$gf["select"]))||!queries("DROP VIEW $h.$D"))return
false;}return
true;}return
false;}function
copy_tables($xe,$hf,$ze){queries("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");foreach($xe
as$S){$D=($ze==DB?table("copy_$S"):idf_escape($ze).".".table($S));if(($_POST["overwrite"]&&!queries("\nDROP TABLE IF EXISTS $D"))||!queries("CREATE TABLE $D LIKE ".table($S))||!queries("INSERT INTO $D SELECT * FROM ".table($S)))return
false;foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($S,"%_\\")))as$M){$Oe=$M["Trigger"];if(!queries("CREATE TRIGGER ".($ze==DB?idf_escape("copy_$Oe"):idf_escape($ze).".".idf_escape($Oe))." $M[Timing] $M[Event] ON $D FOR EACH ROW\n$M[Statement];"))return
false;}}foreach($hf
as$S){$D=($ze==DB?table("copy_$S"):idf_escape($ze).".".table($S));$gf=view($S);if(($_POST["overwrite"]&&!queries("DROP VIEW IF EXISTS $D"))||!queries("CREATE VIEW $D AS $gf[select]"))return
false;}return
true;}function
trigger($D){if($D=="")return
array();$N=get_rows("SHOW TRIGGERS WHERE `Trigger` = ".q($D));return
reset($N);}function
triggers($S){$L=array();foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($S,"%_\\")))as$M)$L[$M["Trigger"]]=array($M["Timing"],$M["Event"]);return$L;}function
trigger_options(){return
array("Timing"=>array("BEFORE","AFTER"),"Event"=>array("INSERT","UPDATE","DELETE"),"Type"=>array("FOR EACH ROW"),);}function
routine($D,$Pe){global$f,$zb,$sc,$Re;$la=array("bool","boolean","integer","double precision","real","dec","numeric","fixed","national char","national varchar");$je="(?:\\s|/\\*[\s\S]*?\\*/|(?:#|-- )[^\n]*\n?|--\r?\n)";$Qe="((".implode("|",array_merge(array_keys($Re),$la)).")\\b(?:\\s*\\(((?:[^'\")]|$zb)++)\\))?\\s*(zerofill\\s*)?(unsigned(?:\\s+zerofill)?)?)(?:\\s*(?:CHARSET|CHARACTER\\s+SET)\\s*['\"]?([^'\"\\s,]+)['\"]?)?";$I="$je*(".($Pe=="FUNCTION"?"":$sc).")?\\s*(?:`((?:[^`]|``)*)`\\s*|\\b(\\S+)\\s+)$Qe";$Wa=$f->result("SHOW CREATE $Pe ".idf_escape($D),2);preg_match("~\\(((?:$I\\s*,?)*)\\)\\s*".($Pe=="FUNCTION"?"RETURNS\\s+$Qe\\s+":"")."(.*)~is",$Wa,$A);$m=array();preg_match_all("~$I\\s*,?~is",$A[1],$Nc,PREG_SET_ORDER);foreach($Nc
as$qd)$m[]=array("field"=>str_replace("``","`",$qd[2]).$qd[3],"type"=>strtolower($qd[5]),"length"=>preg_replace_callback("~$zb~s",'normalize_enum',$qd[6]),"unsigned"=>strtolower(preg_replace('~\s+~',' ',trim("$qd[8] $qd[7]"))),"null"=>1,"full_type"=>$qd[4],"inout"=>strtoupper($qd[1]),"collation"=>strtolower($qd[9]),);if($Pe!="FUNCTION")return
array("fields"=>$m,"definition"=>$A[11]);return
array("fields"=>$m,"returns"=>array("type"=>$A[12],"length"=>$A[13],"unsigned"=>$A[15],"collation"=>$A[16]),"definition"=>$A[17],"language"=>"SQL",);}function
routines(){return
get_rows("SELECT ROUTINE_NAME AS SPECIFIC_NAME, ROUTINE_NAME, ROUTINE_TYPE, DTD_IDENTIFIER FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA = ".q(DB));}function
routine_languages(){return
array();}function
routine_id($D,$M){return
idf_escape($D);}function
last_id(){global$f;return$f->result("SELECT LAST_INSERT_ID()");}function
explain($f,$J){return$f->query("EXPLAIN ".(min_version(5.1)&&!min_version(5.7)?"PARTITIONS ":"").$J);}function
found_rows($T,$Z){return($Z||$T["Engine"]!="InnoDB"?null:$T["Rows"]);}function
types(){return
array();}function
schemas(){return
array();}function
get_schema(){return"";}function
set_schema($Td,$g=null){return
true;}function
create_sql($S,$sa,$qe){global$f;$L=$f->result("SHOW CREATE TABLE ".table($S),1);if(!$sa)$L=preg_replace('~ AUTO_INCREMENT=\d+~','',$L);return$L;}function
truncate_sql($S){return"TRUNCATE ".table($S);}function
use_sql($db){return"USE ".idf_escape($db);}function
trigger_sql($S){$L="";foreach(get_rows("SHOW TRIGGERS LIKE ".q(addcslashes($S,"%_\\")),null,"-- ")as$M)$L.="\nCREATE TRIGGER ".idf_escape($M["Trigger"])." $M[Timing] $M[Event] ON ".table($M["Table"])." FOR EACH ROW\n$M[Statement];;\n";return$L;}function
show_variables(){return
get_key_vals("SHOW VARIABLES");}function
process_list(){return
get_rows("SHOW FULL PROCESSLIST");}function
show_status(){return
get_key_vals("SHOW STATUS");}function
convert_field($l){if(preg_match("~binary~",$l["type"]))return"HEX(".idf_escape($l["field"]).")";if($l["type"]=="bit")return"BIN(".idf_escape($l["field"])." + 0)";if(preg_match("~geometry|point|linestring|polygon~",$l["type"]))return(min_version(8)?"ST_":"")."AsWKT(".idf_escape($l["field"]).")";}function
unconvert_field($l,$L){if(preg_match("~binary~",$l["type"]))$L="UNHEX($L)";if($l["type"]=="bit")$L="CONV($L, 2, 10) + 0";if(preg_match("~geometry|point|linestring|polygon~",$l["type"]))$L=(min_version(8)?"ST_":"")."GeomFromText($L, SRID($l[field]))";return$L;}function
support($Jb){return!preg_match("~scheme|sequence|type|view_trigger|materializedview".(min_version(8)?"":"|descidx".(min_version(5.1)?"":"|event|partitioning".(min_version(5)?"":"|routine|trigger|view")))."~",$Jb);}function
kill_process($W){return
queries("KILL ".number($W));}function
connection_id(){return"SELECT CONNECTION_ID()";}function
max_connections(){global$f;return$f->result("SELECT @@max_connections");}function
driver_config(){$Re=array();$pe=array();foreach(array('Numbers'=>array("tinyint"=>3,"smallint"=>5,"mediumint"=>8,"int"=>10,"bigint"=>20,"decimal"=>66,"float"=>12,"double"=>21),'Date and time'=>array("date"=>10,"datetime"=>19,"timestamp"=>19,"time"=>10,"year"=>4),'Strings'=>array("char"=>255,"varchar"=>65535,"tinytext"=>255,"text"=>65535,"mediumtext"=>16777215,"longtext"=>4294967295),'Lists'=>array("enum"=>65535,"set"=>64),'Binary'=>array("bit"=>20,"binary"=>255,"varbinary"=>65535,"tinyblob"=>255,"blob"=>65535,"mediumblob"=>16777215,"longblob"=>4294967295),'Geometry'=>array("geometry"=>0,"point"=>0,"linestring"=>0,"polygon"=>0,"multipoint"=>0,"multilinestring"=>0,"multipolygon"=>0,"geometrycollection"=>0),)as$x=>$W){$Re+=$W;$pe[$x]=array_keys($W);}return
array('possible_drivers'=>array("MySQLi","MySQL","PDO_MySQL"),'jush'=>"sql",'types'=>$Re,'structured_types'=>$pe,'unsigned'=>array("unsigned","zerofill","unsigned zerofill"),'operators'=>array("=","<",">","<=",">=","!=","LIKE","LIKE %%","REGEXP","IN","FIND_IN_SET","IS NULL","NOT LIKE","NOT REGEXP","NOT IN","IS NOT NULL","SQL"),'functions'=>array("char_length","date","from_unixtime","lower","round","floor","ceil","sec_to_time","time_to_sec","upper"),'grouping'=>array("avg","count","count distinct","group_concat","max","min","sum"),'edit_functions'=>array(array("char"=>"md5/sha1/password/encrypt/uuid","binary"=>"md5/sha1","date|time"=>"now",),array(number_type()=>"+/-","date"=>"+ interval/- interval","time"=>"addtime/subtime","char|text"=>"concat",)),);}}$Sa=driver_config();$yd=$Sa['possible_drivers'];$w=$Sa['jush'];$Re=$Sa['types'];$pe=$Sa['structured_types'];$Ye=$Sa['unsigned'];$jd=$Sa['operators'];$Yb=$Sa['functions'];$bc=$Sa['grouping'];$rb=$Sa['edit_functions'];if($b->operators===null)$b->operators=$jd;define("SERVER",$_GET[DRIVER]);define("DB",$_GET["db"]);define("ME",preg_replace('~\?.*~','',relative_uri()).'?'.(sid()?SID.'&':'').(SERVER!==null?DRIVER."=".urlencode(SERVER).'&':'').(isset($_GET["username"])?"username=".urlencode($_GET["username"]).'&':'').(DB!=""?'db='.urlencode(DB).'&'.(isset($_GET["ns"])?"ns=".urlencode($_GET["ns"])."&":""):''));$ca="4.8.1";function
page_header($Ee,$k="",$Ca=array(),$Fe=""){global$ba,$ca,$b,$ob,$w;page_headers();if(is_ajax()&&$k){page_messages($k);exit;}$Ge=$Ee.($Fe!=""?": $Fe":"");$He=strip_tags($Ge.(SERVER!=""&&SERVER!="localhost"?h(" - ".SERVER):"")." - ".$b->name());echo'<!DOCTYPE html>
<html lang="en" dir="ltr">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="robots" content="noindex">
<title>',$He,'</title>
<link rel="stylesheet" type="text/css" href="',h(preg_replace("~\\?.*~","",ME)."?file=default.css&version=4.8.1"),'">
',script_src(preg_replace("~\\?.*~","",ME)."?file=functions.js&version=4.8.1");if($b->head()){echo'<link rel="shortcut icon" type="image/x-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.8.1"),'">
<link rel="apple-touch-icon" href="',h(preg_replace("~\\?.*~","",ME)."?file=favicon.ico&version=4.8.1"),'">
';foreach($b->css()as$ab){echo'<link rel="stylesheet" type="text/css" href="',h($ab),'">
';}}echo'
<body class="ltr nojs">
';$n=get_temp_dir()."/adminer.version";if(!$_COOKIE["adminer_version"]&&function_exists('openssl_verify')&&file_exists($n)&&filemtime($n)+86400>time()){$ff=unserialize(file_get_contents($n));$Dd="-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwqWOVuF5uw7/+Z70djoK
RlHIZFZPO0uYRezq90+7Amk+FDNd7KkL5eDve+vHRJBLAszF/7XKXe11xwliIsFs
DFWQlsABVZB3oisKCBEuI71J4kPH8dKGEWR9jDHFw3cWmoH3PmqImX6FISWbG3B8
h7FIx3jEaw5ckVPVTeo5JRm/1DZzJxjyDenXvBQ/6o9DgZKeNDgxwKzH+sw9/YCO
jHnq1cFpOIISzARlrHMa/43YfeNRAm/tsBXjSxembBPo7aQZLAWHmaj5+K19H10B
nCpz9Y++cipkVEiKRGih4ZEvjoFysEOdRLj6WiD/uUNky4xGeA6LaJqh5XpkFkcQ
fQIDAQAB
-----END PUBLIC KEY-----
";if(openssl_verify($ff["version"],base64_decode($ff["signature"]),$Dd)==1)$_COOKIE["adminer_version"]=$ff["version"];}echo'<script',nonce(),'>
mixin(document.body, {onkeydown: bodyKeydown, onclick: bodyClick',(isset($_COOKIE["adminer_version"])?"":", onload: partial(verifyVersion, '$ca', '".js_escape(ME)."', '".get_token()."')");?>});
document.body.className = document.body.className.replace(/ nojs/, ' js');
var offlineMessage = '<?php echo
js_escape('You are offline.'),'\';
var thousandsSeparator = \'',js_escape(','),'\';
</script>

<div id="help" class="jush-',$w,' jsonly hidden"></div>
',script("mixin(qs('#help'), {onmouseover: function () { helpOpen = 1; }, onmouseout: helpMouseout});"),'
<div id="content">
';if($Ca!==null){$z=substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1);echo'<p id="breadcrumb"><a href="'.h($z?$z:".").'">'.$ob[DRIVER].'</a> &raquo; ';$z=substr(preg_replace('~\b(db|ns)=[^&]*&~','',ME),0,-1);$P=$b->serverName(SERVER);$P=($P!=""?$P:'Server');if($Ca===false)echo"$P\n";else{echo"<a href='".h($z)."' accesskey='1' title='Alt+Shift+1'>$P</a> &raquo; ";if($_GET["ns"]!=""||(DB!=""&&is_array($Ca)))echo'<a href="'.h($z."&db=".urlencode(DB).(support("scheme")?"&ns=":"")).'">'.h(DB).'</a> &raquo; ';if(is_array($Ca)){if($_GET["ns"]!="")echo'<a href="'.h(substr(ME,0,-1)).'">'.h($_GET["ns"]).'</a> &raquo; ';foreach($Ca
as$x=>$W){$ib=(is_array($W)?$W[1]:h($W));if($ib!="")echo"<a href='".h(ME."$x=").urlencode(is_array($W)?$W[0]:$W)."'>$ib</a> &raquo; ";}}echo"$Ee\n";}}echo"<h2>$Ge</h2>\n","<div id='ajaxstatus' class='jsonly hidden'></div>\n";restart_session();page_messages($k);$eb=&get_session("dbs");if(DB!=""&&$eb&&!in_array(DB,$eb,true))$eb=null;stop_session();define("PAGE_HEADER",1);}function
page_headers(){global$b;header("Content-Type: text/html; charset=utf-8");header("Cache-Control: no-cache");header("X-Frame-Options: deny");header("X-XSS-Protection: 0");header("X-Content-Type-Options: nosniff");header("Referrer-Policy: origin-when-cross-origin");foreach($b->csp()as$Za){$gc=array();foreach($Za
as$x=>$W)$gc[]="$x $W";header("Content-Security-Policy: ".implode("; ",$gc));}$b->headers();}function
csp(){return
array(array("script-src"=>"'self' 'unsafe-inline' 'nonce-".get_nonce()."' 'strict-dynamic'","connect-src"=>"'self'","frame-src"=>"https://www.adminer.org","object-src"=>"'none'","base-uri"=>"'none'","form-action"=>"'self'",),);}function
get_nonce(){static$Zc;if(!$Zc)$Zc=base64_encode(rand_string());return$Zc;}function
page_messages($k){$af=preg_replace('~^[^?]*~','',$_SERVER["REQUEST_URI"]);$Tc=$_SESSION["messages"][$af];if($Tc){echo"<div class='message'>".implode("</div>\n<div class='message'>",$Tc)."</div>".script("messagesPrint();");unset($_SESSION["messages"][$af]);}if($k)echo"<div class='error'>$k</div>\n";}function
page_footer($Uc=""){global$b,$Je;echo'</div>

';if($Uc!="auth"){echo'<form action="" method="post">
<p class="logout">
<input type="submit" name="logout" value="Logout" id="logout">
<input type="hidden" name="token" value="',$Je,'">
</p>
</form>
';}echo'<div id="menu">
';$b->navigation($Uc);echo'</div>
',script("setupSubmitHighlight(document);");}function
int32($C){while($C>=2147483648)$C-=4294967296;while($C<=-2147483649)$C+=4294967296;return(int)$C;}function
long2str($V,$jf){$Sd='';foreach($V
as$W)$Sd.=pack('V',$W);if($jf)return
substr($Sd,0,end($V));return$Sd;}function
str2long($Sd,$jf){$V=array_values(unpack('V*',str_pad($Sd,4*ceil(strlen($Sd)/4),"\0")));if($jf)$V[]=strlen($Sd);return$V;}function
xxtea_mx($of,$nf,$te,$yc){return
int32((($of>>5&0x7FFFFFF)^$nf<<2)+(($nf>>3&0x1FFFFFFF)^$of<<4))^int32(($te^$nf)+($yc^$of));}function
encrypt_string($oe,$x){if($oe=="")return"";$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($oe,true);$C=count($V)-1;$of=$V[$C];$nf=$V[0];$Ed=floor(6+52/($C+1));$te=0;while($Ed-->0){$te=int32($te+0x9E3779B9);$qb=$te>>2&3;for($od=0;$od<$C;$od++){$nf=$V[$od+1];$Wc=xxtea_mx($of,$nf,$te,$x[$od&3^$qb]);$of=int32($V[$od]+$Wc);$V[$od]=$of;}$nf=$V[0];$Wc=xxtea_mx($of,$nf,$te,$x[$od&3^$qb]);$of=int32($V[$C]+$Wc);$V[$C]=$of;}return
long2str($V,false);}function
decrypt_string($oe,$x){if($oe=="")return"";if(!$x)return
false;$x=array_values(unpack("V*",pack("H*",md5($x))));$V=str2long($oe,false);$C=count($V)-1;$of=$V[$C];$nf=$V[0];$Ed=floor(6+52/($C+1));$te=int32($Ed*0x9E3779B9);while($te){$qb=$te>>2&3;for($od=$C;$od>0;$od--){$of=$V[$od-1];$Wc=xxtea_mx($of,$nf,$te,$x[$od&3^$qb]);$nf=int32($V[$od]-$Wc);$V[$od]=$nf;}$of=$V[$C];$Wc=xxtea_mx($of,$nf,$te,$x[$od&3^$qb]);$nf=int32($V[0]-$Wc);$V[0]=$nf;$te=int32($te-0x9E3779B9);}return
long2str($V,true);}$f='';$fc=$_SESSION["token"];if(!$fc)$_SESSION["token"]=rand(1,1e6);$Je=get_token();$ud=array();if($_COOKIE["adminer_permanent"]){foreach(explode(" ",$_COOKIE["adminer_permanent"])as$W){list($x)=explode(":",$W);$ud[$x]=$W;}}function
add_invalid_login(){global$b;$o=file_open_lock(get_temp_dir()."/adminer.invalid");if(!$o)return;$vc=unserialize(stream_get_contents($o));$Ce=time();if($vc){foreach($vc
as$wc=>$W){if($W[0]<$Ce)unset($vc[$wc]);}}$uc=&$vc[$b->bruteForceKey()];if(!$uc)$uc=array($Ce+30*60,0);$uc[1]++;file_write_unlock($o,serialize($vc));}function
check_invalid_login(){global$b;$vc=unserialize(@file_get_contents(get_temp_dir()."/adminer.invalid"));$uc=($vc?$vc[$b->bruteForceKey()]:array());$Yc=($uc[1]>29?$uc[0]-time():0);if($Yc>0)auth_error(lang(array('Too many unsuccessful logins, try again in %d minute.','Too many unsuccessful logins, try again in %d minutes.'),ceil($Yc/60)));}$ra=$_POST["auth"];if($ra){session_regenerate_id();$Y=$ra["driver"];$P=$ra["server"];$U=$ra["username"];$H=(string)$ra["password"];$h=$ra["db"];set_password($Y,$P,$U,$H);$_SESSION["db"][$Y][$P][$U][$h]=true;if($ra["permanent"]){$x=base64_encode($Y)."-".base64_encode($P)."-".base64_encode($U)."-".base64_encode($h);$Bd=$b->permanentLogin(true);$ud[$x]="$x:".base64_encode($Bd?encrypt_string($H,$Bd):"");cookie("adminer_permanent",implode(" ",$ud));}if(count($_POST)==1||DRIVER!=$Y||SERVER!=$P||$_GET["username"]!==$U||DB!=$h)redirect(auth_url($Y,$P,$U,$h));}elseif($_POST["logout"]&&(!$fc||verify_token())){foreach(array("pwds","db","dbs","queries")as$x)set_session($x,null);unset_permanent();redirect(substr(preg_replace('~\b(username|db|ns)=[^&]*&~','',ME),0,-1),'Logout successful.'.' '.'Thanks for using Adminer, consider <a href="https://www.adminer.org/en/donation/">donating</a>.');}elseif($ud&&!$_SESSION["pwds"]){session_regenerate_id();$Bd=$b->permanentLogin();foreach($ud
as$x=>$W){list(,$Ha)=explode(":",$W);list($Y,$P,$U,$h)=array_map('base64_decode',explode("-",$x));set_password($Y,$P,$U,decrypt_string(base64_decode($Ha),$Bd));$_SESSION["db"][$Y][$P][$U][$h]=true;}}function
unset_permanent(){global$ud;foreach($ud
as$x=>$W){list($Y,$P,$U,$h)=array_map('base64_decode',explode("-",$x));if($Y==DRIVER&&$P==SERVER&&$U==$_GET["username"]&&$h==DB)unset($ud[$x]);}cookie("adminer_permanent",implode(" ",$ud));}function
auth_error($k){global$b,$fc;$de=session_name();if(isset($_GET["username"])){header("HTTP/1.1 403 Forbidden");if(($_COOKIE[$de]||$_GET[$de])&&!$fc)$k='Session expired, please login again.';else{restart_session();add_invalid_login();$H=get_password();if($H!==null){if($H===false)$k.=($k?'<br>':'').sprintf('Master password expired. <a href="https://www.adminer.org/en/extension/"%s>Implement</a> %s method to make it permanent.',target_blank(),'<code>permanentLogin()</code>');set_password(DRIVER,SERVER,$_GET["username"],null);}unset_permanent();}}if(!$_COOKIE[$de]&&$_GET[$de]&&ini_bool("session.use_only_cookies"))$k='Session support must be enabled.';$rd=session_get_cookie_params();cookie("adminer_key",($_COOKIE["adminer_key"]?$_COOKIE["adminer_key"]:rand_string()),$rd["lifetime"]);page_header('Login',$k,null);echo"<form action='' method='post'>\n","<div>";if(hidden_fields($_POST,array("auth")))echo"<p class='message'>".'The action will be performed after successful login with the same credentials.'."\n";echo"</div>\n";$b->loginForm();echo"</form>\n";page_footer("auth");exit;}if(isset($_GET["username"])&&!class_exists("Min_DB")){unset($_SESSION["pwds"][DRIVER]);unset_permanent();page_header('No extension',sprintf('None of the supported PHP extensions (%s) are available.',implode(", ",$yd)),false);page_footer("auth");exit;}stop_session(true);if(isset($_GET["username"])&&is_string(get_password())){list($kc,$wd)=explode(":",SERVER,2);if(preg_match('~^\s*([-+]?\d+)~',$wd,$A)&&($A[1]<1024||$A[1]>65535))auth_error('Connecting to privileged ports is not allowed.');check_invalid_login();$f=connect();$i=new
Min_Driver($f);}$Jc=null;if(!is_object($f)||($Jc=$b->login($_GET["username"],get_password()))!==true){$k=(is_string($f)?h($f):(is_string($Jc)?$Jc:'Invalid credentials.'));auth_error($k.(preg_match('~^ | $~',get_password())?'<br>'.'There is a space in the input password which might be the cause.':''));}if($_POST["logout"]&&$fc&&!verify_token()){page_header('Logout','Invalid CSRF token. Send the form again.');page_footer("db");exit;}if($ra&&$_POST["token"])$_POST["token"]=$Je;$k='';if($_POST){if(!verify_token()){$rc="max_input_vars";$Rc=ini_get($rc);if(extension_loaded("suhosin")){foreach(array("suhosin.request.max_vars","suhosin.post.max_vars")as$x){$W=ini_get($x);if($W&&(!$Rc||$W<$Rc)){$rc=$x;$Rc=$W;}}}$k=(!$_POST["token"]&&$Rc?sprintf('Maximum number of allowed fields exceeded. Please increase %s.',"'$rc'"):'Invalid CSRF token. Send the form again.'.' '.'If you did not send this request from Adminer then close this page.');}}elseif($_SERVER["REQUEST_METHOD"]=="POST"){$k=sprintf('Too big POST data. Reduce the data or increase the %s configuration directive.',"'post_max_size'");if(isset($_GET["sql"]))$k.=' '.'You can upload a big SQL file via FTP and import it from server.';}function
email_header($gc){return"=?UTF-8?B?".base64_encode($gc)."?=";}function
send_mail($ub,$re,$B,$Xb="",$Lb=array()){$j=(DIRECTORY_SEPARATOR=="/"?"\n":"\r\n");$B=str_replace("\n",$j,wordwrap(str_replace("\r","","$B\n")));$Ba=uniqid("boundary");$qa="";foreach((array)$Lb["error"]as$x=>$W){if(!$W)$qa.="--$Ba$j"."Content-Type: ".str_replace("\n","",$Lb["type"][$x]).$j."Content-Disposition: attachment; filename=\"".preg_replace('~["\n]~','',$Lb["name"][$x])."\"$j"."Content-Transfer-Encoding: base64$j$j".chunk_split(base64_encode(file_get_contents($Lb["tmp_name"][$x])),76,$j).$j;}$ya="";$hc="Content-Type: text/plain; charset=utf-8$j"."Content-Transfer-Encoding: 8bit";if($qa){$qa.="--$Ba--$j";$ya="--$Ba$j$hc$j$j";$hc="Content-Type: multipart/mixed; boundary=\"$Ba\"";}$hc.=$j."MIME-Version: 1.0$j"."X-Mailer: Adminer Editor".($Xb?$j."From: ".str_replace("\n","",$Xb):"");return
mail($ub,email_header($re),$ya.$B.$qa,$hc);}function
like_bool($l){return
preg_match("~bool|(tinyint|bit)\\(1\\)~",$l["full_type"]);}$f->select_db($b->database());$ed="RESTRICT|NO ACTION|CASCADE|SET NULL|SET DEFAULT";$ob[DRIVER]='Login';if(isset($_GET["select"])&&($_POST["edit"]||$_POST["clone"])&&!$_POST["save"])$_GET["edit"]=$_GET["select"];if(isset($_GET["download"])){$a=$_GET["download"];$m=fields($a);header("Content-Type: application/octet-stream");header("Content-Disposition: attachment; filename=".friendly_url("$a-".implode("_",$_GET["where"])).".".friendly_url($_GET["field"]));$O=array(idf_escape($_GET["field"]));$K=$i->select($a,$O,array(where($_GET,$m)),$O);$M=($K?$K->fetch_row():array());echo$i->value($M[0],$m[$_GET["field"]]);exit;}elseif(isset($_GET["edit"])){$a=$_GET["edit"];$m=fields($a);$Z=(isset($_GET["select"])?($_POST["check"]&&count($_POST["check"])==1?where_check($_POST["check"][0],$m):""):where($_GET,$m));$Ze=(isset($_GET["select"])?$_POST["edit"]:$Z);foreach($m
as$D=>$l){if(!isset($l["privileges"][$Ze?"update":"insert"])||$b->fieldName($l)==""||$l["generated"])unset($m[$D]);}if($_POST&&!$k&&!isset($_GET["select"])){$_=$_POST["referer"];if($_POST["insert"])$_=($Ze?null:$_SERVER["REQUEST_URI"]);elseif(!preg_match('~^.+&select=.+$~',$_))$_=ME."select=".urlencode($a);$u=indexes($a);$Ue=unique_array($_GET["where"],$u);$Hd="\nWHERE $Z";if(isset($_POST["delete"]))queries_redirect($_,'Item has been deleted.',$i->delete($a,$Hd,!$Ue));else{$Q=array();foreach($m
as$D=>$l){$W=process_input($l);if($W!==false&&$W!==null)$Q[idf_escape($D)]=$W;}if($Ze){if(!$Q)redirect($_);queries_redirect($_,'Item has been updated.',$i->update($a,$Q,$Hd,!$Ue));if(is_ajax()){page_headers();page_messages($k);exit;}}else{$K=$i->insert($a,$Q);$Ec=($K?last_id():0);queries_redirect($_,sprintf('Item%s has been inserted.',($Ec?" $Ec":"")),$K);}}}$M=null;if($_POST["save"])$M=(array)$_POST["fields"];elseif($Z){$O=array();foreach($m
as$D=>$l){if(isset($l["privileges"]["select"])){$oa=convert_field($l);if($_POST["clone"]&&$l["auto_increment"])$oa="''";if($w=="sql"&&preg_match("~enum|set~",$l["type"]))$oa="1*".idf_escape($D);$O[]=($oa?"$oa AS ":"").idf_escape($D);}}$M=array();if(!support("table"))$O=array("*");if($O){$K=$i->select($a,$O,array($Z),$O,array(),(isset($_GET["select"])?2:1));if(!$K)$k=error();else{$M=$K->fetch_assoc();if(!$M)$M=false;}if(isset($_GET["select"])&&(!$M||$K->fetch_assoc()))$M=null;}}if(!support("table")&&!$m){if(!$Z){$K=$i->select($a,array("*"),$Z,array("*"));$M=($K?$K->fetch_assoc():false);if(!$M)$M=array($i->primary=>"");}if($M){foreach($M
as$x=>$W){if(!$Z)$M[$x]=null;$m[$x]=array("field"=>$x,"null"=>($x!=$i->primary),"auto_increment"=>($x==$i->primary));}}}edit_form($a,$m,$M,$Ze);}elseif(isset($_GET["select"])){$a=$_GET["select"];$T=table_status1($a);$u=indexes($a);$m=fields($a);$Ub=column_foreign_keys($a);$dd=$T["Oid"];parse_str($_COOKIE["adminer_import"],$ia);$Rd=array();$e=array();$Ae=null;foreach($m
as$x=>$l){$D=$b->fieldName($l);if(isset($l["privileges"]["select"])&&$D!=""){$e[$x]=html_entity_decode(strip_tags($D),ENT_QUOTES);if(is_shortable($l))$Ae=$b->selectLengthProcess();}$Rd+=$l["privileges"];}list($O,$q)=$b->selectColumnsProcess($e,$u);$v=count($q)<count($O);$Z=$b->selectSearchProcess($m,$u);$F=$b->selectOrderProcess($m,$u);$y=$b->selectLimitProcess();if($_GET["val"]&&is_ajax()){header("Content-Type: text/plain; charset=utf-8");foreach($_GET["val"]as$Ve=>$M){$oa=convert_field($m[key($M)]);$O=array($oa?$oa:idf_escape(key($M)));$Z[]=where_check($Ve,$m);$L=$i->select($a,$O,$Z,$O);if($L)echo
reset($L->fetch_row());}exit;}$_d=$Xe=null;foreach($u
as$pc){if($pc["type"]=="PRIMARY"){$_d=array_flip($pc["columns"]);$Xe=($O?$_d:array());foreach($Xe
as$x=>$W){if(in_array(idf_escape($x),$O))unset($Xe[$x]);}break;}}if($dd&&!$_d){$_d=$Xe=array($dd=>0);$u[]=array("type"=>"PRIMARY","columns"=>array($dd));}if($_POST&&!$k){$lf=$Z;if(!$_POST["all"]&&is_array($_POST["check"])){$Ga=array();foreach($_POST["check"]as$Ea)$Ga[]=where_check($Ea,$m);$lf[]="((".implode(") OR (",$Ga)."))";}$lf=($lf?"\nWHERE ".implode(" AND ",$lf):"");if($_POST["export"]){cookie("adminer_import","output=".urlencode($_POST["output"])."&format=".urlencode($_POST["format"]));dump_headers($a);$b->dumpTable($a,"");$Xb=($O?implode(", ",$O):"*").convert_fields($e,$m,$O)."\nFROM ".table($a);$ac=($q&&$v?"\nGROUP BY ".implode(", ",$q):"").($F?"\nORDER BY ".implode(", ",$F):"");if(!is_array($_POST["check"])||$_d)$J="SELECT $Xb$lf$ac";else{$Te=array();foreach($_POST["check"]as$W)$Te[]="(SELECT".limit($Xb,"\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m).$ac,1).")";$J=implode(" UNION ALL ",$Te);}$b->dumpData($a,"table",$J);exit;}if(!$b->selectEmailProcess($Z,$Ub)){if($_POST["save"]||$_POST["delete"]){$K=true;$ja=0;$Q=array();if(!$_POST["delete"]){foreach($e
as$D=>$W){$W=process_input($m[$D]);if($W!==null&&($_POST["clone"]||$W!==false))$Q[idf_escape($D)]=($W!==false?$W:idf_escape($D));}}if($_POST["delete"]||$Q){if($_POST["clone"])$J="INTO ".table($a)." (".implode(", ",array_keys($Q)).")\nSELECT ".implode(", ",$Q)."\nFROM ".table($a);if($_POST["all"]||($_d&&is_array($_POST["check"]))||$v){$K=($_POST["delete"]?$i->delete($a,$lf):($_POST["clone"]?queries("INSERT $J$lf"):$i->update($a,$Q,$lf)));$ja=$f->affected_rows;}else{foreach((array)$_POST["check"]as$W){$kf="\nWHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($W,$m);$K=($_POST["delete"]?$i->delete($a,$kf,1):($_POST["clone"]?queries("INSERT".limit1($a,$J,$kf)):$i->update($a,$Q,$kf,1)));if(!$K)break;$ja+=$f->affected_rows;}}}$B=lang(array('%d item has been affected.','%d items have been affected.'),$ja);if($_POST["clone"]&&$K&&$ja==1){$Ec=last_id();if($Ec)$B=sprintf('Item%s has been inserted.'," $Ec");}queries_redirect(remove_from_uri($_POST["all"]&&$_POST["delete"]?"page":""),$B,$K);if(!$_POST["delete"]){edit_form($a,$m,(array)$_POST["fields"],!$_POST["clone"]);page_footer();exit;}}elseif(!$_POST["import"]){if(!$_POST["val"])$k='Ctrl+click on a value to modify it.';else{$K=true;$ja=0;foreach($_POST["val"]as$Ve=>$M){$Q=array();foreach($M
as$x=>$W){$x=bracket_escape($x,1);$Q[idf_escape($x)]=(preg_match('~char|text~',$m[$x]["type"])||$W!=""?$b->processInput($m[$x],$W):"NULL");}$K=$i->update($a,$Q," WHERE ".($Z?implode(" AND ",$Z)." AND ":"").where_check($Ve,$m),!$v&&!$_d," ");if(!$K)break;$ja+=$f->affected_rows;}queries_redirect(remove_from_uri(),lang(array('%d item has been affected.','%d items have been affected.'),$ja),$K);}}elseif(!is_string($Kb=get_file("csv_file",true)))$k=upload_error($Kb);elseif(!preg_match('~~u',$Kb))$k='File must be in UTF-8 encoding.';else{cookie("adminer_import","output=".urlencode($ia["output"])."&format=".urlencode($_POST["separator"]));$K=true;$Oa=array_keys($m);preg_match_all('~(?>"[^"]*"|[^"\r\n]+)+~',$Kb,$Nc);$ja=count($Nc[0]);$i->begin();$ae=($_POST["separator"]=="csv"?",":($_POST["separator"]=="tsv"?"\t":";"));$N=array();foreach($Nc[0]as$x=>$W){preg_match_all("~((?>\"[^\"]*\")+|[^$ae]*)$ae~",$W.$ae,$Oc);if(!$x&&!array_diff($Oc[1],$Oa)){$Oa=$Oc[1];$ja--;}else{$Q=array();foreach($Oc[1]as$r=>$La)$Q[idf_escape($Oa[$r])]=($La==""&&$m[$Oa[$r]]["null"]?"NULL":q(str_replace('""','"',preg_replace('~^"|"$~','',$La))));$N[]=$Q;}}$K=(!$N||$i->insertUpdate($a,$N,$_d));if($K)$K=$i->commit();queries_redirect(remove_from_uri("page"),lang(array('%d row has been imported.','%d rows have been imported.'),$ja),$K);$i->rollback();}}}$we=$b->tableName($T);if(is_ajax()){page_headers();ob_start();}else
page_header('Select'.": $we",$k);$Q=null;if(isset($Rd["insert"])||!support("table")){$Q="";foreach((array)$_GET["where"]as$W){if($Ub[$W["col"]]&&count($Ub[$W["col"]])==1&&($W["op"]=="="||(!$W["op"]&&!preg_match('~[_%]~',$W["val"]))))$Q.="&set".urlencode("[".bracket_escape($W["col"])."]")."=".urlencode($W["val"]);}}$b->selectLinks($T,$Q);if(!$e&&support("table"))echo"<p class='error'>".'Unable to select the table'.($m?".":": ".error())."\n";else{echo"<form action='' id='form'>\n","<div style='display: none;'>";hidden_fields_get();echo(DB!=""?'<input type="hidden" name="db" value="'.h(DB).'">'.(isset($_GET["ns"])?'<input type="hidden" name="ns" value="'.h($_GET["ns"]).'">':""):"");echo'<input type="hidden" name="select" value="'.h($a).'">',"</div>\n";$b->selectColumnsPrint($O,$e);$b->selectSearchPrint($Z,$e,$u);$b->selectOrderPrint($F,$e,$u);$b->selectLimitPrint($y);$b->selectLengthPrint($Ae);$b->selectActionPrint($u);echo"</form>\n";$G=$_GET["page"];if($G=="last"){$Wb=$f->result(count_rows($a,$Z,$v,$q));$G=floor(max(0,$Wb-1)/$y);}$Vd=$O;$Zb=$q;if(!$Vd){$Vd[]="*";$Va=convert_fields($e,$m,$O);if($Va)$Vd[]=substr($Va,2);}foreach($O
as$x=>$W){$l=$m[idf_unescape($W)];if($l&&($oa=convert_field($l)))$Vd[$x]="$oa AS $W";}if(!$v&&$Xe){foreach($Xe
as$x=>$W){$Vd[]=idf_escape($x);if($Zb)$Zb[]=idf_escape($x);}}$K=$i->select($a,$Vd,$Z,$Zb,$F,$y,$G,true);if(!$K)echo"<p class='error'>".error()."\n";else{if($w=="mssql"&&$G)$K->seek($y*$G);$wb=array();echo"<form action='' method='post' enctype='multipart/form-data'>\n";$N=array();while($M=$K->fetch_assoc()){if($G&&$w=="oracle")unset($M["RNUM"]);$N[]=$M;}if($_GET["page"]!="last"&&$y!=""&&$q&&$v&&$w=="sql")$Wb=$f->result(" SELECT FOUND_ROWS()");if(!$N)echo"<p class='message'>".'No rows.'."\n";else{$xa=$b->backwardKeys($a,$we);echo"<div class='scrollable'>","<table id='table' cellspacing='0' class='nowrap checkable'>",script("mixin(qs('#table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true), onkeydown: editingKeydown});"),"<thead><tr>".(!$q&&$O?"":"<td><input type='checkbox' id='all-page' class='jsonly'>".script("qs('#all-page').onclick = partial(formCheck, /check/);","")." <a href='".h($_GET["modify"]?remove_from_uri("modify"):$_SERVER["REQUEST_URI"]."&modify=1")."'>".'Modify'."</a>");$Xc=array();$Yb=array();reset($O);$Jd=1;foreach($N[0]as$x=>$W){if(!isset($Xe[$x])){$W=$_GET["columns"][key($O)];$l=$m[$O?($W?$W["col"]:current($O)):$x];$D=($l?$b->fieldName($l,$Jd):($W["fun"]?"*":$x));if($D!=""){$Jd++;$Xc[$x]=$D;$d=idf_escape($x);$lc=remove_from_uri('(order|desc)[^=]*|page').'&order%5B0%5D='.urlencode($x);$ib="&desc%5B0%5D=1";echo"<th id='th[".h(bracket_escape($x))."]'>".script("mixin(qsl('th'), {onmouseover: partial(columnMouse), onmouseout: partial(columnMouse, ' hidden')});",""),'<a href="'.h($lc.($F[0]==$d||$F[0]==$x||(!$F&&$v&&$q[0]==$d)?$ib:'')).'">';echo
apply_sql_function($W["fun"],$D)."</a>";echo"<span class='column hidden'>","<a href='".h($lc.$ib)."' title='".'descending'."' class='text'> ‚Üì</a>";if(!$W["fun"]){echo'<a href="#fieldset-search" title="'.'Search'.'" class="text jsonly"> =</a>',script("qsl('a').onclick = partial(selectSearch, '".js_escape($x)."');");}echo"</span>";}$Yb[$x]=$W["fun"];next($O);}}$Hc=array();if($_GET["modify"]){foreach($N
as$M){foreach($M
as$x=>$W)$Hc[$x]=max($Hc[$x],min(40,strlen(utf8_decode($W))));}}echo($xa?"<th>".'Relations':"")."</thead>\n";if(is_ajax()){if($y%2==1&&$G%2==1)odd();ob_end_clean();}foreach($b->rowDescriptions($N,$Ub)as$C=>$M){$Ue=unique_array($N[$C],$u);if(!$Ue){$Ue=array();foreach($N[$C]as$x=>$W){if(!preg_match('~^(COUNT\((\*|(DISTINCT )?`(?:[^`]|``)+`)\)|(AVG|GROUP_CONCAT|MAX|MIN|SUM)\(`(?:[^`]|``)+`\))$~',$x))$Ue[$x]=$W;}}$Ve="";foreach($Ue
as$x=>$W){if(($w=="sql"||$w=="pgsql")&&preg_match('~char|text|enum|set~',$m[$x]["type"])&&strlen($W)>64){$x=(strpos($x,'(')?$x:idf_escape($x));$x="MD5(".($w!='sql'||preg_match("~^utf8~",$m[$x]["collation"])?$x:"CONVERT($x USING ".charset($f).")").")";$W=md5($W);}$Ve.="&".($W!==null?urlencode("where[".bracket_escape($x)."]")."=".urlencode($W):"null%5B%5D=".urlencode($x));}echo"<tr".odd().">".(!$q&&$O?"":"<td>".checkbox("check[]",substr($Ve,1),in_array(substr($Ve,1),(array)$_POST["check"])).($v||information_schema(DB)?"":" <a href='".h(ME."edit=".urlencode($a).$Ve)."' class='edit'>".'edit'."</a>"));foreach($M
as$x=>$W){if(isset($Xc[$x])){$l=$m[$x];$W=$i->value($W,$l);if($W!=""&&(!isset($wb[$x])||$wb[$x]!=""))$wb[$x]=(is_mail($W)?$Xc[$x]:"");$z="";if(preg_match('~blob|bytea|raw|file~',$l["type"])&&$W!="")$z=ME.'download='.urlencode($a).'&field='.urlencode($x).$Ve;if(!$z&&$W!==null){foreach((array)$Ub[$x]as$Tb){if(count($Ub[$x])==1||end($Tb["source"])==$x){$z="";foreach($Tb["source"]as$r=>$ie)$z.=where_link($r,$Tb["target"][$r],$N[$C][$ie]);$z=($Tb["db"]!=""?preg_replace('~([?&]db=)[^&]+~','\1'.urlencode($Tb["db"]),ME):ME).'select='.urlencode($Tb["table"]).$z;if($Tb["ns"])$z=preg_replace('~([?&]ns=)[^&]+~','\1'.urlencode($Tb["ns"]),$z);if(count($Tb["source"])==1)break;}}}if($x=="COUNT(*)"){$z=ME."select=".urlencode($a);$r=0;foreach((array)$_GET["where"]as$V){if(!array_key_exists($V["col"],$Ue))$z.=where_link($r++,$V["col"],$V["val"],$V["op"]);}foreach($Ue
as$yc=>$V)$z.=where_link($r++,$yc,$V);}$W=select_value($W,$z,$l,$Ae);$s=h("val[$Ve][".bracket_escape($x)."]");$X=$_POST["val"][$Ve][bracket_escape($x)];$sb=!is_array($M[$x])&&is_utf8($W)&&$N[$C][$x]==$M[$x]&&!$Yb[$x];$_e=preg_match('~text|lob~',$l["type"]);echo"<td id='$s'";if(($_GET["modify"]&&$sb)||$X!==null){$cc=h($X!==null?$X:$M[$x]);echo">".($_e?"<textarea name='$s' cols='30' rows='".(substr_count($M[$x],"\n")+1)."'>$cc</textarea>":"<input name='$s' value='$cc' size='$Hc[$x]'>");}else{$Kc=strpos($W,"<i>‚Ä¶</i>");echo" data-text='".($Kc?2:($_e?1:0))."'".($sb?"":" data-warning='".h('Use edit link to modify this value.')."'").">$W</td>";}}}if($xa)echo"<td>";$b->backwardKeysPrint($xa,$N[$C]);echo"</tr>\n";}if(is_ajax())exit;echo"</table>\n","</div>\n";}if(!is_ajax()){if($N||$G){$Db=true;if($_GET["page"]!="last"){if($y==""||(count($N)<$y&&($N||!$G)))$Wb=($G?$G*$y:0)+count($N);elseif($w!="sql"||!$v){$Wb=($v?false:found_rows($T,$Z));if($Wb<max(1e4,2*($G+1)*$y))$Wb=reset(slow_query(count_rows($a,$Z,$v,$q)));else$Db=false;}}$pd=($y!=""&&($Wb===false||$Wb>$y||$G));if($pd){echo(($Wb===false?count($N)+1:$Wb-$G*$y)>$y?'<p><a href="'.h(remove_from_uri("page")."&page=".($G+1)).'" class="loadmore">'.'Load more data'.'</a>'.script("qsl('a').onclick = partial(selectLoadMore, ".(+$y).", '".'Loading'."‚Ä¶');",""):''),"\n";}}echo"<div class='footer'><div>\n";if($N||$G){if($pd){$Pc=($Wb===false?$G+(count($N)>=$y?2:1):floor(($Wb-1)/$y));echo"<fieldset>";if($w!="simpledb"){echo"<legend><a href='".h(remove_from_uri("page"))."'>".'Page'."</a></legend>",script("qsl('a').onclick = function () { pageClick(this.href, +prompt('".'Page'."', '".($G+1)."')); return false; };"),pagination(0,$G).($G>5?" ‚Ä¶":"");for($r=max(1,$G-4);$r<min($Pc,$G+5);$r++)echo
pagination($r,$G);if($Pc>0){echo($G+5<$Pc?" ‚Ä¶":""),($Db&&$Wb!==false?pagination($Pc,$G):" <a href='".h(remove_from_uri("page")."&page=last")."' title='~$Pc'>".'last'."</a>");}}else{echo"<legend>".'Page'."</legend>",pagination(0,$G).($G>1?" ‚Ä¶":""),($G?pagination($G,$G):""),($Pc>$G?pagination($G+1,$G).($Pc>$G+1?" ‚Ä¶":""):"");}echo"</fieldset>\n";}echo"<fieldset>","<legend>".'Whole result'."</legend>";$mb=($Db?"":"~ ").$Wb;echo
checkbox("all",1,0,($Wb!==false?($Db?"":"~ ").lang(array('%d row','%d rows'),$Wb):""),"var checked = formChecked(this, /check/); selectCount('selected', this.checked ? '$mb' : checked); selectCount('selected2', this.checked || !checked ? '$mb' : checked);")."\n","</fieldset>\n";if($b->selectCommandPrint()){echo'<fieldset',($_GET["modify"]?'':' class="jsonly"'),'><legend>Modify</legend><div>
<input type="submit" value="Save"',($_GET["modify"]?'':' title="'.'Ctrl+click on a value to modify it.'.'"'),'>
</div></fieldset>
<fieldset><legend>Selected <span id="selected"></span></legend><div>
<input type="submit" name="edit" value="Edit">
<input type="submit" name="clone" value="Clone">
<input type="submit" name="delete" value="Delete">',confirm(),'</div></fieldset>
';}$Vb=$b->dumpFormat();foreach((array)$_GET["columns"]as$d){if($d["fun"]){unset($Vb['sql']);break;}}if($Vb){print_fieldset("export",'Export'." <span id='selected2'></span>");$nd=$b->dumpOutput();echo($nd?html_select("output",$nd,$ia["output"])." ":""),html_select("format",$Vb,$ia["format"])," <input type='submit' name='export' value='".'Export'."'>\n","</div></fieldset>\n";}$b->selectEmailPrint(array_filter($wb,'strlen'),$e);}echo"</div></div>\n";if($b->selectImportPrint()){echo"<div>","<a href='#import'>".'Import'."</a>",script("qsl('a').onclick = partial(toggle, 'import');",""),"<span id='import' class='hidden'>: ","<input type='file' name='csv_file'> ",html_select("separator",array("csv"=>"CSV,","csv;"=>"CSV;","tsv"=>"TSV"),$ia["format"],1);echo" <input type='submit' name='import' value='".'Import'."'>","</span>","</div>";}echo"<input type='hidden' name='token' value='$Je'>\n","</form>\n",(!$q&&$O?"":script("tableCheck();"));}}}if(is_ajax()){ob_end_clean();exit;}}elseif(isset($_GET["script"])){if($_GET["script"]=="kill")$f->query("KILL ".number($_POST["kill"]));elseif(list($S,$s,$D)=$b->_foreignColumn(column_foreign_keys($_GET["source"]),$_GET["field"])){$y=11;$K=$f->query("SELECT $s, $D FROM ".table($S)." WHERE ".(preg_match('~^[0-9]+$~',$_GET["value"])?"$s = $_GET[value] OR ":"")."$D LIKE ".q("$_GET[value]%")." ORDER BY 2 LIMIT $y");for($r=1;($M=$K->fetch_row())&&$r<$y;$r++)echo"<a href='".h(ME."edit=".urlencode($S)."&where".urlencode("[".bracket_escape(idf_unescape($s))."]")."=".urlencode($M[0]))."'>".h($M[1])."</a><br>\n";if($M)echo"...\n";}exit;}else{page_header('Server',"",false);if($b->homepage()){echo"<form action='' method='post'>\n","<p>".'Search data in tables'.": <input type='search' name='query' value='".h($_POST["query"])."'> <input type='submit' value='".'Search'."'>\n";if($_POST["query"]!="")search_tables();echo"<div class='scrollable'>\n","<table cellspacing='0' class='nowrap checkable'>\n",script("mixin(qsl('table'), {onclick: tableClick, ondblclick: partialArg(tableClick, true)});"),'<thead><tr class="wrap">','<td><input id="check-all" type="checkbox" class="jsonly">'.script("qs('#check-all').onclick = partial(formCheck, /^tables\[/);",""),'<th>'.'Table','<td>'.'Rows',"</thead>\n";foreach(table_status()as$S=>$M){$D=$b->tableName($M);if(isset($M["Engine"])&&$D!=""){echo'<tr'.odd().'><td>'.checkbox("tables[]",$S,in_array($S,(array)$_POST["tables"],true)),"<th><a href='".h(ME).'select='.urlencode($S)."'>$D</a>";$W=format_number($M["Rows"]);echo"<td align='right'><a href='".h(ME."edit=").urlencode($S)."'>".($M["Engine"]=="InnoDB"&&$W?"~ $W":$W)."</a>";}}echo"</table>\n","</div>\n","</form>\n",script("tableCheck();");}}page_footer();
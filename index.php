<?php
/*
 * getDownNow - Directory Browser
 * Copyright (C) 2001 Ray Lopez (http://www.TheDreaming.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/*
*********************** CONFIGURE BELOW THIS LINE ***********************
*/


/*
**	Config Settings
*/

$CompressedFilesSupport = true; // Allow viewing and extracting compressed files.
$scriptLocation = ""; // Point this to a refernce to a style sheet (Embeeded CSS will be ingnorned)

$scriptStats = true; // Allow Showing System Stats  (Disk Usage);
$display_zip_contents = true; // Allow Diplsyaing Compressed File Contents

$scriptCSS = "
<style>
/*
BODY {
  margin: 1em;
  font-family: Arial;
  line-height: 1.1;
  background: #3f3f3f;
  color: #999999;
}
*/
/* A:link { color: #cccccc;} */         /* unvisited link */
/* A:visited { color: #cccccc;} */       /* visited links */
/* A:active { color: #555555; background: #999999;} */        /* active links */
/* A:hover { color: #cccccc; background: #555555;} */

/* TD { background: #555555 } */

INPUT.SUBMIT  { background: #999999; }
</style>
";
/*
**      CSS (Style Sheet) Definition Finished
*/


/*
*********************** DO NOT MODIFY BELOW THIS LINE ***********************
*/
$txtArray = array("txt", "nfo", "diz", "now", "bmp", "jpg", "gif", "doc", "1st", "now", "me", "swf");
if ($display_zip_contents) {
    $compArray = array("zip", "tgz", "tar", "gz");
} else {
    $compArray = array();
}

function dirHeader()
{
    $content = "<table width=100% nowrap>";
    return $content;
}

function compHeader()
{

    $content = "<table width=100% nowrap><tr><td>Viewing File: {$_REQUEST["cfile"]} </td></tr></table>";
    $content .= "<table width=100% nowrap class='table table-striped'>";
    return $content;
}


function compFooter()
{
    $content = "</table>";
    $content .= "<table width=100% nowrap><tr><td></td></tr></table>";
    return $content;
}

function dirTable($header = 1)
{
    $content = ($header) ? "<thead>" : "<tfoot>";
    $content .= "<tr><td>Type</td><td width=50%>Name</td><td>Size</td><td>Modified</td></tr>";
    $content .= ($header) ? "</thead>" : "</tfoot>";
    return $content;
}


function dirFooter()
{
    $content = "</table>";
    return $content;
}

function fType($fileitem)
{
    $varFileType = filetype($fileitem);
    if ($varFileType != "dir") {
        $curdir = getcwd();
        $pInfo = pathinfo("$curdir/$fileitem");
        $varFileType = $pInfo["extension"];
    }
    return $varFileType;
}


function fileView($fileitem)
{
    global $txtArray, $compArray, $CompressedFilesSupport;
    $content = "";
    $varType = strtolower(fType($fileitem));
    $varJSSettings = "width=640,height=480,resizable=1,scrollbars=1,menubar=0,status=0,titlebar=0,toolbar=0,hotkeys=0,locationbar=0";
    if (in_array($varType, $txtArray)) {
        $content = " - (<a href=\"#\" onClick=\"window.open('$fileitem', 'viewer','$varJSSettings');\">view</a>)";
    } else if (in_array($varType, $compArray) && $CompressedFilesSupport) {
        $content = " - (<a href=\"index.php?cfile=$fileitem&cftype=$varType\">contents</a>)";
    }
    return $content;
}

function chkCompLineItem($lineitem)
{
    $chk = explode("/", $lineitem);
    if ($chk[count($chk) - 1] == "") {
        return false;
    } else {
        return true;
    }
}

function compDisplay()
{
    $cfile = $_REQUEST["cfile"];
    $ctype = $_REQUEST["cftype"];
    $catc = null;
    $content = "";
    if (isset($_REQUEST["cact"])) $cact = $_REQUEST["cact"];
    if ($ctype == "zip") {
        $outlines = explode("\n", shell_exec("unzip -l " . escapeshellcmd($cfile)));
        $content = "<thead><tr>";
        $content .= "<td nowrap>Name</td>";
        $content .= "<td nowrap>Size</td>";
        $content .= "<td nowrap>Date</td>";
        $content .= "</tr></thead><tbody>";
        for ($i = 3; $i < (count($outlines) - 3); $i++) {
            foreach (explode(" ", $outlines[$i]) as $ti) {
                if (trim($ti) != "") $items[] = $ti;
            }
            if (!chkCompLineItem($items[3])) {
                $url = "";
            } else {
                $url = "index.php?cfile=$cfile&cftype=zip&getfile=" . $items[3];
            }
            $content .= "<tr>";
            $content .= "<td nowrap><a href='{$url}'>{$items[3]}</a></td>";
            $content .= "<td nowrap>{$items[0]}</td>";
            $content .= "<td nowrap> {$items[1]} {$items[2]}</td>";
            $content .= "</tr>";
            unset($items);
        }
        $content .= "</tbody>";
    } else if (($ctype == "tgz") || ($ctype == "gz")) {
        $outlines = split("\n", shell_exec("tar -ztvf " . escapeshellcmd($cfile)));
        if (count($outlines) > 1) {
            $content = "<thead><tr>";
            $content .= "<td width=100%>Name</td>";
            $content .= "<td nowrap>Size</b></td>";
            $content .= "<td nowrap>Date</b></td>";
            $content .= "</tr></thead><tbody>";
            for ($i = 0; $i < (count($outlines) - 1); $i++) {
                foreach (explode(" ", $outlines[$i]) as $ti) {
                    if (trim($ti) != "") $items[] = $ti;
                }
                if (!chkCompLineItem($items[5])) {
                    $url = "";
                } else {
                    $url = "index.php?cfile=$cfile&cftype=tgz&getfile=" . $items[5];
                }
                $content .= "<tr>";
                $content .= "<td width=100% nowrap><a href='{$url}'>{$items[5]}</a></td>";
                $content .= "<td nowrap>{$items[2]}</td>";
                $content .= "<td nowrap>{$items[3]} {$items[3]}</td>";
                $content .= "</tr>";
                unset($items);
            }
            $content .= "</tbody>";
        } else {
            $outlines = explode("\n", shell_exec("gunzip -lv " . escapeshellcmd($cfile)));
            $content = "<thead><tr>";
            $content .= "<td width=100%>Name</td>";
            $content .= "<td nowrap>Compressed Size</td>";
            $content .= "<td nowrap>Original Size</td>";
            $content .= "<td nowrap>Ratio</td>";
            $content .= "<td nowrap>Date</td>";
            $content .= "<td nowrap>crc</td>";
            $content .= "</tr></thead><tbody>";
            for ($i = 1; $i < (count($outlines) - 1); $i++) {
                foreach (explode(" ", $outlines[$i]) as $ti) {
                    if (trim($ti) != "") $items[] = $ti;
                }
                $url = "index.php?cfile=$cfile&cftype=gz&getfile=" . $items[8];
                $content .= "<tr>";
                $content .= "<td width=100% nowrap><a href='{$url}'>{$items[8]}</a></td>";
                $content .= "<td nowrap>{$items[5]}</td>";
                $content .= "<td nowrap>{$items[6]}</td>";
                $content .= "<td nowrap>{$items[7]}</td>";
                $content .= "<td nowrap>{$items[2]} {$items[3]} {$items[4]}</td>";
                $content .= "<td nowrap>{$items[1]}</td>";
                $content .= "</tr>";
                unset($items);
            }
            $content .= "</tbody>";
        }
    }
    return $content;
}

function display_size($file_size)
{
    if ($file_size >= 1073741824) {
        $file_size = round($file_size / 1073741824 * 100) / 100 . "g";
    } elseif ($file_size >= 1048576) {
        $file_size = round($file_size / 1048576 * 100) / 100 . "m";
    } elseif ($file_size >= 1024) {
        $file_size = round($file_size / 1024 * 100) / 100 . "k";
    } else {
        $file_size = $file_size . "b";
    }
    return $file_size;
}

function dirGather()
{
    $dirtext = array();
    $handle = opendir(".");
    $content = "<tbody>";
    //while (false!=($file = readdir($handle))) {
    while ($fileitem = readdir($handle)) {
        if (($fileitem != "index.txt") && ($fileitem != "index.php")) {
            $filetype = fType($fileitem);
            if ($filetype == "dir") {
                $dirtext[] = "$fileitem";
            } else {
                $context[] = "$fileitem";
            }
        }
    }
    if (count($dirtext)) {
        sort($dirtext);
        for ($i = 0; $i < count($dirtext); $i++) {
            $fileitem = $dirtext[$i];
            $lastchanged = filectime($fileitem);
            $changeddate = date("d-m-Y H:i:s", $lastchanged);
            $filesize = display_size(filesize($fileitem));
            $filetype = fType($fileitem);
            $viewfile = fileView($fileitem);
            $content .= "<tr><td>{$filetype}</td>";
            $content .= "<td><a href=\"{$fileitem}\">{$fileitem}</a></td>";
            $content .= "<td>{$filesize}</td>";
            $content .= "<td>{$changeddate}</td></tr>";
        }
    }
    if ($context) {
        sort($context);
        for ($i = 0; $i < count($context); $i++) {
            $fileitem = $context[$i];
            $lastchanged = filectime($fileitem);
            $changeddate = date("d-m-Y H:i:s", $lastchanged);
            $filesize = display_size(filesize($fileitem));
            $filetype = fType($fileitem);
            $viewfile = fileView($fileitem);
            $content .= "<tr><td>{$filetype}</td>";
            $content .= "<td><a href=\"{$fileitem}\">{$fileitem}</a> {$viewfile}</td>";
            $content .= "<td>{$filesize}</td>";
            $content .= "<td>{$changeddate}</td></tr>";

        }
    }
    $content .= "</tbody>";
    return $content;
}

function diskStats($scriptStats)
{
    if ($scriptStats) {
//		$diskTotal = display_size(disk_total_space("/"));
        $diskFree = display_size(diskfreespace("/"));
        $content = "<table width=100% class='table table-striped' >";
        $content .= "<tr><td width=150>Free Disk Space:</td><td>{$diskFree}</td></tr>";
//		$content .= "<tr><td width=150>Total Disk Space:</td><td>{$diskFree}</td></tr>";
        $content .= "</table>";
        print($content);
    }
}

if (isset($_REQUEST["getfile"]) && isset($_REQUEST["cfile"]) && $CompressedFilesSupport) {
    header("Content-type: application/octet-stream");
    if ($_REQUEST["cftype"] == "tgz") {
        $getfile = split("/", $_REQUEST["getfile"]);
        $getfile = $getfile[count($getfile) - 1];
        header("Content-Disposition: inline; filename=" . $getfile);
        $execmd = "tar -zxvf " . escapeshellcmd($_REQUEST["cfile"]) . " " . escapeshellcmd($_REQUEST["getfile"]) . " -O";
    } else if ($_REQUEST["cftype"] == "gz") {
        header("Content-Disposition: inline; filename=" . $_REQUEST["getfile"]);
        $execmd = "gunzip -dc " . escapeshellcmd($_REQUEST["cfile"]);
    } else if ($_REQUEST["cftype"] == "zip") {
        $getfile = split("/", $_REQUEST["getfile"]);
        $getfile = $getfile[count($getfile) - 1];
        header("Content-Disposition: inline; filename=" . $getfile);
        $execmd = "unzip -p " . escapeshellcmd($_REQUEST["cfile"]) . " " . escapeshellcmd($_REQUEST["getfile"]);
    }
    print(shell_exec($execmd));
    exit();
}
?>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <?php if ($scriptLocation == "") {
        print($scriptCSS);
    } else {
        print("<LINK REL=stylesheet HREF=\"$scriptLocation\" TYPE=\"text/css\">");
    } ?>
</head>
<body>
<?php
diskStats($scriptStats);
print(dirHeader());
print(dirTable(1));
print(dirGather());
print(dirTable(0));
print(dirFooter());
diskStats($scriptStats);
if (isset($_REQUEST["cfile"]) && is_file($_REQUEST["cfile"]) && $CompressedFilesSupport
    && isset($_REQUEST["cftype"]) && in_array($_REQUEST["cftype"], $compArray)
) {
    print(compHeader());
    print(compDisplay());
    print(compFooter());
}
@include("index.txt");
print("<table width=100%><tr><td>Directory listing generated by: <a href=\"https://github.com/KaiDream/getDownNow\">getDownNow</a> ver. 2.0.0</td></tr></table>");
?>
</body>
</html>

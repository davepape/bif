<?php
session_start();

require_once 'init-basic.php';

$userPrivs = false;

function loggedIn()
    {
    return ((array_key_exists('userid',$_SESSION)) && ($_SESSION['userid'] > 0));
    }

function requireLogin()
    {
    if (!loggedIn())
        {
        header('Location: .');
        die();
        }
    }

function requirePrivilege($priv)
    {
    if (hasPrivilege($priv))
        return;
    if (is_array($priv))
        log_message('lacks ' . $priv[0] . ' (or other) privilege - ' . $_SERVER['HTTP_REFERER']);
    else
        log_message('lacks ' . $priv . ' privilege - ' . $_SERVER['HTTP_REFERER']);
    header('Location: .');
    die();
    }

function hasPrivilege($priv)
    {
    if (!loggedIn())
        return false;
    if (!isset($db))
        connectDB();
    global $userPrivs,$db;
    if ($userPrivs === false)
        {
        $stmt = dbPrepare('select privs from user where id=?');
        $stmt->bind_param('i',$_SESSION['userid']);
        if (!$stmt->execute())
            die($stmt->error);
        $stmt->bind_result($userPrivs);
        $stmt->fetch();
        $stmt->close();
        }
    if (is_array($priv))
        {
        foreach ($priv as $p)
            if (stripos($userPrivs,'/' . $p . '/') !== false)
                return true;
        }
    else
        {
        if (stripos($userPrivs,'/' . $priv . '/') !== false)
                return true;
        }
    return false;
    }


function bifPageheader($title,$headerExtras='')
{
echo <<<ENDSTRING
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
ENDSTRING;
echo $headerExtras;
echo <<<ENDSTRING
<link type="text/css" rel="stylesheet" media="all" href="style2.css" />
<title>
ENDSTRING;
if ($title != '')
    echo $title . ' | ';
echo <<<ENDSTRING
Buffalo Infringement Festival</title>
</head>
<body>
<div class="menubar">
 <table class="menubar">
 <tr>
 <td>Buffalo Infringement database</td>
 <td> : </td>
 <td><a href="." title="" class="active">Proposals</a></td>
 <td> | </td>
 <td><a href="/contact.php" title="" class="active">Contact</a></td>
 <tr>
 </table>
</div>
<h1 style="background-color: #eec; border-radius: 2em">
$title
</h1>
ENDSTRING;
}


function bifPagefooter()
{
echo <<<ENDSTRING
</body>
</html>
ENDSTRING;
}
?>

<?php
require_once 'init.php';
connectDB();
requirePrivilege(array('scheduler','organizer'));
require_once 'util.php';

$type = POSTvalue('type');
if ($type=='')
    {
    header('Location:newOrgField.php');
    die();
    }
$field = POSTvalue('field',0);
$label = POSTvalue('label');
$default = POSTvalue('default');

bifPageheader('make summary field');
    
$neworgfields = array();
$stmt = dbPrepare('select id, title, info_json, forminfo_json, orgfields_json from proposal where deleted=0 order by title');
$stmt->execute();
$stmt->bind_result($id,$title,$info_json,$forminfo_json,$orgfields_json);
echo "<table>\n";
echo "<tr><th>proposal</th><th>existing value</th><th>new value</th></tr>\n";
while ($stmt->fetch())
    {
    $info = json_decode($info_json,true);
    if (!isset($info[1][1]))
        continue;
    if ($info[1][1] != $type)
        continue;
    if ($title == '')
        $title = '!!NEEDS A TITLE!!';
    $orgfields = json_decode($orgfields_json,true);
    $forminfo = json_decode($forminfo_json,true);
    if ($field == 0)
        $new = $default;
    else
        $new = field($forminfo,$field);
    if (is_array($orgfields) && array_key_exists($label,$orgfields))
        $old = $orgfields[$label];
    else
        $old = '';
    echo "<tr><td>$title</td><td>$old</td><td>$new</td></tr>\n";
    $orgfields[$label] = $new;
    $neworgfields[$id] = $orgfields;
    }
echo "</table>\n";
$stmt->close();

foreach ($neworgfields as $id=>$orgfields)
    {
    $orgfields_json = json_encode($orgfields);
    $stmt = dbPrepare('update proposal set orgfields=? where id=?');
    $stmt->bind_param('si',$orgfields_json,$id);
    $stmt->execute();
    $stmt->close();
    }


log_message("added new summary field '$label'");
if (isset($_SESSION['preferences']['summaryFields']))
    {
    $_SESSION['preferences']['summaryFields'][] = $label;
    savePreferences();
    }

bifPagefooter();

function field($form,$n)
    {
    $i = 1;
    foreach ($form as $f)
        {
        if ($i == $n)
            return $f;
        $i = $i + 1;
        }
    }
?>

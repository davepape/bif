<?php
require_once 'init.php';
connectDB();
requirePrivilege(array('scheduler','organizer'));
require_once 'util.php';

$header = <<<ENDSTRING
<script src="jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
$(document).ready(function() {
 });
</script>
ENDSTRING;

$stmt = dbPrepare('select `proposal`.`id`, `proposerid`, `name`, `title`, `orgfields_json` from `proposal` join `user` on `proposerid`=`user`.`id` where `deleted` = 1 and `festival`=? order by `title`');
$stmt->bind_param('i',getFestivalID());

class propRow
    {
    function __construct($id,$title,$proposer_id,$proposer_name,$orgfields)
        {
        $this->id = $id;
        $this->title = $title;
        $this->proposer_id = $proposer_id;
        $this->proposer_name = $proposer_name;
        $this->orgfields = $orgfields;
        }
    function title()
        {
        return '<a href="proposal.php?id=' . $this->id . '">' . $this->title . '</a>';
        }
    function proposer()
        {
        return '<a href="user.php?id=' . $this->proposer_id . '">' . $this->proposer_name . '</a>';
        }
    function summary($labels)
        {
        $s = '';
        $i = 0;
        foreach ($labels as $l)
            {
            $idnum = $this->id . '_' . $i;
            $value = $this->orgfields[$l];
            $s .= '<td><span id="edit_' . $idnum . '" class="edit_info"><form method="POST" action="api.php"><input type="hidden" name="command" value="changeProposalOrgfield" /><input type="hidden" name="proposal" value="' . $this->id . '" /><input type="hidden" name="fieldlabel" value="' . $l . '" /><input type="text" name="newinfo" size="5" value="' . $value . '" /></form></span><span id="show_' . $idnum . '" class="show_info" onclick="showEditor(\'' . $idnum . '\');">' . $value . '</span></td>';
            $i = $i + 1;
            }
        return $s;
        }
    }

function array_contains($v,$a)
    {
    foreach ($a as $i)
        if ($v == $i)
            return true;
    return false;
    }

$rows = array();
$stmt->execute();
$stmt->bind_result($id,$proposer_id,$proposer_name,$title,$orgfields_json);
while ($stmt->fetch())
    {
    if ($title == '')
        $title = '!!NEEDS A TITLE!!';
    $orgfields = json_decode($orgfields_json,true);
    $rows[] = new propRow($id,$title,$proposer_id,$proposer_name,$orgfields);
    }
$stmt->close();

bifPageheader('deleted projects', $header);

echo "<table class=\"maintable\">\n";
echo "<tr><th>title</th><th>proposer</th>";
echo "</tr>\n";
foreach ($rows as $r)
    echo '<tr><td>' . $r->title() . '</td><td>' . $r->proposer() . "</td></tr>\n";
echo "</table>\n";

bifPagefooter();
?>

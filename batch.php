<?php
require_once 'init.php';
connectDB();
requirePrivilege(array('scheduler','organizer'));
require_once 'util.php';
require '../bif.php';

$id = GETvalue('id',0);

if ($id != 0)
    {
    $row = dbQueryByID('select name,description from `batch` where id=?',$id);
    $pageTitle = 'batch: ' . $row['name'];
    $pageDescription = "<p>$row[description]</p>\n";
    $pageDescription .= "<p><a href='editBatch.php?id=$id'>[edit batch]</a>";
    $pageDescription .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='batchEmail.php?id=$id'>[email addresses]</a></p>";
    }
else
    {
    $pageTitle = 'all proposals';
    $pageDescription = '';
    }

$header = <<<ENDSTRING
<script src="jquery-1.9.1.min.js" type="text/javascript"></script>
<style type="text/css" title="currentStyle">
    @import "dataTables/media/css/demo_page.css";
    @import "dataTables/media/css/demo_table.css";
</style>
<script type="text/javascript" language="javascript" src="dataTables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
function showEditor(name)
    {
    $('.edit_info').hide();
    $('.show_info').show();
    $('#show_' + name).hide();
    $('#edit_' + name).show();
    }
function hideEditor(name)
    {
    $('#show_' + name).show();
    $('#edit_' + name).hide();
    }

$(document).ready(function() {
    $('.edit_info').hide();
        $('#maintable').dataTable( {
            "bPaginate": false,
            "aaSorting": [[ 0, "asc" ], [1, "asc"]],
            })
 });
</script>
<link rel="stylesheet" href="style.css" type="text/css" />
ENDSTRING;

if ($id != 0)
    {
    $stmt = dbPrepare('select proposal.id, proposerid, name, title, orgfields from proposal join user on proposerid=user.id join proposalBatch on proposal.id=proposalBatch.proposal_id where proposalBatch.batch_id=? and deleted=0 order by title');
    $stmt->bind_param('i',$id);
    }
else
    $stmt = dbPrepare('select `proposal`.`id`, `proposerid`, `name`, `title`, `orgfields` from `proposal` join `user` on `proposerid`=`user`.`id` where `deleted` = 0 order by `title`');

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
            if (is_array($this->orgfields) && array_key_exists($l,$this->orgfields))
                $value = $this->orgfields[$l];
            else
                $value = '';
            if ($value == '')
                $value = '_';
            $s .= '<td><span id="edit_' . $idnum . '" class="edit_info"><form method="POST" action="api.php"><input type="hidden" name="command" value="changeProposalOrgfield" /><input type="hidden" name="proposal" value="' . $this->id . '" /><input type="hidden" name="fieldlabel" value="' . $l . '" /><input type="text" name="newinfo" size="5" value="' . $value . '" /></form></span><span id="show_' . $idnum . '" class="show_info" onclick="showEditor(\'' . $idnum . '\');">' . $value . '</span></td>';
            $i = $i + 1;
            }
        return $s;
        }
    }

function addSummaryLabels(&$labels,$orgfields)
    {
    if (!is_array($orgfields))
        return;
    foreach ($orgfields as $k=>$v)
        if (!in_array($k,$labels))
            $labels[] = $k;
    }

$rows = array();
$labels = array();
$stmt->execute();
$stmt->bind_result($id,$proposer_id,$proposer_name,$title,$orgfields_ser);
while ($stmt->fetch())
    {
    if ($title == '')
        $title = '!!NEEDS A TITLE!!';
    $orgfields = unserialize($orgfields_ser);
    $rows[] = new propRow($id,$title,$proposer_id,$proposer_name,$orgfields);
    addSummaryLabels($labels,$orgfields);
    }
$stmt->close();

bifPageheader($pageTitle, $header);
echo $pageDescription;

echo "<table class=\"maintable\">\n";
echo "<tr><th>title</th><th>proposer</th>";
foreach ($labels as $l)
    echo "<th>$l</th>";
echo "</tr>\n";
foreach ($rows as $r)
    echo '<tr><td>' . $r->title() . '</td><td>' . $r->proposer() . '</td>' . $r->summary($labels) . "</tr>\n";
echo "</table>\n";

bifPagefooter();
?>

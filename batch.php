<?php
require_once 'init.php';
connectDB();
requirePrivilege(array('scheduler','organizer'));
require_once 'util.php';

$id = GETvalue('id',0);

if ($id != 0)
    {
    $row = dbQueryByID('select name,description from `batch` where id=?',$id);
    $pageTitle = 'batch: ' . $row['name'];
    $pageDescription = "<p>$row[description]</p>\n";
    $pageDescription .= "<p>";
    $pageDescription .= "<a href='batchEmail.php?id=$id'>[email addresses]</a>\n";
    if (hasPrivilege('scheduler'))
        {
        $pageDescription .= "&nbsp;&nbsp;<a href='editBatch.php?id=$id'>[edit batch]</a>\n";
        $pageDescription .= "&nbsp;&nbsp;<a href='newBatchColumn.php?id=$id'>[new column]</a>\n";
        $pageDescription .= "&nbsp;&nbsp;<a href='autobatch.php?id=$id'>[auto-add to this batch]</a>\n";
        $pageDescription .= "&nbsp;&nbsp;<a href='batchChangeContact.php?id=$id'>[change festival contact for all]</a>\n";
        $pageDescription .= "&nbsp;&nbsp;<a href='batchAddField.php?id=$id'>[add info field to all]</a>\n";
        }
    $pageDescription .= "</p>\n";
    }
else
    {
    $pageTitle = 'all proposals';
    $pageDescription = '';
    }

$header = <<<ENDSTRING
<script src="jquery.min.js" type="text/javascript"></script>
<link type="text/css" rel="stylesheet" href="tablesorter.css" />
<script src="jquery.tablesorter.min.js" type="text/javascript"></script>
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
    $('#batchtable').tablesorter();
 });
</script>
ENDSTRING;

bifPageheader($pageTitle, $header);
echo $pageDescription;


class propRow
    {
    function __construct($id,$title,$proposer_id,$proposer_name,$orgfields,$submitted)
        {
        $this->id = $id;
        $this->title = $title;
        $this->proposer_id = $proposer_id;
        $this->proposer_name = $proposer_name;
        $this->orgfields = $orgfields;
        $this->submitted = $submitted;
        }
    function title()
        {
        return '<a href="proposal.php?id=' . $this->id . '">' . $this->title . '</a>';
        }
    function proposer()
        {
        return '<a href="user.php?id=' . $this->proposer_id . '">' . $this->proposer_name . '</a>';
        }
    function submitted()
        {
        return $this->submitted;
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

if ($id != 0)
    {
    $stmt = dbPrepare('select proposal.id, proposerid, name, title, orgfields, submitted from proposal join user on proposerid=user.id join proposalBatch on proposal.id=proposalBatch.proposal_id where proposalBatch.batch_id=? and deleted=0 order by title');
    $stmt->bind_param('i',$id);
    }
else
    {
    $festival = GETvalue('festival',getFestivalID());
    $stmt = dbPrepare('select `proposal`.`id`, `proposerid`, `name`, `title`, `orgfields`, `submitted` from `proposal` join `user` on `proposerid`=`user`.`id` where `deleted` = 0 and `festival` = ? order by `title`');
    $stmt->bind_param('i',$festival);
    }

$rows = array();
$labels = array();
$stmt->execute();
$stmt->bind_result($id,$proposer_id,$proposer_name,$title,$orgfields_ser,$submitted);
while ($stmt->fetch())
    {
    if ($title == '')
        $title = '!!NEEDS A TITLE!!';
    $orgfields = unserialize($orgfields_ser);
    $rows[] = new propRow($id,$title,$proposer_id,$proposer_name,$orgfields,$submitted);
    addSummaryLabels($labels,$orgfields);
    }
$stmt->close();
if (isset($_SESSION['preferences']['summaryFields']))
    {
    $mylabels = array();
    foreach ($_SESSION['preferences']['summaryFields'] as $s)
        if (in_array($s,$labels))
            $mylabels[] = $s;
    $labels = $mylabels;
    }
sort($labels);

echo "<table id=\"batchtable\" class=\"tablesorter\">\n";
echo "<thead><tr><th>title</th><th>proposer</th><th>submitted</th>";
foreach ($labels as $l)
    echo "<th>$l</th>";
echo "</tr></thead>\n";
echo "<tbody>\n";
foreach ($rows as $r)
    {
    echo '<tr><td>' . $r->title() . '</td><td>' . $r->proposer() . '</td><td>' . $r->submitted() . '</td>' . $r->summary($labels) . "</tr>\n";
    }
echo "</tbody>\n";
echo "</table>\n";

bifPagefooter();
?>

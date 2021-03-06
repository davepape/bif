<?php
require_once 'init.php';
connectDB();
requirePrivilege('scheduler');
require_once 'util.php';

$proposal_id = GETvalue('proposalid',0);
if ($proposal_id == 0)
    errorAndQuit('ERROR: no proposal id given for newOwner');

$user_id = GETvalue('userid',0);
if ($user_id == 0)
    errorAndQuit('ERROR: no user id given for newOwner');

$oldrow = dbQueryByID('select proposerid from proposal where id=?',$proposal_id);

$stmt = dbPrepare('update proposal set proposerid=? where id=?');
$stmt->bind_param('ii',$user_id,$proposal_id);
if (!$stmt->execute())
    errorAndQuit("Database error: " . $stmt->error,true);
$stmt->close();

log_message("changed ownership of proposal $proposal_id from user $oldrow[id] to $user_id");

header("location:proposal.php?id=$proposal_id");
?>

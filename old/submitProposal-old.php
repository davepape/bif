<?php
require_once 'init.php';
connectDB();
require_once 'util.php';
require_once 'scheduler.php';
$post_ser = serialize($_POST);
dumpData('submitProposal POST',$post_ser);
$get_ser = serialize($_GET);
dumpData('submitProposal GET',$get_ser);
requirePrivilege(array('scheduler','confirmed'),'submitting proposal');

bifPageheader('proposal submitted');
$formtype = POSTvalue('formtype');
$proposaltype = POSTvalue('Proposal_Type');
$festival = getFestivalID();
$batchid = getBatch($formtype,$festival);
$orgcontact = orgContact($proposaltype);
$title = POSTvalue('title');
if (trim($title) == '') $title = 'NEEDS A TITLE';
$proposerid = $_SESSION['userid'];
log_message("createProposal \"$title\", type \"$formtype\" / \"$proposaltype\"");

if ($formtype == 'music')
    createMusicProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'dance')
    createDanceProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'theatre')
    createTheatreProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'film')
    createFilmProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'visualart')
    createVisualartProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'literary')
    createLiteraryProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'street')
    createStreetProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else if ($formtype == 'universal')
    createUniversalProposal($title,$proposerid,$festival,$batchid,$orgcontact);
else
    {
    log_message("createProposal failed - unknown type \"$formtype\"");
    die('ERROR: UNKNOWN PROPOSAL TYPE');
    }
?>
<p>
Congratulations!<br>
You have successfully submitted a proposal for the 2016 Buffalo
Infringement Festival that runs from July 28 - August 7, 2016!<br>
Be sure to check out your proposal which should now be displayed prominently on the home page!
<br>
Remember - all proposals are accepted.  As soon as you hear back from
your genre organizer, you're in!<br>
You can alter your proposal at any time by logging into our site.<br>
Please check your email often and respond to messages from organizers
promptly (please check your spam folder, in case our messages end up
there).<br>
Expect an influx of correspondence after May 1.
</p>
<p>
Be sure to <a href="http://infringebuffalo.org/forum/" target="_blank">join our Infringement Forum!</a>  It's a great place to ask questions, collaborate with other artist and get more involved with Infringement in general.
</p>

<p>
If you have any questions, ask away:
</p>
<ul>
<li>General/PR: pr@infringebuffalo.org / info@infringebuffalo.org
<li>Music: Curt, steelcrazybooking@gmail.com
<li>Theater: Jessica, jessicaknoerl@gmail.com
<li>Poetry/Literary: Marek, b00bflo@gmail.com
<li>Dance: Leslie, danceundertheradar@gmail.com
<li>Film: Tom, tms@kitefishlabs.com
<li>Street performance: David, dga8787@aol.com
<li>Visual Arts: Cat/Amy, visualinfringement@live.com
</ul>

<p>
Facebook: <a href="https://www.facebook.com/groups/22033482171/">https://www.facebook.com/groups/22033482171/</a> <a href="https://www.facebook.com/InfringeEveryDay">https://www.facebook.com/InfringeEveryDay</a><br>
Twitter: <a href="https://twitter.com/InfringeBuffalo">https://twitter.com/InfringeBuffalo</a>
</p>

<p>
Infringe Everyday!
</p>

<?php
bifPagefooter();


function userInfo($email)
    {
    return dbQueryByString('select id,email,name,phone from user where email=?', $email);
    }

function orgContact($proposaltype)
    {
    if ($proposaltype == 'Music')
        return userInfo('steelcrazybooking@gmail.com');
    else if ($proposaltype == 'Dance')
        return userInfo('danceundertheradar@gmail.com');
    else if ($proposaltype == 'Theatre')
        return userInfo('jessicaknoerl@gmail.com');
    else if ($proposaltype == 'Film/Video')
        return userInfo('tms@kitefishlabs.com');
    else if ($proposaltype == 'Visual_Art')
        return userInfo('visualinfringement@live.com');
    else if ($proposaltype == 'Literary')
        return userInfo('marekp@roadrunner.com');
    else 
        return userInfo('depape@buffalo.edu');
    }

function contactInfo()
    {
    $contactname = POSTvalue('contactname');
    $contactemail = POSTvalue('contactemail');
    $contactphone = POSTvalue('contactphone');
    $contactaddress = POSTvalue('contactaddress');
    $contactfacebook = POSTvalue('contactfacebook');
    $contactmethod = POSTvalue('bestcontactmethod');
    return "$contactname\nE-mail: $contactemail\nPhone: $contactphone\nAddress: $contactaddress\nFacebook: $contactfacebook\nBest contact method: $contactmethod";
    }

function secondContactInfo()
    {
    $contact2name = POSTvalue('secondcontactname');
    $contact2email = POSTvalue('secondcontactemail');
    $contact2phone = POSTvalue('secondcontactphone');
    $contact2address = POSTvalue('secondcontactaddress');
    return "$contact2name\nE-mail: $contact2email\nPhone: $contact2phone\nAddress: $contact2address";
    }
	
function createUniversalProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Secondary contact info', secondContactInfo());
	foreach ($_POST as $param_name => $param_val){
	  if ($param_name !== 'contactname' || $param_name !== 'contactemail' || $param_name !== 'contactphone' || $param_name !== 'contactaddress' || $param_name !== 'contactfacebook' || $param_name !== 'bestcontactmethod' || $param_name !== 'secondcontactname' || $param_name !== 'secondcontactphone' || $param_name !== 'secondcontactaddress' || $param_name !== 'secondcontactemail'){
	  addInfo($info,$param_name, $param_val);
	  }
	}
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }
	
function createTheatreProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Secondary contact info', secondContactInfo());
    addInfo($info,'Type', 'theatre');
    addInfo($info,'Organization', POSTvalue('organization'));
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Description for organizers', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Number of performers', POSTvalue('numberperformers'));
    addInfo($info,'Setup time', POSTvalue('setuptime'));
    addInfo($info,'Length of performance', POSTvalue('length'));
    addInfo($info,'Strike time', POSTvalue('striketime'));
    addInfo($info,'Number of performances', POSTvalue('numberperformances'));
    addInfo($info,'Pre-arranged venue', POSTvalue('hasvenue'));
    addInfo($info,'Street theatre', POSTvalue('streettheatre'));
    addInfo($info,'Interested in non-traditional venue', POSTvalue('nontraditionalvenue'));
    addInfo($info,'Description of desired venue', POSTvalue('venue'));
    addInfo($info,'Requested venue features', POSTvalue('venuefeatures'));
    addInfo($info,'All performers over 21', POSTvalue('over21'));
    addInfo($info,'Previous infringement festivals', POSTvalue('pastfestivals'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createMusicProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Secondary contact info', secondContactInfo());
    addInfo($info,'Type', 'music');
    addInfo($info,'# of band members',POSTvalue('numberperformers'));
    addInfo($info,'Names and roles', POSTvalue('bandnames'));
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Facebook etc', POSTvalue('otherwebsite'));
    addInfo($info,'Everyone over 21', POSTvalue('over21'));
    addInfo($info,'Any other proposals', POSTvalue('othershows'));
    addInfo($info,'Main genre', POSTvalue('genre'));
    addInfo($info,'Secondary genre', POSTvalue('secondgenre'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Current/previous groups/projects', POSTvalue('otherbandgroups'));
    addInfo($info,'Have own PA', POSTvalue('havepa'));
    addInfo($info,'Share PA', POSTvalue('sharepa'));
    addInfo($info,'Play without amplification', POSTvalue('withoutamp'));
    addInfo($info,'Willing to busk', POSTvalue('busking'));
    addInfo($info,'Share drum kit', POSTvalue('sharedrums'));
    addInfo($info,'Have tables and mixer', POSTvalue('djowntables'));
    addInfo($info,'Describe gear etc', POSTvalue('bandgear'));
    addInfo($info,'Equipment to share', POSTvalue('shareequipment'));
    addInfo($info,'How loud', POSTvalue('howloud'));
    addInfo($info,'Setup time', POSTvalue('setuptime'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Pre-drafted for Anti-warped', POSTvalue('antiwarped'));
    addInfo($info,'Other gigs besides Anti-warped', POSTvalue('antiwarpedplus'));
    addInfo($info,'Opening or closing ceremonies', POSTvalue('openingceremonies'));
    addInfo($info,'Type of venue', POSTvalue('venue'));
    addInfo($info,'Number of shows', POSTvalue('numberperformances'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createFilmProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Type','film');
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Length', POSTvalue('length'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Family friendly', POSTvalue('familyfriendly'));
    addInfo($info,'Over age 21', POSTvalue('over21'));
    addInfo($info,'Venue needs', POSTvalue('venuefeatures'));
    addInfo($info,'Other infringement projects', POSTvalue('othershows'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous infringement festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createVisualartProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Type','visualart');
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Medium', POSTvalue('medium'));
    addInfo($info,'Number of pieces', POSTvalue('numberpieces'));
    addInfo($info,'Dimensions of each piece', POSTvalue('dimensions'));
    addInfo($info,'Dimensions of entire project', POSTvalue('entiredimensions'));
    addInfo($info,'Pre-arranged venue', POSTvalue('hasvenue'));
    addInfo($info,'Desired venue', POSTvalue('venue'));
    addInfo($info,'Availability (if work involves performance/presentation)', POSTvalue('visualartpresentation'));
    addInfo($info,'Other infringement projects', POSTvalue('othershows'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous infringement festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createDanceProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Type', 'dance');
    addInfo($info,'Group', POSTvalue('organization'));
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Names of all performers', POSTvalue('performernames'));
    addInfo($info,'Over age 21', POSTvalue('over21'));
    addInfo($info,'Setup time', POSTvalue('setuptime'));
    addInfo($info,'Length of performance', POSTvalue('length'));
    addInfo($info,'Strike time', POSTvalue('striketime'));
    addInfo($info,'Number of performances', POSTvalue('numberperformances'));
    addInfo($info,'Pre-arranged venue', POSTvalue('hasvenue'));
    addInfo($info,'Venue needs', POSTvalue('venuefeatures'));
    addInfo($info,'Can perform in non-traditional space', POSTvalue('nontraditionalvenue'));
    addInfo($info,'Willing to perform to live band', POSTvalue('performwithband'));
    addInfo($info,'Admission', POSTvalue('admission'));
    addInfo($info,'Other infringement projects', POSTvalue('othershows'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous infringement festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createLiteraryProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Type', 'literary');
    addInfo($info,'Group', POSTvalue('organization'));
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Names of all performers', POSTvalue('performernames'));
    addInfo($info,'Over age 21', POSTvalue('over21'));
    addInfo($info,'Pre-arranged venue', POSTvalue('hasvenue'));
    addInfo($info,'Venue needs', POSTvalue('venuefeatures'));
    addInfo($info,'Number of performances', POSTvalue('numberperformances'));
    addInfo($info,'Other infringement projects', POSTvalue('othershows'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous infringement festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function createStreetProposal($title,$proposerid,$festival,$batchid,$orgcontact)
    {
    global $festivalNumberOfDays;
    $info = array();
    addInfo($info,'Contact info',contactInfo());
    addInfo($info,'Secondary contact info', secondContactInfo());
    addInfo($info,'Type', 'street');
    addInfo($info,'Website', POSTvalue('website'));
    addInfo($info,'Facebook etc', POSTvalue('otherwebsite'));
    addInfo($info,'Description', POSTvalue('description_org'));
    addInfo($info,'Description for web', POSTvalue('description_web'));
    addInfo($info,'Description for brochure', POSTvalue('description_brochure'));
    addInfo($info,'Image link', POSTvalue('imagelink'));
    addInfo($info,'Performers\' names and roles', POSTvalue('performers'));
    addInfo($info,'Everyone over 21', POSTvalue('over21'));
    addInfo($info,'Experience street performing', POSTvalue('streetexperience'));
    addInfo($info,'Street performer\'s license', POSTvalue('streetlicense'));
    addInfo($info,'Play without amplification', POSTvalue('withoutamp'));
    addInfo($info,'Need outlet', POSTvalue('needoutlet'));
    addInfo($info,'Performance space size', POSTvalue('performancespace'));
    addInfo($info,'Required location features', POSTvalue('venuefeatures'));
    addInfo($info,'Ideal location', POSTvalue('venue'));
    addInfo($info,'Interested in tips or making scene', POSTvalue('tipsorscene'));
    addInfo($info,'Scheduled or busking', POSTvalue('scheduledorbusking'));
    addInfo($info,'Improvisatory', POSTvalue('improvisatory'));
    addInfo($info,'Willing to improvise with other artists', POSTvalue('improvcollaborate'));
    addInfo($info,'Interactive', POSTvalue('interactive'));
    addInfo($info,'Family friendly', POSTvalue('familyfriendly'));
    addInfo($info,'Setup time', POSTvalue('setuptime'));
    addInfo($info,'Length of performance', POSTvalue('length'));
    addInfo($info,'Strike time', POSTvalue('striketime'));
    addInfo($info,'Any other proposals', POSTvalue('othershows'));
    addInfo($info,'Number of performances', POSTvalue('numberperformances'));
    addInfo($info,'Equipment', POSTvalue('equipment'));
    addInfo($info,'Equipment to share', POSTvalue('shareequipment'));
    addInfo($info,'[MUSIC] Main genre', POSTvalue('genre'));
    addInfo($info,'[MUSIC] Secondary genre', POSTvalue('secondgenre'));
    addInfo($info,'[MUSIC] How loud', POSTvalue('howloud'));
    addInfo($info,'[MUSIC] Have vocalist', POSTvalue('vocalist'));
    addInfo($info,'[MUSIC] Dance or theatrical component', POSTvalue('havedanceortheatre'));
    addInfo($info,'[MUSIC] Willing to perform with dancers', POSTvalue('playwithdancers'));
    addInfo($info,'[MUSIC] Willing to improvise to projected film', POSTvalue('playwithfilm'));
    addInfo($info,'[DANCE] Act has sound component', POSTvalue('streetdancehavesound'));
    addInfo($info,'[DANCE] Will provide own sound', POSTvalue('streetdanceownsound'));
    addInfo($info,'[DANCE] Willing to perform to live music', POSTvalue('streetdancelivemusic'));
    addInfo($info,'[DANCE] Would prefer to dance to', POSTvalue('streetdancepreferredmusic'));
    addInfo($info,'[DANCE] Can\'t dance to', POSTvalue('streetdancecantmusic'));
    addInfo($info,'[THEATRE] Genre', POSTvalue('streettheatregenre'));
    addInfo($info,'[THEATRE] Have props/wardrobe', POSTvalue('streettheatreprops'));
    addInfo($info,'[LITERARY] Genre', POSTvalue('streetliterarygenre'));
    addInfo($info,'[FILM] Have own projector', POSTvalue('streetfilmprojector'));
    addInfo($info,'[FILM] Have own screen', POSTvalue('streetfilmscreen'));
    addInfo($info,'[FILM] Have permission to use a wall', POSTvalue('streetfilmwall'));
    addInfo($info,'[FILM] Film has sound', POSTvalue('streetfilmsound'));
    addInfo($info,'[FILM] Would like musicians to improvise', POSTvalue('streetfilmmusicians'));
    addInfo($info,'[FILM] Any specific musicians in mind', POSTvalue('streetfilmmusiciansdetail'));
    addInfo($info,'[VISUAL ART] Medium', POSTvalue('streetartmedium'));
    addInfo($info,'[VISUAL ART] Making in public, or displaying', POSTvalue('streetartmaking'));
    addInfo($info,'How will you help Infringement', POSTvalue('volunteer'));
    addInfo($info,'How does it infringe', POSTvalue('infringe'));
    addInfo($info,'Out of town / housing', POSTvalue('outoftown'));
    addInfo($info,'Previous festivals', POSTvalue('pastfestivals'));
    addInfo($info,'Any questions', POSTvalue('questions'));
    $availability = array();
    for ($d = 0; $d < $festivalNumberOfDays; $d++)
        $availability[$d] = POSTvalue('can_day' . $d);
    insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid);
    }

function addInfo(&$info,$label,$value)
    {
    $info[] = array($label,$value);
    }

function insertProposal($info,$availability,$proposerid,$festival,$title,$orgcontact,$batchid)
    {
    $info_ser = serialize($info);
    $availability_ser = serialize($availability);
    $orgfields_ser = serialize(array());
    $proposalid = newEntityID('proposal');
    $orgcontactid = $orgcontact['id'];
    $formtext = createFormText($info, $availability, $title);
    $forminfo = array(POSTvalue("formtype")=>$formtext);
    $forminfo_ser = serialize($forminfo);
    $stmt = dbPrepare('insert into `proposal` (`id`, `proposerid`, `festival`, `title`, `info`, `availability`, `forminfo`, `orgcontact`, `orgfields`) values (?,?,?,?,?,?,?,?,?)');
    $stmt->bind_param('iiissssis',$proposalid,$proposerid,$festival,$title,$info_ser,$availability_ser,$forminfo_ser,$orgcontactid,$orgfields_ser);
    $stmt->execute();
    $stmt->close();
    if ($batchid != 0)
        {
        $stmt = dbPrepare('insert into `proposalBatch` (`proposal_id`, `batch_id`) values (?,?)');
        $stmt->bind_param('ii',$proposalid,$batchid);
        $stmt->execute();
        $stmt->close();
        }
    emailProposal($formtext,$proposerid,$orgcontact);
    echo "<a href=proposal.php?id=$proposalid>$title</a><br>\n";
    }

function createFormText($info,$availability,$title)
    {
    global $festivalNumberOfDays;
    $text = "Title:\n$title\n\n";
    foreach ($info as $i)
        {
        $text .= $i[0] . ":\n";
        $text .= $i[1] . "\n\n";
        }
    $text .= "\nAvailability:\n";
    for ($i=0; $i < $festivalNumberOfDays; $i++)
        {
        if (array_key_exists($i,$availability))
            $text .= dayToDate($i) . ": " . $availability[$i] . "\n";
        }
    return $text;
    }

function emailProposal($formtext,$proposerid,$orgcontact)
    {
    $body = "The following proposal has been submitted for the Buffalo Infringement Festival:\r\n\r\n" . $formtext;
    $row = dbQueryByID("select email from user where id=?",$proposerid);
    $addr = $row['email'];
    $orgaddr = $orgcontact['email'];
    $subject = "Buffalo Infringement proposal";
    loggedMail($addr, $subject, $body);
    loggedMail($orgaddr, $subject, "(Copy of mail sent to $addr)\r\n\r\n" . $body);
    }

?>

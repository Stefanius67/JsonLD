<?php
use SKien\JsonLD\JsonLDLocalBusiness;

require_once 'autoloader.php';

$oJsonLD = new JsonLDLocalBusiness('SportsActivityLocation');
$oJsonLD->setURL('https://www.mydomain.de');
$oJsonLD->setInfo('Sportsclimbing SC', 'info@mydomain.de', '12345 67890');
$oJsonLD->setDescription('Sportsclimbinbg indoor and outdoor for everyone');
$oJsonLD->setAdress('Street 12', '12345', 'MyTown', '', 'Germany');
$oJsonLD->setLocation(48.3365629, 7.8447896, 'https://www.google.de/maps/place/DAV-Kletterzentrum+Lahr/@48.3365629,7.8447896,156m/data=!3m1!1e3!4m5!3m4!1s0x47912e4949b57841:0xc26f08dacee0a1a9!8m2!3d48.3367173!4d7.8441243');
$oJsonLD->setLogo('elephpant.png');
$oJsonLD->addImage('elephpant.png');
$oJsonLD->addLanguage('de');
$oJsonLD->setPriceRange('$$$');

// and create department...
$oDepartment = new JsonLDLocalBusiness('Organization', true);
// at least the @id MUST be other than the base-id!
$oDepartment->setURL('https://www.mydomain.de/outdoor');
$oDepartment->setInfo('Sportsclimbing SC - Outdoor Center', 'outdoor@mydomain.de');
$oDepartment->setAdress('Another street', '12345', 'MyTown');

$oJsonLD->addDepartment($oDepartment);
?>
<!DOCTYPE html>
<html>
<head>
<title>Json LD Generator</title>
<?php echo $oJsonLD->getHTMLHeadTag(false);?>
</head>
<body>
    <h1>Json LD Generator - Local Business</h1>
    <textarea style="font-family: 'Courier'; width: 100%; white-space: nowrap;" rows="50" spellcheck="false">
    <?php echo $oJsonLD->getJson(true);?>
    </textarea>
</body>
</html>



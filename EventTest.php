<?php
use SKien\JsonLD\JsonLDEvent;
// the autoloader will find all needed files...
require_once 'autoloader.php';

$oJsonLD = new JsonLDEvent();

$oJsonLD->setInfo('A great Concert for all...', new DateTime('2020-06-12 18:30'), new DateTime('2020-06-12 22:00'));
$oJsonLD->setDescription('... would it not be amazing, if these two musicians could perform together!' . PHP_EOL . 'Incredible.');
$oJsonLD->setAdress('Kensington Gore', 'SW7 2AP', 'London', '', 'United Kingdom');
$oJsonLD->setLocation('Royal Albert Hall', 51.5009088, -0.177366, 'https://www.google.com/maps/place/Royal+Albert+Hall/@51.5009088,-0.177366,15z/data=!4m5!3m4!1s0x0:0x5efe9cee35da2fd9!8m2!3d51.5009088!4d-0.177366');
$oJsonLD->addOffer('Seat', 80, 'GBP', $oJsonLD::AVAILABLE_PRE_ORDER, new DateTime('NOW'), 'https://www.tickets.com/rah');
$oJsonLD->addOffer('VIP', 250, 'GBP', $oJsonLD::AVAILABLE_PRE_ORDER, new DateTime('NOW'), 'https://www.tickets.com/rah');
$oJsonLD->setOrganizer('Queen Mum', 'https://www.lissyII.uk');
$oJsonLD->addPerformer('U2');
$oJsonLD->addPerformer('Elton John');
$oJsonLD->addImage('elephpant.png');    // use bigger image - only for test purposes to avoid warning from test tool
?>
<!DOCTYPE html>
<html>
<head>
<title>Json LD Generator</title>
<!-- insert the tag in the head section of the document -->
<?php echo $oJsonLD->getHTMLHeadTag(false);?>
</head>
<body>
    <h1>Json LD Generator - Event</h1>
    <p>You can copy generated JsonLD script to test it in 
        <a target="_blank" href="https://search.google.com/structured-data/testing-tool">
            https://search.google.com/structured-data/testing-tool
        </a>
    </p>
    <textarea style="font-family: 'Courier'; width: 100%; white-space: nowrap;" rows="50" spellcheck="false">
    <?php echo $oJsonLD->getJson(true);?>
    </textarea>
</body>
</html>



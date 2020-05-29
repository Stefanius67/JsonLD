<?php
use SKien\JsonLD\JsonLDArticle;
// the autoloader will find all needed files...
require_once 'autoloader.php';

$oJsonLD = new JsonLDArticle();

$oJsonLD->setPublisher('Sportsclimbing SC');
$oJsonLD->setLogo('elephpant.png');
$oJsonLD->setInfo('New Outdoor climbing facilitry opened', '... and the article...', new DateTime('2020-05-12'), new DateTime('2020-05-13 22:13:42'));
$oJsonLD->setAuthor('Stefanius');
$oJsonLD->addImage('elephpant.png');
?>
<!DOCTYPE html>
<html>
<head>
<title>Json LD Generator</title>
<?php echo $oJsonLD->getHTMLHeadTag(false);?>
</head>
<body>
    <h1>Json LD Generator - Article</h1>
    <p>You can copy generated JsonLD script to test it in <a target="_blank" href="https://search.google.com/structured-data/testing-tool">https://search.google.com/structured-data/testing-tool</a></p>
    <textarea style="font-family: 'Courier'; width: 100%; white-space: nowrap;" rows="50" spellcheck="false">
    <?php echo $oJsonLD->getJson(true);?>
    </textarea>
</body>
</html>



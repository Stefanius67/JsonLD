<?php
declare(strict_types=1);

namespace SKien\Test\JsonLD;

use PHPUnit\Framework\TestCase;
use SKien\JsonLD\JsonLD;

/**
 * @package JsonLD Test
 * @author Stefanius <s.kientzler@online.de>
 * @copyright MIT License - see the LICENSE file for details
 */
class JsonLDTestCase extends TestCase
{
    protected JsonLD $oJsonLD;

    protected function getProtectedMethod(string $strMethod ) : \ReflectionMethod
    {
        // create reflection object to call protected method of JsonLD
        $rflObject = new \ReflectionObject($this->oJsonLD);

        $rflMethod = $rflObject->getMethod($strMethod);
        $rflMethod->setAccessible(true);

        return $rflMethod;
    }

    protected function assertArrayContains($value, array $array, string $message = null) : void
    {
        if (in_array($value, $array)) {
            $this->assertTrue(true);
        } else {
            $this->fail($message ?? 'expected array containing [' . $value . ']');
        }
    }

    protected function assertArrayNotContains($value, array $array, string $message = null) : void
    {
        if (in_array($value, $array)) {
            $this->fail($message ?? 'expected array NOT containing [' . $value . ']');
        } else {
            $this->assertTrue(true);
        }
    }

    /*
     * the CURL request doesn't work reliable:
     * -> Our systems have detected unusual traffic from your computer network.  This page
     *    checks to see if it&#39;s really you sending the requests, and not a robot.
     */
    protected function assertValidJsonLD(string $strJson) : void
    {
        $strPostData = "html=" . urlencode($strJson);

        // cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://validator.schema.org/validate',
            CURLOPT_HTTPHEADER     => ['User-Agent: cURL', 'Content-Type: application/x-www-form-urlencoded;charset=utf-8', 'Accept: */*'],
            CURLOPT_POST => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $strPostData,
        ]);

        $strResponse = curl_exec($curl);
        if (!$strResponse) {
            $this->markTestIncomplete('Issues checking JsonLD validity. (cURL-Error: ' . curl_error($curl) . ')');
        }

        curl_close($curl);

        $aResponse = explode("\n", $strResponse);
        $strJson = $aResponse[1];
        $oResponse = json_decode($strJson);
        if ($oResponse === null) {
            $strError = "Invalid JsonLD object:\n" . $strResponse . "\n" . $strJson;
            $this->fail($strError);
        } elseif ($oResponse->totalNumErrors > 0) {
            $strError = "JsonLD validation failed:";
            foreach ($oResponse->errors as $oError) {
                $strError .= PHP_EOL . '-> ' . $oError->errorType;
                if (count($oError->args) > 0) {
                    $strError .= '(';
                    $strSep = '';
                    foreach ($oError->args as $strArg) {
                        $strError .= $strSep . $strArg;
                        $strSep = ', ';
                    }
                    $strError .= ')';
                }
            }
            $this->fail($strError);
        }

        // valid JsonLD
        $this->assertTrue(true);
    }
}
<?php
// Debug (turn off in production)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

require_once __DIR__ . '/xmlrpc/lib/xmlrpc.inc'; // make sure this path is correct

$ServerURL = "https://zerp.saris.info.tz/api/api_xml-rpc.php";

// Build a *struct* parameter as a single argument
$paramStruct = new xmlrpcval(array(
    "StockID"  => new xmlrpcval("ST001"),
    "Username" => new xmlrpcval("leah"),
    "Password" => new xmlrpcval("zalongwa"),
), "struct");

// Build message (no leading dot)
$Msg = new xmlrpcmsg("xmlrpc_GetStockBalance", array($paramStruct));
print_r($Msg);
exit;
// Client
$Client = new xmlrpc_client($ServerURL);

// Optional: if your PHP can’t verify the server cert during testing
//$Client->setSSLVerifyPeer(false);
//$Client->setSSLVerifyHost(0);

// See full request/response on screen
$Client->setDebug(2);

// Send
$Response = $Client->send($Msg);

// Transport errors?
if (!$Response) {
    die("No response from server");
}

// Server-side fault?
if ($Response->faultCode()) {
    die("Fault {$Response->faultCode()}: " . htmlspecialchars($Response->faultString()) .
        "\n\nRaw:\n" . htmlspecialchars($Response->serialize()));
}

// Decode returned value
$Answer = php_xmlrpc_decode($Response->value());

// Typical webERP success shape: [0, data]
if (is_array($Answer) && isset($Answer[0]) && $Answer[0] == 0) {
    foreach ($Answer[1] as $row) {
        echo htmlspecialchars($row["loccode"]) . " has " . htmlspecialchars($row["quantity"]) . " on hand<br>";
    }
} else {
    // Dump for troubleshooting
    echo "<pre>" . print_r($Answer, true) . "</pre>";
}

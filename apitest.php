<?php
include ("phpxmlrpc/lib/xmlrpc.inc");
$ServerURL = "https://zerp.saris.info.tz/api/api_xml-rpc.php";

$Parameters["StockID"] = new xmlrpcval("ST001");
echo 'line 6: niko hapa: ==> '.$Msg;
exit;
$Parameters["Username"] = new xmlrpcval("leah");
$Parameters["Password"] = new xmlrpcval("zalongwa");

$Msg = new xmlrpcmsg(".xmlrpc_GetStockBalance", $Parameters);
$Client = new xmlrpc_client($ServerURL);
$Response = $Client->send($Msg);
$Answer = php_xmlrpc_decode($Response->value());

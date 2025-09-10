<?php
error_reporting(E_ALL ^ E_NOTICE);  // or: error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);        // (temporarily, while debugging)
include ("xmlrpc/lib/xmlrpc.inc");
$ServerURL = "https://zerp.saris.info.tz/api/api_xml-rpc.php";
$Parameters = array();
$Parameters["StockID"] = new xmlrpcval("ST001");
$Parameters["Username"] = new xmlrpcval("leah");
$Parameters["Password"] = new xmlrpcval("zalongwa");
$Msg = new xmlrpcmsg(".xmlrpc_GetStockBalance", $Parameters);
$Client = new xmlrpc_client($ServerURL);
$Response = $Client->send($Msg);
$Answer = php_xmlrpc_decode($Response->value());

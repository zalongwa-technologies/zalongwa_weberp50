<?php

/* Verify that the supplier number is valid, and doesn't already
   exist.*/
	function VerifySupplierNo($SupplierNumber, $i, $Errors) {
		if ((mb_strlen($SupplierNumber)<1) or (mb_strlen($SupplierNumber)>10)) {
			$Errors[$i] = IncorrectDebtorNumberLength;
		}
		$Searchsql = "SELECT count(supplierid)
  				      FROM suppliers
				      WHERE supplierid='".$SupplierNumber."'";
		$SearchResult = DB_query($Searchsql);
		$Answer = DB_fetch_row($SearchResult);
		if ($Answer[0] != 0) {
			$Errors[$i] = SupplierNoAlreadyExists;
		}
		return $Errors;
	}

/* Verify that the supplier number is valid, and already
   exists.*/
	function VerifySupplierNoExists($SupplierNumber, $i, $Errors) {
		if ((mb_strlen($SupplierNumber)<1) or (mb_strlen($SupplierNumber)>10)) {
			$Errors[$i] = IncorrectDebtorNumberLength;
		}
		$Searchsql = "SELECT count(supplierid)
				      FROM suppliers
				      WHERE supplierid='".$SupplierNumber."'";
		$SearchResult = DB_query($Searchsql);
		$Answer = DB_fetch_row($SearchResult);
		if ($Answer[0] == 0) {
			$Errors[$i] = SupplierNoDoesntExists;
		}
		return $Errors;
	}

/* Check that the name exists and is 40 characters or less long */
	function VerifySupplierName($SupplierName, $i, $Errors) {
		if ((mb_strlen($SupplierName)<1) or (mb_strlen($SupplierName)>40)) {
			$Errors[$i] = IncorrectSupplierNameLength;
		}
		return $Errors;
	}

/* Check that the supplier since date is a valid date. The date
 * must be in the same format as the date format specified in the
 * target webERP company */
	function VerifySupplierSinceDate($suppliersincedate, $i, $Errors) {
		$SQL="SELECT confvalue FROM config where confname='DefaultDateFormat'";
		$Result = DB_query($SQL);
		$MyRow=DB_fetch_array($Result);
		$DateFormat=$MyRow[0];
		if (mb_strstr('/',$PeriodEnd)) {
			$Date_Array = explode('/',$PeriodEnd);
		} elseif (mb_strstr('.',$PeriodEnd)) {
			$Date_Array = explode('.',$PeriodEnd);
		}
		if ($DateFormat=='d/m/Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='m/d/Y') {
			$Day=$DateArray[1];
			$Month=$DateArray[0];
			$Year=$DateArray[2];
		} elseif ($DateFormat=='Y/m/d') {
			$Day=$DateArray[2];
			$Month=$DateArray[1];
			$Year=$DateArray[0];
		} elseif ($DateFormat=='d.m.Y') {
			$Day=$DateArray[0];
			$Month=$DateArray[1];
			$Year=$DateArray[2];
		}
		if (!checkdate(intval($Month), intval($Day), intval($Year))) {
			$Errors[$i] = InvalidSupplierSinceDate;
		}
		return $Errors;
	}

	function VerifyBankAccount($BankAccount, $i, $Errors) {
		if (mb_strlen($BankAccount)>30) {
			$Errors[$i] = InvalidBankAccount;
		}
		return $Errors;
	}

	function VerifyBankRef($BankRef, $i, $Errors) {
		if (mb_strlen($BankRef)>12) {
			$Errors[$i] = InvalidBankReference;
		}
		return $Errors;
	}

	function VerifyBankPartics($BankPartics, $i, $Errors) {
		if (mb_strlen($BankPartics)>12) {
			$Errors[$i] = InvalidBankPartics;
		}
		return $Errors;
	}

	function VerifyRemittance($Remittance, $i, $Errors) {
		if ($Remittance!=0 and $Remittance!=1) {
			$Errors[$i] = InvalidRemittanceFlag;
		}
		return $Errors;
	}

/* Check that the factor company is set up in the weberp database */
	function VerifyFactorCompany($factorco , $i, $Errors) {
		$Searchsql = "SELECT COUNT(id)
					 FROM factorcompanies
					  WHERE id='".$factorco."'";
		$SearchResult = DB_query($Searchsql);
		$Answer = DB_fetch_row($SearchResult);
		if ($Answer[0] == 0) {
			$Errors[$i] = FactorCompanyNotSetup;
		}
		return $Errors;
	}

/* Insert a new supplier in the webERP database. This function takes an
   associative array called $SupplierDetails, where the keys are the
   names of the fields in the suppliers table, and the values are the
   values to insert.
*/
	function InsertSupplier($SupplierDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($SupplierDetails as $key => $Value) {
			$SupplierDetails[$key] = DB_escape_string($Value);
		}
		$Errors=VerifySupplierNo($SupplierDetails['supplierid'], sizeof($Errors), $Errors);
		$Errors=VerifySupplierName($SupplierDetails['suppname'], sizeof($Errors), $Errors);
		if (isset($SupplierDetails['address1'])){
			$Errors=VerifyAddressLine($SupplierDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address2'])){
			$Errors=VerifyAddressLine($SupplierDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address3'])){
			$Errors=VerifyAddressLine($SupplierDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address4'])){
			$Errors=VerifyAddressLine($SupplierDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address5'])){
			$Errors=VerifyAddressLine($SupplierDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address6'])){
			$Errors=VerifyAddressLine($SupplierDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lat'])){
			$Errors=VerifyLatitude($SupplierDetails['lat'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lng'])){
			$Errors=VerifyLongitude($SupplierDetails['lng'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['currcode'])){
			$Errors=VerifyCurrencyCode($SupplierDetails['currcode'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['suppliersince'])){
			$Errors=VerifySupplierSinceDate($SupplierDetails['suppliersince'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($SupplierDetails['paymentterms'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaid'])){
			$Errors=VerifyLastPaid($SupplierDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($SupplierDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankact'])){
			$Errors=VerifyBankAccount($SupplierDetails['bankact'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankref'])){
			$Errors=VerifyBankRef($SupplierDetails['bankref'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankpartics'])){
			$Errors=VerifyBankPartics($SupplierDetails['bankpartics'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['remittance'])){
			$Errors=VerifyRemittance($SupplierDetails['remittance'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['taxgroupid'])){
			$Errors=VerifyTaxGroupId($SupplierDetails['taxgroupid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['factorcompanyid'])){
			$Errors=VerifyFactorCompany($SupplierDetails['factorcompanyid'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($SupplierDetails as $key => $Value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$Value.'", ';
		}
		$SQL = 'INSERT INTO suppliers ('.mb_substr($FieldNames,0,-2).') '.
			'VALUES ('.mb_substr($FieldValues,0,-2).') ';
		if (sizeof($Errors)==0) {
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

	function ModifySupplier($SupplierDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($SupplierDetails as $key => $Value) {
			$SupplierDetails[$key] = DB_escape_string($Value);
		}
		$Errors=VerifySupplierNoExists($SupplierDetails['supplierid'], sizeof($Errors), $Errors);
		$Errors=VerifySupplierName($SupplierDetails['suppname'], sizeof($Errors), $Errors);
		if (isset($SupplierDetails['address1'])){
			$Errors=VerifyAddressLine($SupplierDetails['address1'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address2'])){
			$Errors=VerifyAddressLine($SupplierDetails['address2'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address3'])){
			$Errors=VerifyAddressLine($SupplierDetails['address3'], 40, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address4'])){
			$Errors=VerifyAddressLine($SupplierDetails['address4'], 50, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address5'])){
			$Errors=VerifyAddressLine($SupplierDetails['address5'], 20, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['address6'])){
			$Errors=VerifyAddressLine($SupplierDetails['address6'], 15, sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lat'])){
			$Errors=VerifyLatitude($SupplierDetails['lat'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lng'])){
			$Errors=VerifyLongitude($SupplierDetails['lng'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['currcode'])){
			$Errors=VerifyCurrencyCode($SupplierDetails['currcode'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['suppliersince'])){
			$Errors=VerifySupplierSinceDate($SupplierDetails['suppliersince'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['paymentterms'])){
			$Errors=VerifyPaymentTerms($SupplierDetails['paymentterms'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaid'])){
			$Errors=VerifyLastPaid($SupplierDetails['lastpaid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['lastpaiddate'])){
			$Errors=VerifyLastPaidDate($SupplierDetails['lastpaiddate'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankact'])){
			$Errors=VerifyBankAccount($SupplierDetails['bankact'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankref'])){
			$Errors=VerifyBankRef($SupplierDetails['bankref'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['bankpartics'])){
			$Errors=VerifyBankPartics($SupplierDetails['bankpartics'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['remittance'])){
			$Errors=VerifyRemittance($SupplierDetails['remittance'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['taxgroupid'])){
			$Errors=VerifyTaxGroupId($SupplierDetails['taxgroupid'], sizeof($Errors), $Errors);
		}
		if (isset($SupplierDetails['factorcompanyid'])){
			$Errors=VerifyFactorCompany($SupplierDetails['factorcompanyid'], sizeof($Errors), $Errors);
		}
		if (isset($CustomerDetails['taxref'])){
			$Errors=VerifyTaxRef($CustomerDetails['taxref'], sizeof($Errors), $Errors);
		}
		$SQL='UPDATE suppliers SET ';
		foreach ($SupplierDetails as $key => $Value) {
			$SQL .= $key.'="'.$Value.'", ';
		}
		$SQL = mb_substr($SQL,0,-2)." WHERE supplierid='".$SupplierDetails['supplierid']."'";
		if (sizeof($Errors)==0) {
			$Result = DB_query($SQL);
			echo DB_error_no();
			if (DB_error_no() != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function takes a supplier id and returns an associative array containing
   the database record for that supplier. If the supplier id doesn't exist
   then it returns an $Errors array.
*/
	function GetSupplier($SupplierID, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$Errors = VerifySupplierNoExists($SupplierID, sizeof($Errors), $Errors);
		if (sizeof($Errors)!=0) {
			return $Errors;
		}
		$SQL="SELECT * FROM suppliers WHERE supplierid='".$SupplierID."'";
		$Result = DB_query($SQL);
		if (sizeof($Errors)==0) {
			return DB_fetch_array($Result);
		} else {
			return $Errors;
		}
	}

/* This function takes a field name, and a string, and then returns an
   array of supplier ids that fulfill this criteria.
*/
	function SearchSuppliers($Field, $Criteria, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$SQL='SELECT supplierid
			FROM suppliers
			WHERE '.$Field." LIKE '%".$Criteria."%' ORDER BY supplierid";
		$Result = DB_query($SQL);
		$i=0;
		$SupplierList = array();
		while ($MyRow=DB_fetch_array($Result)) {
			$SupplierList[$i]=$MyRow[0];
			$i++;
		}
		return $SupplierList;
	}
	function InsertSupplierInvoice($InvoiceDetails, $user, $password) {
				$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		foreach ($InvoiceDetails as $key => $Value) {
			$InvoiceDetails[$key] = DB_escape_string($Value);
		}
		/*
		$autonumbersql="SELECT confvalue FROM config
						 WHERE confname='AutoDebtorNo'";
		$autonumberresult=DB_query($autonumbersql);
		$autonumber=DB_fetch_row($autonumberresult);
		if ($autonumber[0]==0) {
			$Errors=VerifyDebtorNo($CustomerDetails['debtorno'], sizeof($Errors), $Errors);
		} else {
			$CustomerDetails['debtorno']='';
		}
		*/
		$Errors=VerifySupplierNo($InvoiceDetails['supplierid'], sizeof($Errors), $Errors);
		if (isset($InvoiceDetails['InvoiceNo'])){
			$Errors=VerifyBankPartics($InvoiceDetails['Invoiceno'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['InvoiceAmount'])){
			$Errors=VerifyBankPartics($InvoiceDetails['InvoiceAmount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['GlAccount'])){
			$Errors=VerifyBankPartics($InvoiceDetails['GlAccount'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['InvoiceDate'])){
			$Errors=VerifyBankPartics($InvoiceDetails['InvoiceDate'], sizeof($Errors), $Errors);
		}
		if (isset($InvoiceDetails['InvoiceTax'])){
			$Errors=VerifyBankPartics($InvoiceDetails['InvoiceTax'], sizeof($Errors), $Errors);
		}
		$FieldNames='';
		$FieldValues='';
		foreach ($SupplierDetails as $key => $Value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$Value.'", ';
		}
//=======mmmmm========
// $_POST['PostInvoice'] is set so do the postings -and dont show the button to process
	/*First do input reasonableness checks
	 then do the updates and inserts to process the invoice entered */
	$TaxTotal = 0;

	$InputError = False;
	if ($TaxTotal + $InvoiceDetails['InvoiceAmount'] < 0) {

		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the total amount of the invoice is less than  0') . '. ' . _('Invoices are expected to have a positive charge') , 'error');
		echo '<p>' . _('The tax total is') . ' : ' . locale_number_format($TaxTotal, $_SESSION['SuppTrans']->CurrDecimalPlaces);
		echo '<p>' . _('The ovamount is') . ' : ' . locale_number_format($_SESSION['SuppTrans']->OvAmount, $_SESSION['SuppTrans']->CurrDecimalPlaces);

	}
	elseif ($TaxTotal + $InvoiceDetails['InvoiceAmount'] == 0) {

		prnMsg(_('The invoice as entered will be processed but be warned the amount of the invoice is  zero!') . '. ' . _('Invoices are normally expected to have a positive charge') , 'warn');

	}
	elseif (mb_strlen($InvoiceDetails['InvoiceNo']) < 1) {

		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the there is no suppliers invoice number or reference entered') . '. ' . _('The supplier invoice number must be entered') , 'error');

	}
	elseif (!Is_date($InvoiceDetails['InvoiceDate'])) {

		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the invoice date entered is not in the format') . ' ' . $InvoiceDetails['InvoiceDate'], 'error');

	}
	elseif (DateDiff(Date($InvoiceDetails['InvoiceDate']) , $InvoiceDetails['InvoiceDate'], 'd') < 0) {

		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the invoice date is after today') . '. ' . _('Purchase invoices are expected to have a date prior to or today') , 'error');

	}
	else {

		$SQL = "SELECT count(*)
				FROM supptrans
				WHERE supplierno='" . $InvoiceDetails['supplierid'] . "'
				AND supptrans.suppreference='" . $InvoiceDetails['Invoiceno'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sql to check for the previous entry of the same invoice failed');
		$Result = DB_query($SQL, $ErrMsg, '', True);

		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0] == 1) { /*Transaction reference already entered */
			prnMsg(_('The invoice number') . ' : ' . $InvoiceDetails['Invoiceno']. ' ' . _('has already been entered') . '. ' . _('It cannot be entered again') , 'error');
			$InputError = True;
		}
	}

	if ($InputError == False) {

		/* SQL to process the postings for purchase invoice */
		/*Start an SQL transaction */

		DB_Txn_Begin();

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the invoice date*/
		$InvoiceNo = GetNextTransNo(20);
		$PeriodNo = GetPeriod($InvoiceDetails['InvoiceDate']);
		$SQLInvoiceDate = FormatDateForSQL($InvoiceDetails['InvoiceDate']);

		$_SESSION['SuppTrans']->GLLink_Creditors = 1;

		if ($_SESSION['SuppTrans']->GLLink_Creditors == 1) {
		//if ($InvoiceDetails['GlAccount'] == 1) {
			/*Loop through the GL Entries and create a debit posting for each of the accounts entered */
			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:
			if its a shipment entry then the cost must go against the GRN suspense account defined in the company record

			if its a general ledger amount it goes straight to the account specified

			if its a GRN amount invoiced then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The cost as originally credited to GRN suspense on arrival of goods is debited to GRN suspense.
			Depending on the setting of WeightedAverageCosting:
			If the order line item is a stock item and WeightedAverageCosting set to OFF then use standard costing .....
				Any difference
				between the std cost and the currency cost charged as converted at the ex rate of of the invoice is written off
				to the purchase price variance account applicable to the stock item being invoiced.
			Otherwise
				Recalculate the new weighted average cost of the stock and update the cost - post the difference to the appropriate stock code

			Or if its not a stock item
			but a nominal item then the GL account in the orignal order is used for the price variance account.
			*/

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode) {

				/*GL Items are straight forward - just do the debit postings to the GL accounts specified -
				 the credit is to creditors control act  done later for the total invoice value + tax*/
				//skamnev added tag
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (20,
										'" . $InvoiceNo . "',
										'" . $SQLInvoiceDate . "',
										'" . $PeriodNo . "',
										'" . $InvoiceDetails['GlAccount']. "',
										'" . mb_substr($InvoiceDetails['supplierid'] . ' - ' . $InvoiceDetails['Narrative'], 0, 200) . "',
										'" . $InvoiceDetails['InvoiceAmount'] / $InvoiceDetails['ExRate'] . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');

				$Result = DB_query($SQL, $ErrMsg, '', true);
				InsertGLTags($EnteredGLCode->Tag);

				$LocalTotal += $InvoiceDetails['InvoiceAmount'] / $InvoiceDetails['ExRate'];
			}

			if ($Debug == 1 AND (abs($_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate) - $LocalTotal) > 0.009999) {

				echo '<p>' . _('The total posted to the debit accounts is') . ' ' . $LocalTotal . ' ' . _('but the sum of OvAmount converted at ExRate') . ' = ' . ($_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate);
			}

			foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
				/* Now the TAX account */
				if ($Tax->TaxOvAmount <> 0) {
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (20,
												'" . $InvoiceNo . "',
												'" . $SQLInvoiceDate . "',
												'" . $PeriodNo . "',
												'" . $Tax->TaxGLCode . "',
												'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . locale_number_format($Tax->TaxRate * 100, 2) . '% ' . $_SESSION['SuppTrans']->CurrCode . $Tax->TaxOvAmount . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate, 0, 200) . "',
												'" . ($Tax->TaxOvAmount / $_SESSION['SuppTrans']->ExRate) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', True);
				}

			} /*end of loop to post the tax */
			/* Now the control account */

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES (20,
									'" . $InvoiceNo . "',
									'" . $SQLInvoiceDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->CreditorsAct . "',
									'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode . locale_number_format($_SESSION['SuppTrans']->OvAmount + $TaxTotal, $_SESSION['SuppTrans']->CurrDecimalPlaces) . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate, 0, 200) . "',
									'" . -($LocalTotal + ($TaxTotal / $_SESSION['SuppTrans']->ExRate)) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', True);

			EnsureGLEntriesBalance(20, $InvoiceNo);
		} /*Thats the end of the GL postings */

		/*Now insert the invoice into the SuppTrans table*/

		$SQL = "INSERT INTO supptrans (transno,
										type,
										supplierno,
										suppreference,
										trandate,
										duedate,
										ovamount,
										ovgst,
										rate,
										transtext,
										inputdate)
							VALUES (
								'" . $InvoiceNo . "',
								20 ,
								'" . $_SESSION['SuppTrans']->SupplierID . "',
								'" . $_SESSION['SuppTrans']->SuppReference . "',
								'" . $SQLInvoiceDate . "',
								'" . FormatDateForSQL($_SESSION['SuppTrans']->DueDate) . "',
								'" . $_SESSION['SuppTrans']->OvAmount . "',
								'" . $TaxTotal . "',
								'" . $_SESSION['SuppTrans']->ExRate . "',
								'" . $_SESSION['SuppTrans']->Comments . "',
								'" . Date('Y-m-d') . "')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
		$Result = DB_query($SQL, $ErrMsg, '', True);
		$SuppTransID = DB_Last_Insert_ID('supptrans', 'id');

		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['SuppTrans']->Taxes AS $TaxTotals) {

			$SQL = "INSERT INTO supptranstaxes (supptransid,
												taxauthid,
												taxamount)
									VALUES (
										'" . $SuppTransID . "',
										'" . $TaxTotals->TaxAuthID . "',
										'" . $TaxTotals->TaxOvAmount . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier transaction taxes records could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		/* Now update the GRN and PurchOrderDetails records for amounts invoiced  - can't use the other loop through the GRNs as this was only where the GL link to credtors is active */

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN) {

			//in local currency
			$ActualCost = $EnteredGRN->ChgPrice / $_SESSION['SuppTrans']->ExRate;
			$PurchPriceVar = $EnteredGRN->This_QuantityInv * ($ActualCost - $EnteredGRN->StdCostUnit);

			$SQL = "UPDATE purchorderdetails
					SET qtyinvoiced = qtyinvoiced + " . $EnteredGRN->This_QuantityInv . ",
						actprice = '" . $EnteredGRN->ChgPrice . "'
					WHERE podetailitem = '" . $EnteredGRN->PODetailItem . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced of the purchase order line could not be updated because');

			$Result = DB_query($SQL, $ErrMsg, '', True);

			$SQL = "UPDATE grns
					SET quantityinv = quantityinv + " . $EnteredGRN->This_QuantityInv . "
					WHERE grnno = '" . $EnteredGRN->GRNNo . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced off the goods received record could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', True);

			$SQL = "INSERT INTO suppinvstogrn VALUES ('" . $InvoiceNo . "',
									'" . $EnteredGRN->GRNNo . "')";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The invoice could not be mapped to the
					goods received record because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			if (mb_strlen($EnteredGRN->ShiptRef) > 0 AND $EnteredGRN->ShiptRef != '0') {
				/* insert the shipment charge records */
				$SQL = "INSERT INTO shipmentcharges (shiptref,
													transtype,
													transno,
													stockid,
													value)
										VALUES (
											'" . $EnteredGRN->ShiptRef . "',
											20,
											'" . $InvoiceNo . "',
											'" . $EnteredGRN->ItemCode . "',
											'" . ($EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice) / $_SESSION['SuppTrans']->ExRate . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') . ' ' . $EnteredGRN->ShiptRef . ' ' . _('could not be added because');
				$Result = DB_query($SQL, $ErrMsg, '', True);

			} //end of adding GRN shipment charges
			else {
				/*so its not a GRN shipment item its a plain old stock item */

				if ($PurchPriceVar != 0) { /* don't bother with any of this lot if there is no difference ! */

					if (mb_strlen($EnteredGRN->ItemCode) > 0 OR $EnteredGRN->ItemCode != '') { /*so it is a stock item */

						/*We need to:
						 *
						 * a) update the stockmove for the delivery to reflect the actual cost of the delivery
						 *
						 * b) If a WeightedAverageCosting system and the stock quantity on hand now is negative then the cost that has gone to sales analysis and the cost of sales stock movement records will have been incorrect ... attempt to fix it retrospectively
						*/
						/*Get the location that the stock was booked into */
						$Result = DB_query("SELECT intostocklocation
											FROM purchorders
											WHERE orderno='" . $EnteredGRN->PONo . "'");
						$LocRow = DB_fetch_array($Result);
						$LocCode = $LocRow['intostocklocation'];

						/* First update the stockmoves delivery cost */
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the delivery could not have the cost updated to the actual cost');
						$SQL = "UPDATE stockmoves SET price = '" . $ActualCost . "'
											WHERE stockid='" . $EnteredGRN->ItemCode . "'
											AND type=25
											AND loccode='" . $LocCode . "'
											AND transno='" . $EnteredGRN->GRNBatchNo . "'";

						$Result = DB_query($SQL, $ErrMsg, '', True);

						if ($_SESSION['WeightedAverageCosting'] == 1) {
							/*
							 * 	How many in stock now?
							 *  The quantity being invoiced here - $EnteredGRN->This_QuantityInv
							 *  If the quantity in stock now is less than the quantity being invoiced
							 *  here then some items sold will not have had this cost factored in
							 * The cost of these items = $ActualCost
							*/

							$TotalQuantityOnHand = GetQuantityOnHand($EnteredGRN->ItemCode, 'ALL');

							/* If the quantity on hand is less the quantity charged on this invoice then some must have been sold and the price variance should be reflected in the cost of sales*/

							if ($EnteredGRN->This_QuantityInv > $TotalQuantityOnHand) {

								/* The variance to the extent of the quantity invoiced should also be written off against the sales analysis cost - as sales analysis would have been created using the cost at the time the sale was made... this was incorrect as hind-sight has shown here. However, how to determine when these were last sold? To update the sales analysis cost. Work through the last 6 months sales analysis from the latest period in which this invoice is being posted and prior.

								The assumption here is that the goods have been sold prior to the purchase invoice  being entered so it is necessary to back track on the sales analysis cost.
								* Note that this will mean that posting to GL COGS will not agree to the cost of sales from the sales analysis
								* Of course the price variances will need to be included in COGS as well
								* */

								$QuantityVarianceAllocated = $EnteredGRN->This_QuantityInv;
								$CostVarPerUnit = $ActualCost - $EnteredGRN->StdCostUnit;
								$PeriodAllocated = $PeriodNo;
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales analysis records could not be updated for the cost variances on this purchase invoice');

								while ($QuantityVarianceAllocated > 0) {
									$SalesAnalResult = DB_query("SELECT cust,
																	custbranch,
																	typeabbrev,
																	periodno,
																	stkcategory,
																	area,
																	salesperson,
																	cost,
																	qty
																FROM salesanalysis
																WHERE salesanalysis.stockid = '" . $EnteredGRN->ItemCode . "'
																AND salesanalysis.budgetoractual=1
																AND periodno='" . $PeriodAllocated . "'");
									if (DB_num_rows($SalesAnalResult) > 0) {
										while ($SalesAnalRow = DB_fetch_array($SalesAnalResult) AND $QuantityVarianceAllocated > 0) {
											if ($SalesAnalRow['qty'] <= $QuantityVarianceAllocated) {
												$QuantityVarianceAllocated -= $SalesAnalRow['qty'];
												$QuantityAllocated = $SalesAnalRow['qty'];
											}
											else {
												$QuantityAllocated = $QuantityVarianceAllocated;
												$QuantityVarianceAllocated = 0;
											}
											$UpdSalAnalResult = DB_query("UPDATE salesanalysis
																			SET cost = cost + " . ($CostVarPerUnit * $QuantityAllocated) . "
																			WHERE cust ='" . $SalesAnalRow['cust'] . "'
																			AND stockid='" . $EnteredGRN->ItemCode . "'
																			AND custbranch='" . $SalesAnalRow['custbranch'] . "'
																			AND typeabbrev='" . $SalesAnalRow['typeabbrev'] . "'
																			AND periodno='" . $PeriodAllocated . "'
																			AND area='" . $SalesAnalRow['area'] . "'
																			AND salesperson='" . $SalesAnalRow['salesperson'] . "'
																			AND stkcategory='" . $SalesAnalRow['stkcategory'] . "'
																			AND budgetoractual=1", $ErrMsg, '', True);
										}
									} //end if there were sales in that period
									$PeriodAllocated--; //decrement the period
									if ($PeriodNo - $PeriodAllocated > 6) {
										/*if more than 6 months ago when sales were made then forget it */
										break;
									}
								} /*end loop around different periods to see which sales analysis records to update */

								/*now we need to work back through the sales stockmoves up to the quantity on this purchase invoice to update costs
								 * Only go back up to 6 months looking for stockmoves and
								 * Only in the stock location where the purchase order was received
								 * into - if the stock was transferred to another location then
								 * we cannot adjust for this */
								$Result = DB_query("SELECT stkmoveno,
															type,
															qty,
															standardcost
													FROM stockmoves
													WHERE loccode='" . $LocCode . "'
													AND qty < 0
													AND stockid='" . $EnteredGRN->ItemCode . "'
													AND trandate>='" . FormatDateForSQL(DateAdd($_SESSION['SuppTrans']->TranDate, 'm', -6)) . "'
													ORDER BY stkmoveno DESC");
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movements for invoices cannot be updated for the cost variances on this purchase invoice');
								$QuantityVarianceAllocated = $EnteredGRN->This_QuantityInv;
								while ($StkMoveRow = DB_fetch_array($Result) AND $QuantityVarianceAllocated > 0) {
									if ($StkMoveRow['qty'] + $QuantityVarianceAllocated > 0) {
										if ($StkMoveRow['type'] == 10) { //its a sales invoice
											$Result = DB_query("UPDATE stockmoves
																SET standardcost = '" . $ActualCost . "'
																WHERE stkmoveno = '" . $StkMoveRow['stkmoveno'] . "'", $ErrMsg, '', True);
										}
									}
									else { //Only $QuantityVarianceAllocated left to allocate so need need to apportion cost using weighted average
										if ($StkMoveRow['type'] == 10) { //its a sales invoice
											$WACost = (((-$StkMoveRow['qty'] - $QuantityVarianceAllocated) * $StkMoveRow['standardcost']) + ($QuantityVarianceAllocated * $ActualCost)) / -$StkMoveRow['qty'];

											$UpdStkMovesResult = DB_query("UPDATE stockmoves
																SET standardcost = '" . $WACost . "'
																WHERE stkmoveno = '" . $StkMoveRow['stkmoveno'] . "'", $ErrMsg, '', True);
										}
									}
									$QuantityVarianceAllocated += $StkMoveRow['qty'];
								}
							} // end if the quantity being invoiced here is greater than the current stock on hand
							/*Now to update the stock cost with the new weighted average */

							/*Need to consider what to do if the cost has been changed manually between receiving the stock and entering the invoice - this code assumes there has been no cost updates made manually and all the price variance is posted to stock.

							A nicety or important?? */

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost could not be updated because');

							if ($TotalQuantityOnHand > 0) {

								$CostIncrement = ($PurchPriceVar - $WriteOffToVariances) / $TotalQuantityOnHand;

								$SQL = "UPDATE stockmaster
										SET lastcost=materialcost+overheadcost+labourcost,
										materialcost=materialcost+" . $CostIncrement . "
										WHERE stockid='" . $EnteredGRN->ItemCode . "'";
								$Result = DB_query($SQL, $ErrMsg, '', True);
							}
							else {
								/* if stock is negative then update the cost to this cost */
								$SQL = "UPDATE stockmaster
										SET lastcost=materialcost+overheadcost+labourcost,
											materialcost='" . $ActualCost . "'
										WHERE stockid='" . $EnteredGRN->ItemCode . "'";
								$Result = DB_query($SQL, $ErrMsg, '', True);
							}
						} /* End if it is weighted average costing we are working with */
					} /*Its a stock item */
				} /* There was a price variance */
			}
			if ($EnteredGRN->AssetID != 0) { //then it is an asset
				if ($PurchPriceVar != 0) {
					/*Add the fixed asset trans for the difference in the cost */
					$SQL = "INSERT INTO fixedassettrans (assetid,
														transtype,
														transno,
														transdate,
														periodno,
														inputdate,
														fixedassettranstype,
														amount)
											VALUES ('" . $EnteredGRN->AssetID . "',
													20,
													'" . $InvoiceNo . "',
													'" . $SQLInvoiceDate . "',
													'" . $PeriodNo . "',
													'" . Date('Y-m-d') . "',
													'cost',
													'" . ($PurchPriceVar) . "')";
					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);

					/*Now update the asset cost in fixedassets table */
					$SQL = "UPDATE fixedassets SET cost = cost + " . ($PurchPriceVar) . "
							WHERE assetid = '" . $EnteredGRN->AssetID . "'";

					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost could not be updated because:');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} //end if there was a difference in the cost

			} //the item was an asset received on a purchase order

		} /* end of the GRN loop to do the updates for the quantity of order items the supplier has invoiced */

		/*Add shipment charges records as necessary */
		foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg) {

			$SQL = "INSERT INTO shipmentcharges (shiptref,
												transtype,
												transno,
												value)
									VALUES ('" . $ShiptChg->ShiptRef . "',
												'20',
											'" . $InvoiceNo . "',
											'" . $ShiptChg->Amount / $_SESSION['SuppTrans']->ExRate . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') . ' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');

			$Result = DB_query($SQL, $ErrMsg, '', True);

		}
		/*Add contract charges records as necessary */

		foreach ($_SESSION['SuppTrans']->Contracts as $Contract) {

			if ($Contract->AnticipatedCost == true) {
				$Anticipated = 1;
			}
			else {
				$Anticipated = 0;
			}
			$SQL = "INSERT INTO contractcharges (contractref,
												transtype,
												transno,
												amount,
												narrative,
												anticipated)
									VALUES ('" . $Contract->ContractRef . "',
										'20',
										'" . $InvoiceNo . "',
										'" . $Contract->Amount / $_SESSION['SuppTrans']->ExRate . "',
										'" . $Contract->Narrative . "',
										'" . $Anticipated . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The contract charge record for contract') . ' ' . $Contract->ContractRef . ' ' . _('could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', True);
		}

		foreach ($_SESSION['SuppTrans']->Assets as $AssetAddition) {

			/*Asset additions need to have
			 * 	1. A fixed asset transaction inserted for the cost
			 * 	2. A general ledger transaction to fixed asset cost account if creditors linked
			 * 	3. The fixedasset table cost updated by the addition
			*/

			/* First the fixed asset transaction */
			$SQL = "INSERT INTO fixedassettrans (assetid,
												transtype,
												transno,
												transdate,
												periodno,
												inputdate,
												fixedassettranstype,
												amount)
									VALUES ('" . $AssetAddition->AssetID . "',
											20,
											'" . $InvoiceNo . "',
											'" . $SQLInvoiceDate . "',
											'" . $PeriodNo . "',
											'" . Date('Y-m-d') . "',
											'" . _('cost') . "',
											'" . ($AssetAddition->Amount / $_SESSION['SuppTrans']->ExRate) . "')";
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Now update the asset cost in fixedassets table */
			$Result = DB_query("SELECT datepurchased
								FROM fixedassets
								WHERE assetid='" . $AssetAddition->AssetID . "'");
			$AssetRow = DB_fetch_array($Result);

			$SQL = "UPDATE fixedassets SET cost = cost + " . ($AssetAddition->Amount / $_SESSION['SuppTrans']->ExRate);
			if ($AssetRow['datepurchased'] == '1000-01-01') {
				$SQL .= ", datepurchased='" . $SQLInvoiceDate . "'";
			}
			$SQL .= " WHERE assetid = '" . $AssetAddition->AssetID . "'";
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost and date purchased was not able to be updated because:');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		} //end of non-gl fixed asset stuff
		DB_Txn_Commit();

		prnMsg(_('Supplier invoice number') . ' ' . $InvoiceNo . ' ' . _('has been processed') , 'success');
		echo '<br />
				<div class="centre">
					<a href="' . $RootPath . '/SupplierInvoice.php?&SupplierID=' . $_SESSION['SuppTrans']->SupplierID . '">' . _('Enter another Invoice for this Supplier') . '</a>
					<br />
					<a href="' . $RootPath . '/Payments.php?&SupplierID=' . $_SESSION['SuppTrans']->SupplierID . '&amp;Amount=' . ($_SESSION['SuppTrans']->OvAmount + $TaxTotal) . '">' . _('Enter payment') . '</a>
				</div>';
		unset($_SESSION['SuppTrans']->GRNs);
		unset($_SESSION['SuppTrans']->Shipts);
		unset($_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']->Contracts);
		unset($_SESSION['SuppTrans']);
	}

//=======mmmmm=======
		if (sizeof($Errors)==0) {
			$Result = DB_query($SQL);
			if (DB_error_no() != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;

	}
<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_GET['SelectedSupplier'])) {
	$_POST['supplierid']=$_GET['SelectedSupplier'];
}

if (isset($_POST['PrintPDF']) or isset($_POST['View']) or isset($_POST['Email'])) {

	//get supplier
	$SQLsup = "SELECT suppname,
					  currcode,
					  decimalplaces AS currdecimalplaces
				FROM suppliers INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['supplier'] . "'";
	$Resultsup = DB_query($SQLsup);
	$RowSup = DB_fetch_array($Resultsup);
	$SupplierName=$RowSup['suppname'];
	$CurrCode =$RowSup['currcode'];
	$CurrDecimalPlaces=$RowSup['currdecimalplaces'];

	//get category
	if ($_POST['category']!='all'){
		$SQLcat="SELECT categorydescription
				FROM `stockcategory`
				WHERE categoryid ='" . $_POST['category'] . "'";

		$Resultcat = DB_query($SQLcat);
		$RowCat = DB_fetch_row($Resultcat);
		$Categoryname=$RowCat['0'];
	} else {
		$Categoryname='ALL';
	}


	//get date price
	if ($_POST['price']=='all'){
		$CurrentOrAllPrices=__('All Prices');
	} else {
		$CurrentOrAllPrices=__('Current Price');
	}

	//price and category = all
	if (($_POST['price']=='all') AND ($_POST['category']=='all')){
		$SQL = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					purchdata.conversionfactor,
					(purchdata.effectivefrom)as dateprice,
					purchdata.supplierdescription,
					purchdata.suppliers_partno
				FROM purchdata,stockmaster
				WHERE supplierno='" . $_POST['supplier'] . "'
				AND stockmaster.stockid=purchdata.stockid
				ORDER BY stockid ASC ,dateprice DESC";
	} else {
	//category=all and price != all
		if (($_POST['price']!='all') AND ($_POST['category']=='all')){

			$SQL = "SELECT purchdata.stockid,
							stockmaster.description,
							(SELECT purchdata.price
							 FROM purchdata
							 WHERE purchdata.stockid = stockmaster.stockid
							 ORDER BY effectivefrom DESC
							 LIMIT 0,1) AS price,
							purchdata.conversionfactor,
							(SELECT purchdata.effectivefrom
							 FROM purchdata
							 WHERE purchdata.stockid = stockmaster.stockid
							 ORDER BY effectivefrom DESC
							 LIMIT 0,1) AS dateprice,
							purchdata.supplierdescription,
							purchdata.suppliers_partno
					FROM purchdata, stockmaster
					WHERE supplierno = '" . $_POST['supplier'] . "'
					AND stockmaster.stockid = purchdata.stockid
					GROUP BY stockid
					ORDER BY stockid ASC , dateprice DESC";
		} else {
			//price = all category !=all
			if (($_POST['price']=='all')and($_POST['category']!='all')){

				$SQL = "SELECT 	purchdata.stockid,
								stockmaster.description,
								purchdata.price,
								purchdata.conversionfactor,
								(purchdata.effectivefrom)as dateprice,
								purchdata.supplierdescription,
								purchdata.suppliers_partno
						FROM purchdata,stockmaster
						WHERE supplierno='" . $_POST['supplier'] . "'
						AND stockmaster.stockid=purchdata.stockid
						AND stockmaster.categoryid='" . $_POST['category'] .  "'
						ORDER BY stockid ASC ,dateprice DESC";
			} else {
			//price != all category !=all
				$SQL = "SELECT 	purchdata.stockid,
								stockmaster.description,
								(SELECT purchdata.price
								 FROM purchdata
								 WHERE purchdata.stockid = stockmaster.stockid
								 ORDER BY effectivefrom DESC
								 LIMIT 0,1) AS price,
								purchdata.conversionfactor,
								(SELECT purchdata.effectivefrom
								FROM purchdata
								WHERE purchdata.stockid = stockmaster.stockid
								ORDER BY effectivefrom DESC
								LIMIT 0,1) AS dateprice,
								purchdata.supplierdescription,
								purchdata.suppliers_partno
						FROM purchdata,stockmaster
						WHERE supplierno='" . $_POST['supplier'] . "'
						AND stockmaster.stockid=purchdata.stockid
						AND stockmaster.categoryid='" . $_POST['category'] .  "'
						GROUP BY stockid
						ORDER BY stockid ASC ,dateprice DESC";
			}
		}
	}
	$ErrMsg =  __('The Price List could not be retrieved');
	$PricesResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PricesResult)==0) {

		$Title = __('Supplier Price List') . '-' . __('Report');
		include('includes/header.php');
		prnMsg(__('There are no result so the PDF is empty'));
		include('includes/footer.php');
		exit();
	}
	$HTML = '';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Supplier Price List for').' '.$CurrentOrAllPrices . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Supplier') . ' - ' . $_POST['supplier'] . ' - ' . $SupplierName . '<br />
					' . __('Category') . ' - ' . $Categoryname . '<br />
					' . __('Currency') . ' - ' . $CurrCode . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th class="SortedColumn">' . __('Code') . '</th>
							<th class="SortedColumn">' . __('Description') . '</th>
							<th class="SortedColumn">' . __('Conv Factor') . '</th>
							<th class="SortedColumn">' . __('Price') . '</th>
							<th class="SortedColumn">' . __('Date From') . '</th>
							<th class="SortedColumn">' . __('Supp Code') . '</th>
						</tr>
					</thead>
					<tbody>';

		while ($MyRow = DB_fetch_array($PricesResult)) {
			$HTML .= '<tr class="striped_row">
						<td>' . $MyRow['stockid'] . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . $MyRow['conversionfactor'] . '</td>
						<td class="number">' . $MyRow['price'] . '</td>
						<td class="date">' . ConvertSQLDate($MyRow['dateprice']) . '</td>
						<td>' . $MyRow['suppliers_partno'] . '</td>
					</tr>';

		}

		$HTML .= '</tbody>
			</table>';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_SupplierPriceList_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} elseif (isset($_POST['Email'])) {

		/// @todo we could skip generating the pdf if $_SESSION['InventoryManagerEmail'] == ''
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);
		// (Optional) set up the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');
		// Render the HTML as PDF
		$dompdf->render();
		// Output the generated PDF to a temporary file
		$output = $dompdf->output();

		$PDFFileName = sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_SupplierPriceList_' . date('Y-m-d') . '.pdf';
		file_put_contents($PDFFileName, $output);

		if ($_SESSION['InventoryManagerEmail']!='') {
			$ConfirmationText = __('Please find attached the Supplier Price List, generated by user') . ' ' . $_SESSION['UserID'] . ' ' . __('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = $_SESSION['DatabaseName'] . '_SupplierPriceList_' . date('Y-m-d') . '.pdf';
			/// @todo drop this IF - it's handled within SendEmailFromWebERP
			if ($_SESSION['SmtpSetting']==0){
				mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
			} else {
				SendEmailFromWebERP($_SESSION['CompanyRecord']['email'],
									array($_SESSION['InventoryManagerEmail'] =>  ''),
									$EmailSubject,
									$ConfirmationText,
									array($PDFFileName)
								);
			}
		}
		unlink($PDFFileName);

		$Title = __('Send Report By Email');
		include('includes/header.php');
		echo '<div class="centre">
				<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
			</div>';
		include('includes/footer.php');
	} else {
		$Title = __('View supplier price');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Purchase') . '" alt="" />
		'. __('Supplier Price List').'</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */

	$Title=__('Supplier Price List');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Purchase') . '" alt="" />' . ' ' . __('Supplier Price List') . '
		</p>';
	echo '<div class="page_help_text">' . __('View the Price List from supplier') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT supplierid,suppname FROM `suppliers`";
	$Result = DB_query($SQL);
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="supplier">' . __('Supplier') . ':</label>
				<select name="supplier"> ';
	while ($MyRow=DB_fetch_array($Result)){
		if (isset($_POST['supplierid']) and ($MyRow['supplierid'] == $_POST['supplierid'])) {
			 echo '<option selected="selected" value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'].' - '.$MyRow['suppname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'].' - '.$MyRow['suppname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL="SELECT categoryid, categorydescription FROM stockcategory";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="category">' . __('Category') . ':</label>
			<select name="category"> ';
		echo '<option value="all">' . __('ALL') . '</option>';
	while ($MyRow=DB_fetch_array($Result)){
		if (isset($_POST['categoryid']) and ($MyRow['categoryid'] == $_POST['categoryid'])) {
			 echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['categoryid'] . '">' .$MyRow['categoryid'].' - '. $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="price">' . __('Price List') . ':</label>
			<select name="price">
				<option value="all">' .__('All Prices') . '</option>
				<option value="current">' .__('Only Current Price') . '</option>
			</select>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
			<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
			<input type="submit" name="Email" title="Email Report" value="' . __('Email') . '" />
		</div>';

	echo '</form>';
	include('includes/footer.php');

} /*end of else not PrintPDF */

function PrintHeader($pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices) {


	/*PDF page header for Supplier price list */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$LineHeight=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$LineHeight);


	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function

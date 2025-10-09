<?php
session_start();
include("../../config/config.php");
include("../../config/session.php");
include("../../include/function_database_query.php");
include_once(COMMON_FUNCTION_PATH."common_functions.php");
include_once(COMMON_FUNCTION_PATH."finance_common_functions.php");
/*===============================================================
| Tutorial 02
|
| This code sample shows how to export list to Excel file in PHP.
| The list contains data from a SQL database.
| The cells are formatted using a user-defined format.
* =============================================================*/

include("Color.inc");
include("Alignment.inc");

// Constants declaration
$OddRowStripesStyleColor = 0xf0f7ef;

header("Content-Type: text/html");

echo "Tutorial 02<br>";
echo "----------<br>";

// Create an instance of the class that exports Excel files
$workbook = new COM("EasyXLS.ExcelDocument");

// Query the database
$query = $dbcon->query("select * from tbl_ledger");

// Create the list that stores the query values
$lstRows = new COM("EasyXLS.Util.List");

// Add the report header row to the list
$lstHeaderRow = new COM("EasyXLS.Util.List");
$lstHeaderRow->addElement("Order Date");
$lstHeaderRow->addElement("Product Name");
$lstHeaderRow->addElement("Price");
$lstHeaderRow->addElement("Quantity");
$lstHeaderRow->addElement("Value");
$lstRows->addElement($lstHeaderRow);

// Add the query values from the database to the list
while ($row=brp_mysqli_fetch_array($query))
{
    $RowList = new COM("EasyXLS.Util.List");
    $RowList->addElement("" . $row['l_name']);
    $RowList->addElement("" . $row["ledger_code"]);
    $RowList->addElement("" . $row["l_group"]);
    $RowList->addElement("" . $row["l_form"]);
    $RowList->addElement("" . $row["countryid"]);
    $lstRows->addElement($RowList);
}

// Create an instance of the class used to format the cells in the report
$xlsAutoFormat = new COM("EasyXLS.ExcelAutoFormat");

// Set the formatting style of the header
$xlsHeaderStyle = new COM("EasyXLS.ExcelStyle");
$xlsHeaderStyle->setBackground((int)$COLOR_LIGHTGREEN);
$xlsHeaderStyle->setFontSize(12);
$xlsAutoFormat->setHeaderRowStyle($xlsHeaderStyle);

// Set the formatting style of the cells (alternating style)
$xlsEvenRowStripesStyle = new COM("EasyXLS.ExcelStyle");
$xlsEvenRowStripesStyle->setBackground((int)$COLOR_FLORALWHITE);
$xlsEvenRowStripesStyle->setFormat("$0.00");
$xlsEvenRowStripesStyle->setHorizontalAlignment($ALIGNMENT_ALIGNMENT_LEFT);
$xlsAutoFormat->setEvenRowStripesStyle($xlsEvenRowStripesStyle);
$xlsOddRowStripesStyle = new COM("EasyXLS.ExcelStyle");
$xlsOddRowStripesStyle->setBackground((int)$OddRowStripesStyleColor);
$xlsOddRowStripesStyle->setFormat("$0.00");
$xlsOddRowStripesStyle->setHorizontalAlignment ($ALIGNMENT_ALIGNMENT_LEFT);
$xlsAutoFormat->setOddRowStripesStyle($xlsOddRowStripesStyle);
$xlsLeftColumnStyle = new COM("EasyXLS.ExcelStyle");
$xlsLeftColumnStyle->setBackground((int)$COLOR_FLORALWHITE);
$xlsLeftColumnStyle->setFormat("mm/dd/yyyy");
$xlsLeftColumnStyle->setHorizontalAlignment($ALIGNMENT_ALIGNMENT_LEFT);
$xlsAutoFormat->setLeftColumnStyle($xlsLeftColumnStyle);

// Export list to Excel file
echo "Writing file: C:\Samples\Tutorial02 - export List to Excel with formatting.xlsx<br>";
$workbook->easy_WriteXLSXFile_FromList_2("C:\Samples\Tutorial02 - export List to Excel with formatting.xlsx", 
                                         $lstRows, $xlsAutoFormat, "Sheet1");

// Confirm export of Excel file
if ($workbook->easy_getError() == "")
    echo "File successfully created.";
else
    echo "Error encountered: " . $workbook->easy_getError();

// Free the memory associated with the query
$stmt->execute();

// Close database connection
$stmt->close();         

// Dispose memory
$workbook->Dispose();
$workbook = null;
$lstRows = null;
$lstHeaderRow = null;
$RowList = null;
$xlsAutoFormat = null;
$xlsHeaderStyle = null;
$xlsEvenRowStripesStyle = null;
$xlsOddRowStripesStyle = null;
$xlsLeftColumnStyle = null;
	
?>


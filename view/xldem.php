<html>
<head>
<script src="//unpkg.com/xlsx/dist/xlsx.full.min.js" type="text/javascript"></script>
<script>
function exportFile(){
  var wb = XLSX.utils.table_to_book(document.getElementById('sampletable'));
  XLSX.writeFile(wb, 'sample.xlsx');
  return false;
}
</script>
</head>
<body>
<table id="sampletable" class="uk-report-table table table-striped" style="border:1px solid">
                        <thead>
                            <tr>
                                <th colspan="1" rowspan="3" style="border:1px solid">Date Range</th>
                                <th colspan="5" style="border:1px solid">
                                    <h2>Last 30 Days</h2>
                                </th>
                                <th colspan="5" style="border:1px solid">
                                    <h2>Previous 30 Days</h2>
                                </th>
                            </tr>
                            <tr>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1. Widtd-2200mm<br />
2. Height-1200mm<br />
3. Driving type-Helical gear + timing belt drive type<br />
4. Roller- Dia60 steel roller, Dia65 witd rubber sleeve, roller pitch 90mm<br />
5. Frame-Steel (square pipe)-80x40x3mm</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1</td>
                                <td style="border:1px solid">1. Widtd-2200mm<br />
2. Height-1200mm<br />
3. Driving type-Helical gear + timing belt drive type<br />
4. Roller- Dia60 steel roller, Dia65 with rubber sleeve, roller pitch 90mm<br />
5. Frame-Steel (square pipe)-80x40x3mm</td>
                            </tr>
                            
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
<button onclick="return exportFile()">Export</button>

<script>
var lineArray = [];
result_table.forEach(function(infoArray, index) {
  var line = infoArray.join(" \t");
  lineArray.push(index == 0 ? line : line);
});
var csvContent = lineArray.join("\r\n");
var excel_file = document.createElement('a');
excel_file.setAttribute('href', 'data:application/vnd.ms-excel;charset=utf-8,' + encodeURIComponent(csvContent));
excel_file.setAttribute('download', 'Visitor_History.xls');
document.body.appendChild(excel_file);
excel_file.click();
document.body.removeChild(excel_file);
</script>
</body>
</html>


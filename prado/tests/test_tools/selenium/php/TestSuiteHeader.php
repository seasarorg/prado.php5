<!--
Copyright 2004 ThoughtWorks, Inc

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
-->
<html>
<head>
<meta content="text/html; charset=ISO-8859-1"
http-equiv="content-type">
<title>Test Suite</title>
<link rel="stylesheet" type="text/css" href="<?php echo $base_dir; ?>core/selenium.css" />
<script language="JavaScript" type="text/javascript" src="<?php echo $base_dir; ?>core/scripts/selenium-browserdetect.js"></script>
<script language="JavaScript" type="text/javascript">
    var DISABLED = true; // used to flag failing tests

    function filterTestsForBrowser() {
        var suiteTable = document.getElementById("suiteTable");
        var skippedTests = document.getElementById("skippedTests");

        for(rowNum = suiteTable.rows.length - 1; rowNum >= 0; rowNum--)
        {
            var row = suiteTable.rows[rowNum];
            var filterString = row.getAttribute("unless");
            if (filterString && eval(filterString))
            {
              var cellHTML = row.cells[0].innerHTML;
              suiteTable.deleteRow(rowNum);

              var newRow = skippedTests.insertRow(1);
              var newCell = newRow.insertCell(0)
              newCell.innerHTML = cellHTML;
            }
        }
    }
</script>
</head>

<body onload="filterTestsForBrowser()">

    <table id="suiteTable"    cellpadding="1"
           cellspacing="1"
           border="1"
           class="selenium">
        <tbody>

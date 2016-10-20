var rwData=null;
var prunid = getUrlVars()["runid"];
var pbuild = getUrlVars()["build"];
var pmfs= getUrlVars()["mfsinstances"];
var pdriver= getUrlVars()["driver"];

var plabelA1= null, plabelA2 = null, plabelA3 = null, plabelA4 = null;
var prunidA1= null, prunidA2 = null, prunidA3 = null, prunidA4 = null;
var pbuildA1 = null, pbuildA2 = null, pbuildA3= null, pbuildA4= null;
var pmfsA1 = null, pmfsA2 = null, pmfsA3 = null, pmfsA4 = null;
var pdriverA1= null, pdriverA2= null, pdriverA3=null, pdriverA4=null;

var lurl = "http://10.10.88.185/puffd/rwspeed/data.php";
var purl1 = "";
var purl2 = "";
var purl3 = "";
var purl4 = "";

var tabledata;
var tablerows = getUrlVars()["rows"];
if(!tablerows){
  tablerows = 10;
}

var table;
var chart3_tabledata = null;

// Load the Visualization API and the piechart package.
//google.charts.load('current', {'packages':['table']});
google.charts.load('current', {'packages':['corechart', 'table', 'line']});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(drawRunInfoTable);

//google.charts.setOnLoadCallback(init);

function resetLurl() {
  lurl = "http://10.10.88.185/puffd/rwspeed/data.php";
}
function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
  });
  return vars;
}

function changeTableList(){
  tablerows = document.getElementById("tablelimit").value;
  drawRunInfoTable();
}

function filterSearch(){
  prunid= document.getElementById('runIdSearch').value;
  pbuild = document.getElementById("buildSearch").value;
  pmfs = document.getElementById('mfsSearch').value;
  pdriver= document.getElementById('driverSearch').value;
  drawRunInfoTable();
}

function drawRunInfoTable() {
  resetLurl();
  var param=false;
  var purl = "";
  if (prunid || pbuild || pmfs || pdriver) {
    if (prunid != null && prunid !="") {
      purl += "?runid=" + prunid;
      param = true;
    }
    if (pbuild != null && pbuild != "") {
      if (param) { purl += "&build=" + pbuild; }
      else { purl += "?build=" + pbuild; }
      param = true;
    }
    if (pmfs!= null && pmfs!= "") {
      if (param) { purl += "&mfsinstances=" + pmfs; }
      else { purl += "?mfsinstances=" + pmfs;}
      param = true;
    }
    if (pdriver!= null && pdriver != "") {
      if (param) { purl += "&driver=" + pdriver ; }
      else { purl += "?driver=" + pdriver ;}
      param = true;
    }
    /* if (pdesc != null) {
       purl += (param ? "&desc=" : "?desc=") + pdesc;
       }
    purl += "&rows=" + tablerows;
    */
  } else {
    lurl += "?rows=" + tablerows;
  }

  if(purl != "") {
    lurl += purl;
    document.getElementById("displayurl1").innerHTML = document.location + purl;
  }

  var rwMetadata = $.ajax({
    url: lurl,
    dataType: "json",
    async: false
  }).responseText;
  //alert('Metadata ' + lurl);
  var jsonData = null;
  try { jsonData = JSON.parse(rwMetadata);}
  catch (e) { alert ("Invalid search: url= " + lurl + " " + e); }
  tabledata = new google.visualization.DataTable();


  tabledata.addColumn('number', 'RunID');
  tabledata.addColumn('string', 'Build');
  tabledata.addColumn('number', '# of MFS');
  tabledata.addColumn('number', '# of SP');
  tabledata.addColumn('number', 'Repl1-local-read');
  tabledata.addColumn('number', 'Repl1-local-write');
  tabledata.addColumn('number', 'Repl1-remote-read');
  tabledata.addColumn('number', 'Repl1-remote-write');
  tabledata.addColumn('number', 'Repl3-local-read');
  tabledata.addColumn('number', 'Repl3-local-write');
  tabledata.addColumn('number', 'Repl3-remote-read'); 
  tabledata.addColumn('number', 'Repl3-remote-write');
  /*Mitch these later on for easy viewing*/
  tabledata.addColumn('string', 'Timestamp');
  tabledata.addColumn('string', 'Desc');
  tabledata.addColumn('string', 'CLDB');
  tabledata.addColumn('string', 'Secure');
  tabledata.addColumn('string', 'NetEnc');
  tabledata.addColumn('string', 'HadoopVer');

  for ( var i = 0; i < jsonData.length; i++) {
    var date = new Date(jsonData[i].timestamp*1000);
    tabledata.addRow([ jsonData[i].runid, 
        //jsonData[i].os, 
        jsonData[i].build,
        jsonData[i].mfsinstances, jsonData[i].numsp,
        /*jsonData[i].nodes, 
          jsonData[i].status, jsonData[i].disktype,*/
        jsonData[i].repl1localread,
        jsonData[i].repl1localwrite,
        jsonData[i].repl1remoteread,
        jsonData[i].repl1remotewrite,
        jsonData[i].repl3localread,
        jsonData[i].repl3localwrite,
        jsonData[i].repl3remoteread, 
        jsonData[i].repl3remotewrite,
        date.toString(),
        jsonData[i].description,
        jsonData[i].driver, 
        jsonData[i].secure,
        jsonData[i].networkencryption,
        jsonData[i].hadoopversion
    ]);

  }

  if(table == null) {
    table = new google.visualization.Table(document.getElementById('runinfo_table_div'));
    google.visualization.events.addListener(table, 'select', selectHandler);
  }
  table.draw(tabledata, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true});
}

function findGuts() {

  var gutsF1= document.getElementById('pgutsF1').value;
  var gutsF2= document.getElementById('pgutsF2').value;

  var gutsC1= document.getElementById('pgutsC1').value;
  var gutsC2= document.getElementById('pgutsC2').value;

  var gutsM1= document.getElementById('pgutsM1').value;
  var gutsM2= document.getElementById('pgutsM2').value;

  var gutsN= document.getElementById('pgutsN').value;
  var param=false;

  var gurl = "http://10.10.88.185/puffd/rwspeed/guts_data.php";
  if (gutsF1 != "") {
    if (param) { gurl += "&f1="+gutsF1; }
    else { gurl += "?f1="+gutsF1;}
    param = true
  }
  if (gutsF2 != "") {
    if (param) { gurl += "&f2="+gutsF2; }
    else { gurl += "?f2="+gutsF2; }
    param = true
  }
  if (gutsC1 != "") {
    if (param) { gurl += "&c1="+gutsC1; }
    else { gurl += "?c1="+gutsC1; }
    param = true
  }
  if (gutsC2 != "") {
    if (param) { gurl += "&c2="+gutsC2; }
    else { gurl += "?c2="+gutsC2; }
    param = true
  }
  if (gutsM1 != "") {
    if (param) { gurl += "&m1="+gutsM1; }
    else { gurl += "?m1="+gutsM1; }
    param = true
  }
  if (gutsM2 != "") {
    if (param) { gurl += "&m2="+gutsM2; }
    else { gurl += "?m2="+gutsM2; }
    param = true
  }
  if (gutsN != "") {
    if (param) { gurl += "&trace="+gutsN; }
    else { gurl += "?trace="+gutsN; }
    param = true
  }

  var gutsJ= $.ajax({
    url: gurl,
    dataType: "json",
    async: false
  }).responseText;

  var jsonData=JSON.parse(gutsJ);
  console.log(gurl);
  console.log(jsonData);

  if ( gutsC1 == "") {    //Display header row
    var guts_tabledata = new google.visualization.DataTable();
    guts_tabledata.addColumn('number', 'Col#');
    guts_tabledata.addColumn('string', 'Metric');

    table_jsonData = jsonData.metricTable;
    console.log(table_jsonData)

    for ( var i = 0; i < table_jsonData.length; i++) {
      guts_tabledata.addRow([ table_jsonData[i].colnum, table_jsonData[i].metric]);
    }

    var test_table = new google.visualization.Table(document.getElementById('test_table'));
    test_table.draw(guts_tabledata, {'showRowNumber': false, 'width': '90%', 'alternatingRowStyle': true});
  
    var guts_logdata = new google.visualization.DataTable();
    guts_logdata.addColumn('string', gutsF1);
    guts_logdata.addColumn('string', gutsF2);

    var dir1files = jsonData.dir1files;
    var dir2files = jsonData.dir2files;
    var tlen = Math.max(dir1files.length, dir2files.length)
    for ( var i = 0; i < tlen; i++) {
      guts_logdata.addRow([ "tmp", "tmp"]);
    }

    for ( var i = 0; i < dir1files.length; i++) {
      guts_logdata.setCell(i,0, dir1files[i]);
    }
    for ( var i = 0; i < dir2files.length; i++) {
      guts_logdata.setCell(i,1, dir2files[i]);
    }

    var test_table2 = new google.visualization.Table(document.getElementById('test_table2'));
    test_table2.draw(guts_logdata, {'showRowNumber': false, 'width': '90%', 'alternatingRowStyle': true});
  }
  else {
    var set1data = jsonData.set1
    var set2data = jsonData.set2
    var dt = new google.visualization.DataTable();
    dt.addColumn('number', "Index");
    dt.addColumn('number', gutsF1);
    dt.addColumn('number', gutsF2);
    var tlen = Math.max(set1data.length, set2data.length)
    for ( var i = 0; i < tlen; i++) {
      dt.addRow([ i,0, 0]);
    }

    for ( var i = 0; i < set1data.length; i++) {
      dt.setCell(i,1, set1data[i]);
    }
    for ( var i = 0; i < set2data.length; i++) {
      dt.setCell(i,2, set2data[i]);
    }

    var options = {
        'legend':'top',
        vAxis: {
          title: gutsC1
        },
        backgroundColor: '#f3f3f3'
      };

    var chart = new google.visualization.LineChart(document.getElementById('guts_chart1_div'));
    var chart1_png_div = document.getElementById("guts_chart1_png_div");
    google.visualization.events.addListener(chart, 'ready', function () {
      chart1_png_div.innerHTML = '<img src="' + chart.getImageURI() + '">'; });

    chart.draw(dt, options);
    
  }

}


function selectTables() {
  plabelA1= document.getElementById('plabelA1').value;
  plabelA2 = document.getElementById('plabelA2').value;
  plabelA3 = document.getElementById('plabelA3').value;
  plabelA4 = document.getElementById('plabelA4').value;

  prunidA1 = document.getElementById('prunidA1').value;
  prunidA2 = document.getElementById('prunidA2').value;
  prunidA3 = document.getElementById('prunidA3').value;
  prunidA4 = document.getElementById('prunidA4').value;

  pbuildA1 = document.getElementById('pbuildA1').value;
  pbuildA2 = document.getElementById('pbuildA2').value;
  pbuildA3 = document.getElementById('pbuildA3').value;
  pbuildA4 = document.getElementById('pbuildA4').value;

  pmfsA1 = document.getElementById('pmfsA1').value;
  pmfsA2 = document.getElementById('pmfsA2').value;
  pmfsA3 = document.getElementById('pmfsA3').value;
  pmfsA4 = document.getElementById('pmfsA4').value;

  pdriverA1 = document.getElementById('pdriverA1').value;
  pdriverA2 = document.getElementById('pdriverA2').value;
  pdriverA3 = document.getElementById('pdriverA3').value;
  pdriverA4 = document.getElementById('pdriverA4').value;

  selectTable1();
  selectTable2();
  selectTable3();
  selectTable4();

  plotChart3();
}

function selectTable1() {
  resetLurl();
  var param=false;
  var jsonData = null;

  if (prunidA1 || pbuildA1 || pmfsA1 || pdriverA1) {
    if (prunidA1 != null && prunidA1 !="") {
      if (param) { purl1 += "&runid=" + prunidA1;}
      else { purl1 += "?runid=" + prunidA1; }
      param = true;
    }
    if (pbuildA1 != null && pbuildA1 != "") {
      if (param) { purl1 += "&build=" + pbuildA1; }
      else { purl1 += "?build=" + pbuildA1; }
      param = true;
    }
    if (pmfsA1!= null && pmfsA1!= "") {
      if (param) { purl1 += "&mfsinstances=" + pmfsA1; }
      else { purl1 += "?mfsinstances=" + pmfsA1;}
      param = true;
    }
    if (pdriverA1!= null && pdriverA1 != "") {
      if (param) { purl1 += "&driver=" + pdriverA1 ; }
      else { purl1 += "?driver=" + pdriverA1 ; }
      param = true;
    }
    if (purl1 != "") {
      lurl += purl1;
      document.getElementById("displayurl2").innerHTML = document.location + purl1;
    }

    var rwMetadata = $.ajax({
      url: lurl,
      dataType: "json",
      async: false
    }).responseText;
    try { jsonData = JSON.parse(rwMetadata);}
    catch (e) { alert ("Invalid search: url= " + lurl + " " + e); }
  }
  else {
    return;
  }

  var td1 = new google.visualization.DataTable();

  td1.addColumn('string', 'Fake'); //add this fake column for aggregation
  td1.addColumn('number', 'RunID');
  td1.addColumn('string', 'Build');
  td1.addColumn('number', 'Repl1-local-read');
  td1.addColumn('number', 'Repl1-local-write');
  td1.addColumn('number', 'Repl1-remote-read');
  td1.addColumn('number', 'Repl1-remote-write');
  td1.addColumn('number', 'Repl3-local-read');
  td1.addColumn('number', 'Repl3-local-write');
  td1.addColumn('number', 'Repl3-remote-read'); 
  td1.addColumn('number', 'Repl3-remote-write');

  if (chart3_tabledata == null) {
    chart3_tabledata = new google.visualization.DataTable();
  }

  chart3_tabledata.addColumn('string', 'Fake'); //add this fake column for aggregation
  chart3_tabledata.addColumn('number', 'RunID');
  chart3_tabledata.addColumn('string', 'Build');
  chart3_tabledata.addColumn('number', 'Repl1-local-read');
  chart3_tabledata.addColumn('number', 'Repl1-local-write');
  chart3_tabledata.addColumn('number', 'Repl1-remote-read');
  chart3_tabledata.addColumn('number', 'Repl1-remote-write');
  chart3_tabledata.addColumn('number', 'Repl3-local-read');
  chart3_tabledata.addColumn('number', 'Repl3-local-write');
  chart3_tabledata.addColumn('number', 'Repl3-remote-read'); 
  chart3_tabledata.addColumn('number', 'Repl3-remote-write');

  for ( var i = 0; i < jsonData.length; i++) {
    td1.addRow([ 'fake',jsonData[i].runid, 
        jsonData[i].build,
        jsonData[i].repl1localread,
        jsonData[i].repl1localwrite,
        jsonData[i].repl1remoteread,
        jsonData[i].repl1remotewrite,
        jsonData[i].repl3localread,
        jsonData[i].repl3localwrite,
        jsonData[i].repl3remoteread, 
        jsonData[i].repl3remotewrite
    ]);
  }

  var res1 = google.visualization.data.group(td1,[0],[
      {'column': 3, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 4, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 5, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 6, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 7, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 8, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 9, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 10, 'aggregation': google.visualization.data.avg, 'type': 'number'}
  ]);

  td1.addRow(res1[0]);
  td1.setValue(td1.getNumberOfRows()-1,2,'Average');

  for(var i=1; i<res1.getNumberOfColumns(); i++) {
    td1.setCell(td1.getNumberOfRows()-1,i+2,res1.getValue(0,i));
    td1.setProperty(td1.getNumberOfRows()-1, i+2, 'style', 'background-color: #85c1e9;');
  }

  var view = new google.visualization.DataView(td1);
  view.hideColumns([0]);
  var vt_td1 = new google.visualization.Table(document.getElementById("table3"));
  //google.visualization.events.addListener(vt_td1, 'select', selectHandler_td1);
  vt_td1.draw(view, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true, 'allowHtml':true});


  var gdata = [];
  gdata.push( td1.getValue(td1.getNumberOfRows()-1,0) );
  gdata.push( td1.getValue(td1.getNumberOfRows()-1,1) );
  if (plabelA1 == null || plabelA1 == "") {
    gdata.push( "Set1" );
  }
  else {
    gdata.push( plabelA1 );
  }
  for(var i=3; i<td1.getNumberOfColumns(); i++) {
    gdata.push( Math.floor(td1.getValue(td1.getNumberOfRows()-1,i)) );
  }
  
  chart3_tabledata.addRow(gdata);
}

function selectTable2() {
  resetLurl();
  var param=false;
  var jsonData = null;

  if (prunidA2 || pbuildA2 || pmfsA2 || pdriverA2) {
    if (prunidA2 != null && prunidA2 !="") {
      if (param) { purl2 += "&runid=" + prunidA2;}
      else { purl2 += "?runid=" + prunidA2; }
      param = true;
    }
    if (pbuildA2 != null && pbuildA2 != "") {
      if (param) { purl2 += "&build=" + pbuildA2; }
      else { purl2 += "?build=" + pbuildA2; }
      param = true;
    }
    if (pmfsA2!= null && pmfsA2!= "") {
      if (param) { purl2 += "&mfsinstances=" + pmfsA2; }
      else { purl2 += "?mfsinstances=" + pmfsA2;}
      param = true;
    }
    if (pdriverA2!= null && pdriverA2 != "") {
      if (param) { purl2 += "&driver=" + pdriverA2 ; }
      else { purl2 += "?driver=" + pdriverA2 ; }
      param = true;
    }
    if (purl2 != "") {
      lurl += purl2;
      document.getElementById("displayurl3").innerHTML = document.location + purl2;
    }

    var rwMetadata = $.ajax({
      url: lurl,
      dataType: "json",
      async: false
    }).responseText;
    //alert('Metadata ' + lurl);
    try { jsonData = JSON.parse(rwMetadata);}
    catch (e) { alert ("Invalid search: url= " + lurl + " " + e); }
  }
  else {
    return;
  }

  var td2 = new google.visualization.DataTable();

  td2.addColumn('string', 'Fake'); //add this fake column for aggregation
  td2.addColumn('number', 'RunID');
  td2.addColumn('string', 'Build');
  td2.addColumn('number', 'Repl1-local-read');
  td2.addColumn('number', 'Repl1-local-write');
  td2.addColumn('number', 'Repl1-remote-read');
  td2.addColumn('number', 'Repl1-remote-write');
  td2.addColumn('number', 'Repl3-local-read');
  td2.addColumn('number', 'Repl3-local-write');
  td2.addColumn('number', 'Repl3-remote-read'); 
  td2.addColumn('number', 'Repl3-remote-write');

  for ( var i = 0; jsonData!=null && i < jsonData.length; i++) {
    td2.addRow([ 'fake',jsonData[i].runid, 
        jsonData[i].build,
        jsonData[i].repl1localread,
        jsonData[i].repl1localwrite,
        jsonData[i].repl1remoteread,
        jsonData[i].repl1remotewrite,
        jsonData[i].repl3localread,
        jsonData[i].repl3localwrite,
        jsonData[i].repl3remoteread, 
        jsonData[i].repl3remotewrite
    ]);
  }

  var res2 = google.visualization.data.group(td2,[0],[
      {'column': 3, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 4, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 5, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 6, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 7, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 8, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 9, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 10, 'aggregation': google.visualization.data.avg, 'type': 'number'}
  ]);

  td2.addRow(res2[0]);
  td2.setValue(td2.getNumberOfRows()-1,2,'Average');
  for(var i=1; i<res2.getNumberOfColumns(); i++) {
    td2.setCell(td2.getNumberOfRows()-1,i+2,res2.getValue(0,i));
    td2.setProperty(td2.getNumberOfRows()-1, i+2, 'style', 'background-color: #85c1e9;');
  }

  var view = new google.visualization.DataView(td2);
  view.hideColumns([0]);
  var vt_td2 = new google.visualization.Table(document.getElementById("table4"));
  //google.visualization.events.addListener(vt_td2, 'select', selectHandler_td2);
  vt_td2.draw(view, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true, 'allowHtml':true});

  var gdata = [];
  gdata.push( td2.getValue(td2.getNumberOfRows()-1,0) );
  gdata.push( td2.getValue(td2.getNumberOfRows()-1,1) );
  if (plabelA2 == null || plabelA2 == "") {
    gdata.push( "Set2" );
  }
  else {
    gdata.push( plabelA2 );
  }
  for(var i=3; i<td2.getNumberOfColumns(); i++) {
    gdata.push( Math.floor(td2.getValue(td2.getNumberOfRows()-1,i)) );
  }
  
  chart3_tabledata.addRow(gdata);
}

function selectTable3() {
  resetLurl();
  var param=false;
  var jsonData = null;

  if (prunidA3 || pbuildA3 || pmfsA3 || pdriverA3) {
    if (prunidA3 != null && prunidA3 !="") {
      if (param) { purl3 += "&runid=" + prunidA3;}
      else { purl3 += "?runid=" + prunidA3; }
      param = true;
    }
    if (pbuildA3 != null && pbuildA3 != "") {
      if (param) { purl3 += "&build=" + pbuildA3; }
      else { purl3 += "?build=" + pbuildA3; }
      param = true;
    }
    if (pmfsA3!= null && pmfsA3!= "") {
      if (param) { purl3 += "&mfsinstances=" + pmfsA3; }
      else { purl3 += "?mfsinstances=" + pmfsA3;}
      param = true;
    }
    if (pdriverA3!= null && pdriverA3 != "") {
      if (param) { purl3 += "&driver=" + pdriverA3 ; }
      else { purl3 += "?driver=" + pdriverA3 ; }
      param = true;
    }

    if (purl3 != "") {
      lurl += purl3;
      document.getElementById("displayurl4").innerHTML = document.location + purl3;
    }

    var rwMetadata = $.ajax({
      url: lurl,
      dataType: "json",
      async: false
    }).responseText;
    //alert('Metadata ' + lurl);
    try { jsonData = JSON.parse(rwMetadata);}
    catch (e) { alert ("Invalid search: url= " + lurl + " " + e); }
  }
  else {
    return;
  }

  var td3 = new google.visualization.DataTable();

  td3.addColumn('string', 'Fake'); //add this fake column for aggregation
  td3.addColumn('number', 'RunID');
  td3.addColumn('string', 'Build');
  td3.addColumn('number', 'Repl1-local-read');
  td3.addColumn('number', 'Repl1-local-write');
  td3.addColumn('number', 'Repl1-remote-read');
  td3.addColumn('number', 'Repl1-remote-write');
  td3.addColumn('number', 'Repl3-local-read');
  td3.addColumn('number', 'Repl3-local-write');
  td3.addColumn('number', 'Repl3-remote-read'); 
  td3.addColumn('number', 'Repl3-remote-write');

  for ( var i = 0; i < jsonData.length; i++) {
    td3.addRow([ 'fake',jsonData[i].runid, 
        jsonData[i].build,
        jsonData[i].repl1localread,
        jsonData[i].repl1localwrite,
        jsonData[i].repl1remoteread,
        jsonData[i].repl1remotewrite,
        jsonData[i].repl3localread,
        jsonData[i].repl3localwrite,
        jsonData[i].repl3remoteread, 
        jsonData[i].repl3remotewrite
    ]);
  }

  var res3 = google.visualization.data.group(td3,[0],[
      {'column': 3, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 4, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 5, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 6, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 7, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 8, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 9, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 10, 'aggregation': google.visualization.data.avg, 'type': 'number'}
  ]);

  td3.addRow(res3[0]);
  td3.setValue(td3.getNumberOfRows()-1,2,'Average');
  for(var i=1; i<res3.getNumberOfColumns(); i++) {
    td3.setCell(td3.getNumberOfRows()-1,i+2,res3.getValue(0,i));
    td3.setProperty(td3.getNumberOfRows()-1, i+2, 'style', 'background-color: #85c1e9;');
  }

  var view = new google.visualization.DataView(td3);
  view.hideColumns([0]);
  var vt_td3 = new google.visualization.Table(document.getElementById("table5"));
  //google.visualization.events.addListener(vt_td3, 'select', selectHandler_td3);
  vt_td3.draw(view, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true, 'allowHtml':true});

  var gdata = [];
  gdata.push( td3.getValue(td3.getNumberOfRows()-1,0) );
  gdata.push( td3.getValue(td3.getNumberOfRows()-1,1) );
  if (plabelA3 == null || plabelA3 == "") {
    gdata.push( "Set3" );
  }
  else {
    gdata.push( plabelA3 );
  }
  for(var i=3; i<td3.getNumberOfColumns(); i++) {
    gdata.push( Math.floor(td3.getValue(td3.getNumberOfRows()-1,i)) );
  }
  
  chart3_tabledata.addRow(gdata);
}

function selectTable4() {
  resetLurl();
  var param=false;
  var jsonData = null;

  if (prunidA3 || pbuildA4 || pmfsA4 || pdriverA4) {
    if (prunidA4 != null && prunidA4 !="") {
      if (param) { purl4 += "&runid=" + prunidA4;}
      else { purl4 += "?runid=" + prunidA4; }
      param = true;
    }
    if (pbuildA4 != null && pbuildA4 != "") {
      if (param) { purl4 += "&build=" + pbuildA4; }
      else { purl4 += "?build=" + pbuildA4; }
      param = true;
    }
    if (pmfsA4!= null && pmfsA4!= "") {
      if (param) { purl4 += "&mfsinstances=" + pmfsA4; }
      else { purl4 += "?mfsinstances=" + pmfsA4;}
      param = true;
    }
    if (pdriverA4!= null && pdriverA4 != "") {
      if (param) { purl4 += "&driver=" + pdriverA4 ; }
      else { purl4 += "?driver=" + pdriverA4 ; }
      param = true;
    }

    if (purl4 != "") {
      lurl += purl4;
      document.getElementById("displayurl5").innerHTML = document.location + purl4;
    }

    var rwMetadata = $.ajax({
      url: lurl,
      dataType: "json",
      async: false
    }).responseText;
    //alert('Metadata ' + lurl);
    try { jsonData = JSON.parse(rwMetadata);}
    catch (e) { alert ("Invalid search: url= " + lurl + " " + e); }
  }
  else {
    return;
  }

  var td4 = new google.visualization.DataTable();

  td4.addColumn('string', 'Fake'); //add this fake column for aggregation
  td4.addColumn('number', 'RunID');
  td4.addColumn('string', 'Build');
  td4.addColumn('number', 'Repl1-local-read');
  td4.addColumn('number', 'Repl1-local-write');
  td4.addColumn('number', 'Repl1-remote-read');
  td4.addColumn('number', 'Repl1-remote-write');
  td4.addColumn('number', 'Repl3-local-read');
  td4.addColumn('number', 'Repl3-local-write');
  td4.addColumn('number', 'Repl3-remote-read'); 
  td4.addColumn('number', 'Repl3-remote-write');

  for ( var i = 0; i < jsonData.length; i++) {
    td4.addRow([ 'fake',jsonData[i].runid, 
        jsonData[i].build,
        jsonData[i].repl1localread,
        jsonData[i].repl1localwrite,
        jsonData[i].repl1remoteread,
        jsonData[i].repl1remotewrite,
        jsonData[i].repl3localread,
        jsonData[i].repl3localwrite,
        jsonData[i].repl3remoteread, 
        jsonData[i].repl3remotewrite
    ]);
  }

  var res4 = google.visualization.data.group(td4,[0],[
      {'column': 3, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 4, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 5, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 6, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 7, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 8, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 9, 'aggregation': google.visualization.data.avg, 'type': 'number'},
      {'column': 10, 'aggregation': google.visualization.data.avg, 'type': 'number'}
  ]);

  td4.addRow(res4[0]);
  td4.setValue(td4.getNumberOfRows()-1,2,'Average');
  for(var i=1; i<res4.getNumberOfColumns(); i++) {
    td4.setCell(td4.getNumberOfRows()-1,i+2,res4.getValue(0,i));
    td4.setProperty(td4.getNumberOfRows()-1, i+2, 'style', 'background-color: #85c1e9;');
  }

  var view = new google.visualization.DataView(td4);
  view.hideColumns([0]);
  var vt_td4 = new google.visualization.Table(document.getElementById("table6"));
  //google.visualization.events.addListener(vt_td4, 'select', selectHandler_td4);
  vt_td4.draw(view, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true, 'allowHtml':true});

  var gdata = [];
  gdata.push( td4.getValue(td4.getNumberOfRows()-1,0) );
  gdata.push( td4.getValue(td4.getNumberOfRows()-1,1) );
  if (plabelA4 == null || plabelA4 == "") {
    gdata.push( "Set4" );
  }
  else {
    gdata.push( plabelA4 );
  }
  for(var i=3; i<td4.getNumberOfColumns(); i++) {
    gdata.push( Math.floor(td4.getValue(td4.getNumberOfRows()-1,i)) );
  }
  
  chart3_tabledata.addRow(gdata);
}

function selectHandler() {
  var selection = table.getSelection();
  var view = new google.visualization.DataView(tabledata);

  var item = selection[0];
  var icols = [];
  for (var i=0; i<tabledata.getNumberOfColumns(); i++) {
    var n = tabledata.getColumnLabel(i);
    var t = tabledata.getColumnType(i);
    if (n.match(/Repl/i) || n.match(/Run/i)) {
      icols.push(i);
    }
  }
  view.setColumns(icols);

  var irows = [];
  for (var i = 0; i < selection.length; i++) {
    var item = selection[i];
    irows.push(item.row);
  }
  view.setRows(irows);

  //Transpose and plot
  var newTB = new google.visualization.DataTable();
  newTB.addColumn('string',""); //Column for test names
  for (var rowIdx=1; rowIdx < view.getNumberOfColumns(); rowIdx++) {
    newTB.addRow();
    newTB.setValue(rowIdx-1,0,view.getColumnLabel(rowIdx));
  }
  for (var rowIdx=0; rowIdx < view.getNumberOfRows(); rowIdx++) {
    newTB.addColumn('number',view.getValue(rowIdx,0));
  }
  
  for (var rowIdx=0; rowIdx < view.getNumberOfRows(); rowIdx++) {
    for( var colIdx = 1; colIdx < view.getNumberOfColumns(); colIdx++) {
        newTB.setValue(colIdx-1,rowIdx+1,view.getValue(rowIdx, colIdx));
    }
  }
  
  plotChart2(newTB, "chart2");
  for( var col = 2; col < newTB.getNumberOfColumns(); col++) {
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      var v = (newTB.getValue(row,col)/newTB.getValue(row,1)) - 1;
      //console.log("Div by "+newTB.getValue(row,1)+ " " + v);
      newTB.setValue(row,col, v * 100);
    }
  }

  //annotations; Since the graph is normalised add annotation for the first column
  //All other columns are percentage increases from the first
  if(newTB.getNumberOfColumns() > 2) {
    newTB.insertColumn(2, {type:'string', role:'annotation'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,2, newTB.getFormattedValue(row,1));
      newTB.setValue(row,1, 0);
    }
    newTB.insertColumn(3, {type:'string', role:'style'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,3,'stroke-color: #000; stroke-opacity: 0.6; stroke-width: 4; fill-color: #d3d3d3; fill-opacity: 0.2');
    }
  }

  /*newTB.addColumn('number','');
  for (var i=0; i<newTB.getNumberOfRows(); i++) {
    newTB.setValue(i, newTB.getNumberOfColumns()-1, 1);
  }
  var ncol = newTB.getNumberOfColumns()-4;
  console.log(ncol);
  */

  //vtable = new google.visualization.Table(document.getElementById('chart1'));
  //vtable.draw(newTB, {'showRowNumber':false, 'allowHtml' : true});

  var options = {
    bar: {groupWidth: "75%"},
    legend: { position: "top" },
    crosshair: { trigger: 'both' },
    annotations: {
      alwaysOutside: false,
        textStyle: {
        fontSize: 14,
        color: '#000',
        style: 'line',
        auraColor: 'none'
      },
    },
    backgroundColor: {'fill': '#f3f3f3','opacity': 10},
    vAxis: {textStyle:{fontSize: 12, color:'#999999'}},
    hAxis: {textStyle:{fontSize: 12, color:'#999999'}}
  };

    //use these for combo chart 
    //seriesType: 'bars',
    //series: {0: {type: 'line'}},
    //series: { 2: {visibleInLegend: false, enableInteractivity: false}},
    //, viewWindow: { min: 0}
  
  var chart1 = new google.visualization.ColumnChart(document.getElementById('chart1'));				    
  var chart1_png_div = document.getElementById("chart1_png");
  google.visualization.events.addListener(chart1, 'ready', function () {
  chart1_png_div.innerHTML = '<img src="' + chart1.getImageURI() + '">'; });
  
  chart1.draw(newTB, options);

}

function plotChart2(dt, plotchart) {
  var options = {
    //title:'Throughput per server (MB/sec)',
    bar: {groupWidth: "75%"},
    legend: { position: "top" },
    annotations: {
      alwaysOutside: false,
      textStyle: {
        fontSize: 14,
        color: '#000',
        style: 'line',
        auraColor: 'none'
      }
    },
    backgroundColor: {'fill': '#f3f3f3','opacity': 10},
    vAxis: {textStyle:{fontSize: 12, color:'#999999'}},
    hAxis: {textStyle:{fontSize: 12, color:'#999999'}}
  };

  var chart2 = new google.visualization.ColumnChart(document.getElementById('chart2'));				    
  var chart2_png_div = document.getElementById("chart2_png");
  google.visualization.events.addListener(chart2, 'ready', function () {
  chart2_png_div.innerHTML = '<img src="' + chart2.getImageURI() + '">'; });
  
  var chart4 = new google.visualization.ColumnChart(document.getElementById('chart4'));				    
  var chart4_png_div = document.getElementById("chart4_png");
  google.visualization.events.addListener(chart4, 'ready', function () {
  chart4_png_div.innerHTML = '<img src="' + chart4.getImageURI() + '">'; });
  
  if(plotchart == "chart2") {
    chart2.draw(dt, options);
  }
  else {
    chart4.draw(dt, options);
  }

}

function plotChart3() {
  var view = new google.visualization.DataView(chart3_tabledata);

  var icols = [];
  for (var i=0; i<chart3_tabledata.getNumberOfColumns(); i++) {
    var n = chart3_tabledata.getColumnLabel(i);
    var t = chart3_tabledata.getColumnType(i);
    if (n.match(/Repl/i) || n.match(/Build/i)) {
      icols.push(i);
    }
  }
  view.setColumns(icols);

  var irows = [];
  for (var i = 0; i < chart3_tabledata.getNumberOfRows(); i++) {
    irows.push(i);
  }
  view.setRows(irows);

  //Transpose and plot
  var newTB = new google.visualization.DataTable();
  newTB.addColumn('string',""); //Column for test names
  for (var rowIdx=1; rowIdx < view.getNumberOfColumns(); rowIdx++) {
    newTB.addRow();
    newTB.setValue(rowIdx-1,0,view.getColumnLabel(rowIdx));
  }
  for (var rowIdx=0; rowIdx < view.getNumberOfRows(); rowIdx++) {
    newTB.addColumn('number',view.getValue(rowIdx,0));
  }
  
  for (var rowIdx=0; rowIdx < view.getNumberOfRows(); rowIdx++) {
    for( var colIdx = 1; colIdx < view.getNumberOfColumns(); colIdx++) {
        newTB.setValue(colIdx-1,rowIdx+1,view.getValue(rowIdx, colIdx));
    }
  }
  
  plotChart2(newTB, "chart4");

  for( var col = 2; col < newTB.getNumberOfColumns(); col++) {
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      var v = (newTB.getValue(row,col)/newTB.getValue(row,1)) - 1;
      newTB.setValue(row,col, v*100);
    }
  }

  //annotations; Since the graph is normalised add annotation for the first column
  //All other columns are percentage increases from the first
  if(newTB.getNumberOfColumns() > 2) {
    newTB.insertColumn(2, {type:'string', role:'annotation'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,2, newTB.getFormattedValue(row,1));
      newTB.setValue(row,1, 0);
    }
    newTB.insertColumn(3, {type:'string', role:'style'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,3,'stroke-color: #000; stroke-opacity: 0.6; stroke-width: 4; fill-color: #d3d3d3; fill-opacity: 0.2');
    }
  }

  var options = {
    bar: {groupWidth: "75%"},
    legend: { position: "top" },
    annotations: {
      alwaysOutside: false,
      textStyle: {
        fontSize: 14,
        color: '#000',
        style: 'line',
        auraColor: 'none'
      }
    },
    backgroundColor: {'fill': '#f3f3f3','opacity': 10},
    vAxis: {textStyle:{fontSize: 12, color:'#999999'}},
    hAxis: {textStyle:{fontSize: 12, color:'#999999'}}
  };

  var chart3 = new google.visualization.ColumnChart(document.getElementById('chart3'));				    
  var chart3_png_div = document.getElementById("chart3_png");
  google.visualization.events.addListener(chart3, 'ready', function () {
  chart3_png_div.innerHTML = '<img src="' + chart3.getImageURI() + '">'; });
  
  chart3.draw(newTB, options);

}


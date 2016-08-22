var rwData=null;
var urlts = getUrlVars()["timestamp"];
var prunid = getUrlVars()["runid"];
var pbuild = getUrlVars()["build"];
var pmfs= getUrlVars()["mfsinstances"];

var prunidA1 = null, prunidA2 = null;
var pbuildA1 = null, pbuildA2 = null;
var pmfsA1 = null, pmfsA2 = null;

var url=urlts==null?null:"http://10.10.88.136/puffd/rwspeed/data.php?runid="+urlts;

if(url != null){
  rwData = $.ajax({
    url: url,
    dataType: "json",
    async: false
  }).responseText;
}

var tabledata;
var tablerows = getUrlVars()["rows"];
if(!tablerows){
  tablerows = 10;
}

var table;

// Load the Visualization API and the piechart package.
//google.charts.load('current', {'packages':['table']});
google.charts.load('current', {'packages':['corechart', 'table', 'line']});

// Set a callback to run when the Google Visualization API is loaded.
google.charts.setOnLoadCallback(drawRunInfoTable);

//google.charts.setOnLoadCallback(init);

function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
    vars[key] = value;
  });
  return vars;
}

function changeTableList(){
  tablerows = document.getElementById("tablelimit").value;
  urlts=null;
  drawRunInfoTable();
}

function filterSearch(){
  prunid= document.getElementById('runIdSearch').value;
  pbuild = document.getElementById("buildSearch").value;
  pmfs = document.getElementById('mfsSearch').value;
  urlts=null;
  drawRunInfoTable();
}

function drawRunInfoTable() {
  var lurl = "http://10.10.88.136/puffd/rwspeed/data.php";
  var param=false;
  if (prunid || pbuild || pmfs) {
    if (prunid != null && prunid !="") {
      lurl += "?runid=" + prunid;
      param = true;
    }
    if (pbuild != null && pbuild != "") {
      if (param) { lurl += "&build=" + pbuild; }
      else { lurl += "?build=" + pbuild; }
      param = true;
    }
    if (pmfs!= null && pmfs!= "") {
      if (param) { lurl += "&mfsinstances=" + pmfs; }
      else { lurl += "?mfsinstances=" + pmfs;}
      param = true;
    }
    /* if (pdesc != null) {
       lurl += (param ? "&desc=" : "?desc=") + pdesc;
       } */
    lurl += "&rows=" + tablerows;
  } else {
    lurl += "?rows=" + tablerows;
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
    var date = new Date(jsonData[i].timestamp);
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


function selectTables() {
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

  selectTable1();
  selectTable2();
  //selectTable3();
  //selectTable4();
}

function selectTable1() {
  var lurl = "http://10.10.88.136/puffd/rwspeed/meta.php";
  var param=false;
  if (prunidA1 || pbuildA1 || pmfsA1) {
    if (prunidA1 != null && prunidA1 !="") {
      lurl += "?runid=" + prunidA1;
      param = true;
    }
    if (pbuildA1 != null && pbuildA1 != "") {
      if (param) { lurl += "&build=" + pbuildA1; }
      else { lurl += "?build=" + pbuildA1; }
      param = true;
    }
    if (pmfsA1!= null && pmfsA1!= "") {
      if (param) { lurl += "&mfsinstances=" + pmfsA1; }
      else { lurl += "?mfsinstances=" + pmfsA1;}
      param = true;
    }
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

}

function selectTable2() {
}

function selectHandler() {
  var selection = table.getSelection();
  var view = new google.visualization.DataView(tabledata);

  var item = selection[0];
  var icols = [];
  for (var i=0; i<tabledata.getNumberOfColumns(); i++) {
    var n = tabledata.getColumnLabel(i);
    var t = tabledata.getColumnType(i);
    if (n.match(/Repl/i) || n.match(/Build/i)) {
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
  
  plotChart2(newTB);
  for( var col = 2; col < newTB.getNumberOfColumns(); col++) {
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      var v = (newTB.getValue(row,col)/newTB.getValue(row,1));
      //console.log("Div by "+newTB.getValue(row,1)+ " " + v);
      newTB.setValue(row,col, v);
    }
  }

  //annotations; Since the graph is normalised add annotation for the first column
  //All other columns are percentage increases from the first
  if(newTB.getNumberOfColumns() > 2) {
    newTB.insertColumn(2, {type:'string', role:'annotation'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,2, newTB.getFormattedValue(row,1));
      newTB.setValue(row,1, 1);
    }
    newTB.insertColumn(3, {type:'string', role:'style'});
    for( var row = 0; row < newTB.getNumberOfRows(); row++) {
      newTB.setValue(row,3,'stroke-color: #000; stroke-opacity: 0.6; stroke-width: 4; fill-color: #d3d3d3; fill-opacity: 0.2');
    }
  }

  //vtable = new google.visualization.Table(document.getElementById('chart1'));
  //vtable.draw(newTB, {'showRowNumber':false, 'allowHtml' : true});

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
    //bars: 'vertical'};
    //seriesType: 'bars', gridlines: {count:10}, 
    //legend:'top', backgroundColor: {'fill': '#f3f3f3','opacity': 10},

  var chart1 = new google.visualization.ColumnChart(document.getElementById('chart1'));				    
  chart1.draw(newTB, options);

}

function plotChart2(dt) {
  var options = {title:'Throughput per server (MB/sec)',
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
  chart2.draw(dt, options);

}

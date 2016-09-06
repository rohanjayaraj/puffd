var prunid = getUrlVars()["runid"];
var pbuild = getUrlVars()["build"];
var pmfs= getUrlVars()["mfsinstances"];
var pdriver= getUrlVars()["driver"];

var plabelA1= null, plabelA2 = null, plabelA3 = null, plabelA4 = null;
var pbuildA1 = null, pbuildA2 = null, plabelA3 = null, plabelA4 = null;
var pmfsA1 = null, pmfsA2 = null, pmfsA3 = null, pmfsA4 = null;
var pdriverA1= null, pdriverA2= null, pdriverA3=null, pdriverA4=null;

var lurl = null;

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
  lurl = "http://10.10.88.136/puffd/terasort/meta.php";
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
      if (param) { 
        purl += "&build=" + pbuild; 
      }
      else { 
        purl += "?build=" + pbuild; 
      }
      param = true;
    }
    if (pmfs!= null && pmfs!= "") {
      if (param) { 
        purl += "&mfsinstances=" + pmfs; 
      }
      else { 
        purl += "?mfsinstances=" + pmfs;
      }
      param = true;
    }
    if (pdriver!= null && pdriver != "") {
      if (param) { 
        purl += "&driver=" + pdriver ; 
      }
      else { 
        purl += "?driver=" + pdriver ;
      }
      param = true;
    }
    /* if (pdesc != null) {
       lurl += (param ? "&desc=" : "?desc=") + pdesc;
       } 
    lurl += "&rows=" + tablerows;
    */
  } else {
    lurl += "?rows=" + tablerows;
  }
  if(purl != ""){
    lurl += purl;
    document.getElementById("displayurl").innerHTML = document.location + purl
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
  tabledata.addColumn('number', 'Avg Map');
  tabledata.addColumn('number', 'Avg Reduce');
  tabledata.addColumn('number', 'Avg Shuffle');
  tabledata.addColumn('number', 'Avg Merge');
  tabledata.addColumn('number', 'Runtime');
  tabledata.addColumn('string', 'HadoopVer');
  /*Mitch these later on for easy viewing*/
  tabledata.addColumn('string', 'Timestamp');
  tabledata.addColumn('number', '# of MFS');
  tabledata.addColumn('string', 'Desc');
  tabledata.addColumn('string', 'CLDB');
  tabledata.addColumn('string', 'Secure');
  tabledata.addColumn('string', 'NetEnc');

  for ( var i = 0; i < jsonData.length; i++) {
    var date = new Date(jsonData[i].timestamp*1000);
    tabledata.addRow([ jsonData[i].runid, 
        jsonData[i].build,
        jsonData[i].avgmap,
        jsonData[i].avgreduce,
        jsonData[i].avgshuffle,
        jsonData[i].avgmerge,
        jsonData[i].runtime,
        jsonData[i].hadoopversion,
  /*Mitch these later on for easy viewing*/
        date.toString(),
        jsonData[i].mfsinstances,
        jsonData[i].description,
        jsonData[i].driver, 
        jsonData[i].secure,
        jsonData[i].networkencryption
    ]);

  }

  if(table == null) {
    table = new google.visualization.Table(document.getElementById('runinfo_table_div'));
    google.visualization.events.addListener(table, 'select', selectHandler);
  }
  table.draw(tabledata, {'showRowNumber': false, 'width': '100%', 'alternatingRowStyle': true});
}


function selectHandler() {
  var selection = table.getSelection();
  var view = new google.visualization.DataView(tabledata);

  var item = selection[0];
  var icols = [];
  for (var i=0; i<tabledata.getNumberOfColumns(); i++) {
    var n = tabledata.getColumnLabel(i);
    var t = tabledata.getColumnType(i);
    if (n.match(/Avg/i) || n.match(/Build/i) || n.match(/Runtime/i)) {
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

  var chart1 = new google.visualization.ColumnChart(document.getElementById('chart1'));				    
  var chart1_png_div = document.getElementById("chart1_png");
  google.visualization.events.addListener(chart1, 'ready', function () {
  chart1_png_div.innerHTML = '<img src="' + chart1.getImageURI() + '">'; });
  
  chart1.draw(newTB, options);

}

function plotChart2(dt, plotchart) {
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
  var chart2_png_div = document.getElementById("chart2_png");
  google.visualization.events.addListener(chart2, 'ready', function () {
  chart2_png_div.innerHTML = '<img src="' + chart2.getImageURI() + '">'; });
  

  if(plotchart == "chart2") {
    chart2.draw(dt, options);
  }

}


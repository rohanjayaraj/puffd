import os,sys
import sys
sys.path.append('/var/www/html/puffd/rwspeed/assets/python/lib')
import gviz_api
import json

dir1 = None
dir2 = None
col1 = None
col2 = None
machine1 = None
machine2 = None
traceName = None


for i,a in enumerate(sys.argv):
    if i == 0:
      continue
    if i == 1:
        dir1 = a
    elif i == 2:
        dir2 = a
    elif i == 3:
        col1 = a
    elif i == 4:
        col2 = a
    elif i == 5:
        machine1 = a
    elif i == 6:
        machine2 = a
    elif i == 7:
        traceName = a
    else:
        print ("Invalid argument",a)
        sys.exit()

def populate_table(dir1, plot_guts, col, machine):
  cpu_dict=dict()
  table_dict=dict()
  table_dict2={
      "rpc": [],
      "lpc": [],
      "write0": [],
      "write1": [],
      "lwrite0": [],
      "lwrite1": [],
      "bwrite0": [],
      "bwrite1": [],
      "read0": [],
      "read1": [],
      "lread0": [],
      "lread1": [],
      "icache0": [],
      "icache1": [],
      "dcache0": [],
      "dcache1": [],
      "di": [],
      "ic": [],
      "dd": [],
      "dc": [],
      "ior0": [],
      "ior1": [],
      "iow0": [],
      "iow1": [],
      }

  if dir1 is None:
    print ("No input dir")
    sys.exit()

  if machine != -1 and traceName != None:
    infile=os.popen("ls /home/MAPRTECH/qa/perfResults/rwspeed/"+dir1+"/tracelogs*"+machine+"/"+traceName+" | head -1 | tr -d '\n'").read()
  else:
    infile=os.popen("ls /home/MAPRTECH/qa/perfResults/rwspeed/"+dir1+"/tracelogs*/guts* | head -1 | tr -d '\n'").read()

  f = open(infile, 'r')
  lines=f.readlines()
  dlines=[]
  max_dlen=0
  min_dlen=999
  header_done = False

  for line in lines[:-2]: #truncete guts output in case last row is corrupt
    if(len(line.split())>10): #Ignore blanks and non data lines
      dlines.append(line)
  
  for line in dlines:
    vals=line.split()
    if vals[0].isalpha(): #header row
      if header_done:
        continue
      cur_idx=0
      for v in vals:
        if v == "time": # 2 col; do not track
          cur_idx = cur_idx + 1
        elif v.isdigit(): #cpus - variable
          cpu_dict[cur_idx] = v
        elif v == "rpc":  #rpc - 1
          table_dict[cur_idx] = "rpc"
        elif v == "lpc":  #lpc - 1
          table_dict[cur_idx] = "lpc"
        elif v == "write":  #write - 2
          table_dict[cur_idx] = "write0"
          table_dict[cur_idx + 1] = "write1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "lwrite":  #lwrite - 2
          table_dict[cur_idx] = "lwrite0"
          table_dict[cur_idx + 1] = "lwrite1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "bwrite":  #bwrite - 2
          table_dict[cur_idx] = "bwrite0"
          table_dict[cur_idx+1] = "bwrite1"
          cur_idx = cur_idx + 1   #increment for next val})
        elif v == "read":  #read - 2})
          table_dict[cur_idx] = "read0"
          table_dict[cur_idx+1] = "read1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "lread":  #lread- 2
          table_dict[cur_idx] = "lread0"
          table_dict[cur_idx+1] = "lread1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "icache": #icache - 2
          table_dict[cur_idx] = "icache0"
          table_dict[cur_idx+1] = "icache1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "dcache": #dcache - 2
          table_dict[cur_idx] = "dcache0"
          table_dict[cur_idx+1] = "dcache1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "di": #di - 1
          table_dict[cur_idx] = "di"
        elif v == "ic": #ic - 1
          table_dict[cur_idx] = "ic"
        elif v == "dd": #dd - 1
          table_dict[cur_idx] = "dd"
        elif v == "dc": #dc - 1
          table_dict[cur_idx] = "dc"
        elif v == "ior": #ior - 2
          table_dict[cur_idx] = "ior0"
          table_dict[cur_idx+1] = "ior1"
          cur_idx = cur_idx + 1   #increment for next val
        elif v == "iow": #iow - 2
          table_dict[cur_idx] = "iow0"
          table_dict[cur_idx+1] = "iow1"
          cur_idx = cur_idx + 1   #increment for next val})
        else:
          print ("column name not tracked", v)
          sys.exit(127);

        cur_idx = cur_idx + 1   #increment for next val
      
      if plot_guts == False:   #Skip data processing
        return table_dict
      header_done=True
      #print table_dict

    else:   #data row
      for idx, v in enumerate(vals):
        if idx == 0:
          continue
        if idx in table_dict:
          aa = table_dict[idx]
          ab = table_dict2[aa]
          ab.append(v)

  return table_dict2 


if __name__ == '__main__':
  if col1 is None or col2 is None :
    table_dict = populate_table(dir1, False, -1, -1)
    res_data = dict()
    data = []
    for k,v in table_dict.items():
      data.append({"colnum": k, "metric": v})
    res_data["metricTable"] = data

    tests=os.popen("ls /home/MAPRTECH/qa/perfResults/rwspeed/"+dir1+"/tracelogs*/guts* | rev | cut -d '/' -f 1 | rev |sort | uniq").readlines()
    res_data["dir1files"] = tests
    tests=os.popen("ls /home/MAPRTECH/qa/perfResults/rwspeed/"+dir2+"/tracelogs*/guts* | rev | cut -d '/' -f 1 | rev |sort | uniq").readlines()
    res_data["dir2files"] = tests

    print json.dumps(res_data)
  else:
    t1 = populate_table(dir1, True, col1, machine1)
    t2 = populate_table(dir2, True, col2, machine2)
    pl_dt = dict()
    pl_dt["set1"] = t1[col1]
    pl_dt["set2"] = t2[col2]
    print (json.dumps(pl_dt))

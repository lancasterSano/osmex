import subprocess
from datetime import *

path = "D:\osmScript"
python_path = "C:\Python27\python.exe"
cmd1 = "%s %s\setUp\createDataBase.py" % (python_path, path)
cmd2 = "%s %s\setUp\OSMSAXParser[OPTIMIZED].py ukraine-latest.osm" % (python_path, path)
cmd3 = "%s %s\setUp\FillDataBase.py" % (python_path, path)

PIPE = subprocess.PIPE

print "[%s] Creating database..." % datetime.today().strftime('%H:%M:%S')
p = subprocess.Popen(cmd1, shell = True)
p.wait()
print "[%s] DONE!" % datetime.today().strftime('%H:%M:%S')

print "[%s] Start parsing osm files..." % datetime.today().strftime('%H:%M:%S')
p = subprocess.Popen(cmd2, shell = True)
p.wait()
print "[%s] DONE!" % datetime.today().strftime('%H:%M:%S')

print "[%s] Filling database..." % datetime.today().strftime('%H:%M:%S')
p = subprocess.Popen(cmd3, shell = True)
p.wait()
print "[s] DONE!" % datetime.today().strftime('%H:%M:%S')
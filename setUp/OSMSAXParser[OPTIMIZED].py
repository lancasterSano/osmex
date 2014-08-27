from xml.sax.handler import ContentHandler
from xml.sax import make_parser
from datetime import *
from math import *
from copy import *
import sys
import MySQLdb, mysql_config

conf = mysql_config.MySQLConfig()
db = MySQLdb.connect(host="127.0.0.1", user="root", port = 3306, passwd=conf.password, charset='utf8')
connection = db.cursor()
db.autocommit(False)
connection.execute("USE osmex3d;")

CREATE_DATABASE_6 = """CREATE TABLE IF NOT EXISTS buildings (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idNode CHAR(10),
  idWay CHAR(12),
  lat DOUBLE,
  lon DOUBLE
);"""
connection.execute(CREATE_DATABASE_6)
connection.execute("ALTER TABLE buildings ADD INDEX (idNode);")
db.commit()

def checkNodeAngle(firstNode, secondNode):
    a = secondNode[1] - firstNode[1]
    b = secondNode[0] - firstNode[0]
    c = sqrt(a*a+b*b)
    if c == 0:
        angle = 0
    else:
        angle = asin(a/c)
        #grad = 180 - (90 +fabs((angle * 180) / pi))
    return (angle * 180) / pi

def calculateDistance(firstNode, secondNode):
    return (111.2 * sqrt(pow((firstNode[0]-secondNode[0]),2)
                         + pow((firstNode[1]-secondNode[1])*cos(pi*firstNode[0]/180),2))*1000)

def searchLeftVector(rectangle):
    index = 0
    #print "rectangle: ",rectangle
    for i in range(0, len(rectangle) - 1):
        if rectangle[i][1] < rectangle[index][1]:
            index = i
    last_index = 0
    rectangle_n = copy(rectangle)
    del rectangle_n[index]
    for i in range(0, len(rectangle_n) ): #MAGIC! why not for len(rectangle_n) -1
        if (rectangle_n[i][1] < rectangle_n[last_index][1]):
            last_index = i
    if rectangle[index][0] < rectangle_n[last_index][0]:
        return [rectangle[index], rectangle_n[last_index]]
    else:
        return [rectangle_n[last_index], rectangle[index]]

def searchRightVector(rectangle):
    index = 0
    #print "rectangle: ",rectangle
    for i in range(0, len(rectangle) - 1):
        if rectangle[i][1] > rectangle[index][1]:
            index = i
    last_index = 0
    rectangle_n = copy(rectangle)
    del rectangle_n[index]
    for i in range(0, len(rectangle_n) ): #MAGIC! why not for len(rectangle_n) -1
        if (rectangle_n[i][1] > rectangle_n[last_index][1]):
            last_index = i
    if rectangle[index][0] < rectangle_n[last_index][0]:
        return [rectangle[index], rectangle_n[last_index]]
    else:
        return [rectangle_n[last_index], rectangle[index]]

def searchLongVector(rectangle):
    vector = [rectangle[0], rectangle[1]]
    lenvect = calculateDistance(vector[0], vector[1])
    for i in range(0, len(rectangle)-1):
        if (lenvect < calculateDistance(rectangle[i], rectangle[i+1])):
            vector = [rectangle[i], rectangle[i+1]]
            lenvect = calculateDistance(rectangle[i], rectangle[i+1])
    if vector[0][1] > vector[1][1]:
        return [vector[1], vector[0]]
    else:
        return [vector[0], vector[1]]

def calculateAngleOffset(boundbox, rectangle):
    bottomLine = [ [float(boundbox[0]), float(boundbox[2])], [float(boundbox[0]), float(boundbox[3])] ]
    bottomLine[0][0] -= 0.003
    bottomLine[1][0] -= 0.003
    xx = searchLeftVector(rectangle)
    yy = searchRightVector(rectangle)
    center_left = [ (xx[0][0] + xx[1][0]) / 2 , (xx[0][1] + xx[1][1]) / 2 ]
    center_right = [ (yy[0][0] + yy[1][0]) / 2 , (yy[0][1] + yy[1][1]) / 2 ]
    if center_left[0] > center_right[0]:
        center_line = [center_right, center_left]
    else:
        center_line = [center_left, center_right]
        #print "CENTER LINE: ", [center_right, center_left]
    #print "bottomLine: ", bottomLine
    #print "building: ", buildingLine
    v1x = bottomLine[1][0] - bottomLine[0][0]
    v1y = bottomLine[1][1] - bottomLine[0][1]
    v2x = center_line[1][0] - center_line[0][0]
    v2y = center_line[1][1] - center_line[0][1]
    #print "vectors: ", v1x, v1y, v2x, v2y
    angle = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
    horizontalAngle = ((angle*180)/pi)
    #print "horizontal angle1", horizontalAngle
    angle =  (((horizontalAngle)*pi)/180)
    return angle

def calculateAngleOffsetBottom(boundbox, rectangle):
    bottomLine = [ [float(boundbox[0]), float(boundbox[2])], [float(boundbox[0]), float(boundbox[3])] ]
    bottomLine[0][0] -= 0.003
    bottomLine[1][0] -= 0.003
    xx = searchLeftVector(rectangle)
    yy = searchRightVector(rectangle)
    #print "WARNING! ", xx, "!!!!!!!!!", yy
    buildingLine = [ yy[0], xx[0] ]
    #print "CENTER LINE: ", [center_right, center_left]
    #print "bottomLine: ", bottomLine
    #print "building: ", buildingLine
    v1x = bottomLine[1][0] - bottomLine[0][0]
    v1y = bottomLine[1][1] - bottomLine[0][1]
    v2x = buildingLine[1][0] - buildingLine[0][0]
    v2y = buildingLine[1][1] - buildingLine[0][1]
    #print "vectors: ", v1x, v1y, v2x, v2y
    angle = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
    horizontalAngle = 180 - ((angle*180)/pi)
    #print "horizontal angle1", horizontalAngle
    angle =  (((horizontalAngle)*pi)/180)
    return angle

def calculateAngleOffsetSecond(boundbox, rectangle):
    bottomLine = [ [float(boundbox[0]), float(boundbox[2])], [float(boundbox[0]), float(boundbox[3])] ]
    bottomLine[0][0] -= 0.003
    bottomLine[1][0] -= 0.003
    buildingLine = searchLeftVector(rectangle)
    #print "bottomLine: ", bottomLine
    #print "building: ", buildingLine
    v1x = bottomLine[1][0] - bottomLine[0][0]
    v1y = bottomLine[1][1] - bottomLine[0][1]
    v2x = buildingLine[1][0] - buildingLine[0][0]
    v2y = buildingLine[1][1] - buildingLine[0][1]
    #print "vectors: ", v1x, v1y, v2x, v2y
    angle = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
    horizontalAngle = (angle*180)/pi
    #print "horizontal angle2", horizontalAngle
    verticalLine = [ [float(boundbox[0]), float(boundbox[2])], [float(boundbox[1]), float(boundbox[2])] ]
    verticalLine[0][1] -= 0.003
    verticalLine[1][1] -= 0.003
    #print "verticalLine: ", verticalLine
    v1x = verticalLine[1][0] - verticalLine[0][0]
    v1y = verticalLine[1][1] - verticalLine[0][1]
    v2x = buildingLine[1][0] - buildingLine[0][0]
    v2y = buildingLine[1][1] - buildingLine[0][1]
    #print "vectors: ", v1x, v1y, v2x, v2y
    angleNew = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
    if horizontalAngle < 90:
        angleNew *= -1
    verticalAngle = (angleNew*180)/pi
    #print "vertical angle", verticalAngle
    return angleNew

def createRectangle(boundBox, building, database):
    #print building
    leftLine = searchLeftVector(building)
    rightLine = searchRightVector(building)
    try:
        bottomLine = [ [float(boundBox[0]), float(boundBox[2])], [float(boundBox[0]), float(boundBox[3])] ]
        bottomLine[0][0] -= 0.003
        bottomLine[1][0] -= 0.003
        center_y = 0.0
        center_x = 0.0
        v1x = bottomLine[1][0] - bottomLine[0][0]
        v1y = bottomLine[1][1] - bottomLine[0][1]
        v2x = leftLine[1][0] - leftLine[0][0]
        v2y = leftLine[1][1] - leftLine[0][1]
        angleleft = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
        horizontalAngleleft = (angleleft*180)/pi
        v2x = rightLine[1][0] - rightLine[0][0]
        v2y = rightLine[1][1] - rightLine[0][1]
        angleright = acos((v1x*v2x + v1y*v2y) / (sqrt(pow (v1x,2) + pow (v1y,2)) * sqrt(pow (v2x,2) + pow (v2y,2))))
        horizontalAngleright = (angleright*180)/pi
    except Exception as ert:
        print "**************\nprepare error %s" % ert
        print "bottomLine: ", bottomLine
        print "leftLine: ", leftLine
        print "rightLine: ", rightLine
        print "left %f and right %f" %(horizontalAngleleft , horizontalAngleright)
        horizontalAngleleft = 999

    if abs(horizontalAngleleft - horizontalAngleright) < 10:
        topLine = [leftLine[1], rightLine[1]]
        bottomLine = [leftLine[0], rightLine[0]]
        BGN = leftLine[1]
        END = rightLine[0]
        center_x = BGN[0] + ((END[0] - BGN[0]) / 2)
        center_y = BGN[1] + ((END[1] - BGN[1]) / 2)
        #print "%f , %f," % (center_x, center_y)
        if calculateDistance(leftLine[0], leftLine[1]) > calculateDistance(rightLine[0], rightLine[1]):
            Z_COORD = calculateDistance(leftLine[0], leftLine[1]) / 2
        else:
            Z_COORD = calculateDistance(rightLine[0], rightLine[1]) / 2
        if Z_COORD > 150:
            print "*********"
            print "leftLine", leftLine
            print "rightLine", rightLine
            print "topLine", topLine
            print "bottomLine", bottomLine
            print "Center X: %f | Center Y: %f" % (center_x, center_y)
            print "Z COORD: ", Z_COORD
        Y_COORD = 20

        if calculateDistance(topLine[0], topLine[1]) > calculateDistance(bottomLine[0], bottomLine[1]):
            X_COORD = calculateDistance(topLine[0], topLine[1]) / 2
        else:
            X_COORD = calculateDistance(bottomLine[0], bottomLine[1]) / 2

        if X_COORD > 150:
            print "*********"
            print "leftLine", leftLine
            print "rightLine", rightLine
            print "topLine", topLine
            print "bottomLine", bottomLine
            print "Center X: %f | Center Y: %f" % (center_x, center_y)
            print "XCOORD: ", X_COORD
        try:
            angle1 = calculateAngleOffsetBottom(boundBox, building)
            angle2 = calculateAngleOffsetSecond(boundBox, building)
            #print "angle ", angle1, " angle2 ", angle2
            constant = 1
            if angle2 < 0:
                constant *= -1
            angleOffset = ((abs(angle1) + abs(angle2))/2) * constant
            INSERT_RECTANGLE = "INSERT INTO objectInstance VALUES (null, %f, %f, %f, \
            %f, %f, %f, %f, %f, %d);" % (X_COORD, Y_COORD, Z_COORD, 0.0, angleOffset, 0.0,
                                         center_x, center_y, 1)
            database.execute(INSERT_RECTANGLE)
        except Exception as errw:
            print "[%s] ERROR IN ANGLE CALCULATION: %s" % ( datetime.today().strftime('%H:%M:%S'),
                                                            errw )
    return [center_x, center_y]

class OSMHandler(ContentHandler):
    def startElement(self, name, attrs):
        if name == "bounds" :
            self.minlatN = attrs.get("minlat")
            self.minlonN = attrs.get("minlon")
            self.maxlatN = attrs.get("maxlat")
            self.maxlonN = attrs.get("maxlon")
            self.i = 0
            self.tmp = 0
            self.rm = []
        if name == "way":
            self.idway = attrs.get("id")
            self.buildmas = []
            self.accept = False
        if name == "nd":
            self.idnd = attrs.get("ref")
            self.buildmas.append(self.idnd)
        if name == "tag":
            tagname = attrs.get("k")
            if tagname == "building" or tagname == "amenity":
                self.accept = True

    def endElement(self,name):
        if name == "way":
            try:
                x = len(self.buildmas)
                if x > 3 and x <= 5 and self.accept == True:
                    if self.buildmas[x-1] == self.buildmas[0]: #remove unused node
                        del self.buildmas[x-1] #remove unused node
                    if len(self.buildmas) < 4:
                        self.buildmas = []
                        pass
                    else:
                        for j in range(0, len(self.buildmas)):
                            connection.execute("INSERT INTO buildings VALUES(null, '%s', '%s', 0.0, 0.0)"
                                               % (self.buildmas[j], self.idway))
                        db.commit()
            except IndexError as errw:
                print "[%s] ERROR in way [%s]: %s" % ( datetime.today().strftime('%H:%M:%S'),
                                                       self.idway, errw)
            except Exception as errw:
                print "[%s] Unexpected error! %s" % (datetime.today().strftime('%H:%M:%S'), errw)

class OSMHandler_Node(ContentHandler):
    def startElement(self, name, attrs):
        if name == "node" :
            self.id = attrs.get("id")
            self.lat = attrs.get("lat")
            self.lon = attrs.get("lon")
            connection.execute("SELECT EXISTS (SELECT null FROM buildings WHERE idNode = '%s');" % self.id)
            data = connection.fetchall()
            if data[0][0] == 1:
                connection.execute("UPDATE buildings SET lat = %f, lon = %f WHERE idNode='%s'"
                                   % (float(self.lat), float(self.lon), self.id))
                db.commit()

osm = OSMHandler()
osm_node = OSMHandler_Node()
saxparser = make_parser()
saxparser.setContentHandler(osm)
print "Please, enter file name:"
filename = sys.argv[1]
print("[%s] Start processing data" % datetime.today().strftime('%H:%M:%S'))
try:
    datasource = open(filename,"r")
    saxparser.parse(datasource)
    datasource = open(filename,"r")
    saxparser = make_parser()
    saxparser.setContentHandler(osm_node)
    saxparser.parse(datasource)

    connection.execute("SELECT COUNT(id) FROM buildings;")
    count = connection.fetchall()[0][0]
    print "[%s] Parsing done! " % datetime.today().strftime('%H:%M:%S')
    iv = 1
    while iv < (count+1):
        next3 = 0
        if (iv+400) < count+1:
            next3 = iv+400
        else:
            next3 = iv + (count+1-iv)
        connection.execute("SELECT idWay,lat,lon FROM buildings WHERE id >= %d AND id < %d;" % (iv, next3))
        data = connection.fetchall()
        connection.execute("DELETE FROM buildings WHERE id >= %d AND id < %d;" % (iv, next3))
        j = 0
        while j+3 < len(data):
            try:
                boundbox = [float(osm.minlatN), float(osm.minlonN),
                            float(osm.maxlatN), float(osm.maxlonN)]
                node1 = [data[j][1], data[j][2]]
                node2 = [data[j+1][1], data[j+1][2]]
                node3 = [data[j+2][1], data[j+2][2]]
                node4 = [data[j+3][1], data[j+3][2]]
                if data[j][0] != data[j+1][0]:
                    j += 1
                elif data[j][0] != data[j+2][0]:
                    j += 2
                elif data[j][0] != data[j+3][0]:
                    j += 3
                else:
                    building = [ node1, node2, node3, node4 ]
                    try:
                        createRectangle(boundbox, building, connection)
                    except Exception as errorw:
                        print "CAN'T CREATE BUILDING: %s" % errorw
                    j += 4
            except Exception as errorsm:
                print "Error: %s" % errorsm
        iv = (next3)
except Exception as errw:
    print "[%s] ERROR: %s" % (datetime.today().strftime('%H:%M:%S'), errw)
connection.execute("DROP TABLE buildings;")
db.close()
print("[%s] End processing data" % datetime.today().strftime('%H:%M:%S'))

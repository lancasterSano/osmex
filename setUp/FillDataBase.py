import MySQLdb, mysql_config

conf = mysql_config.MySQLConfig()
db = MySQLdb.connect(host="127.0.0.1", user="root", port = 3306, passwd=conf.password, charset='utf8')
connection = db.cursor()
connection.execute("USE osmex3d;")
connection.execute("INSERT INTO objectCategory VALUES (NULL, 'Primitives');")
db.commit()
connection.execute("INSERT INTO objectType VALUES (NULL, 'Cube', 1, '', 10.0, 10.0, 10.0);")
connection.execute("INSERT INTO objectType VALUES (NULL, 'Sphere', 1, '', 10.0, 10.0, 10.0);")
connection.execute("INSERT INTO objectType VALUES (NULL, 'Cylinder', 1, '', 10.0, 10.0, 10.0);")
connection.execute("INSERT INTO objectType VALUES (NULL, 'Cone', 1, '', 10.0, 10.0, 10.0);")
connection.execute("INSERT INTO objectType VALUES (NULL, 'Torus', 1, '', 10.0, 10.0, 10.0);")
connection.execute("INSERT INTO objectType VALUES (NULL, 'Tetrahedron', 1, '', 10.0, 10.0, 10.0);")
db.commit()

db.close()

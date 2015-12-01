import sys, re, mysql.connector

stop_chars = ['[',']',',']

f = open("env/mysql_creds","r")
creds = f.read().splitlines()

cnx = mysql.connector.connect(user=creds[0],password=creds[1],host=creds[2],database=creds[3])
cursor = cnx.cursor()	

query = ("SELECT tweet_id, search_term, place FROM Tweets " 
		 "WHERE place != %s")
place = "None"

cursor.execute(query, (place,))

count = 0
l = []

for c in cursor:
	l.append([c[0],c[1],c[2]])

for item in l:
	print(str(count))
	try:
		tweet_id = item[0]
		search_term = item[1]
		geo = item[2]
		found = re.search('coordinates=(.+?)\)',geo).group(1)
	except Exception, e:
		print(e)
		continue
	for ch in stop_chars:
		found = found.replace(ch,"")
	lon = 0
	lat = 0
	for i in range(0,len(found.split()),2):
		lon += float(found.split()[i])
	for i in range(1,len(found.split()),2):
		lat += float(found.split()[i])
	lon /= float(4)
	lat /= float(4)
	try:
		cursor.execute("""update Tweets 
					set latitude=%s,longitude=%s
					where tweet_id=%s and search_term=%s
			""",(lat,lon,tweet_id,search_term))
		count += 1
	except Exception, e:
		print(e)

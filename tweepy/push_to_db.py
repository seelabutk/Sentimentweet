import sys, mysql.connector

def get_sent_score(text):
	pos_count = 0
	neg_count = 0
	for word in text.split():
		if word in neg_list:
			neg_count += 1
		if word in pos_list:
			pos_count += 1
	if not (pos_count or neg_count):
		return 0
	sent_score = (pos_count - neg_count)/float(pos_count + neg_count)
	return sent_score

if(len(sys.argv) < 3):
	print("usage: push_to_db.py <records_fname> <search_term>")
	sys.exit(1)

pos_list = []
neg_list = []

f = open("sentiment/positive-words.txt","r")
lines = f.read().splitlines()
for line in lines:
	pos_list.append(line)

	
f = open("sentiment/negative-words.txt","r")
lines = f.read().splitlines()
for line in lines:
	neg_list.append(line)

f = open(sys.argv[1],"r")
lines = f.read().splitlines()

f = open("env/mysql_creds","r")
creds = f.read().splitlines()

cnx = mysql.connector.connect(user=creds[0],password=creds[1],host=creds[2],database=creds[3])
cursor = cnx.cursor()	

insert_call = ("INSERT INTO Tweets "
				"(tweet_id,text,created_at,user_id,place,search_term,sent_score)"
				"VALUES (%s,%s,%s,%s,%s,%s,%s)")

tweet_id = ""
text = ""
created_at = ""
user_id = ""
place = ""
search_term = sys.argv[2]
sent_score = ""

for i in range(0,len(lines)):
	words = lines[i].split()
	if words:
		if words[0] == "tweet_id:":
			tweet_id = words[1]
		if words[0] == "text:":
			text = ' '.join(words[1:len(words)])
			more_text = True
			while(more_text):
				next_line = lines[i+1].split()
				i += 1
				if next_line:
					if next_line[0] == "created_at:":
						more_text = False
					else:
						text += ' ' +  ' '.join(next_line)
		if words[0] == "created_at:":
			created_at = ' '.join(words[1:len(words)])
		if words[0] == "user_id:":
			user_id = words[1]
		if words[0] == "place:":
			if words[1] == "None":
				place = "None"
			else:
				place = ' '.join(words[1:len(words)])
			sent_score = get_sent_score(text)
			insert_values = (tweet_id,text,created_at,user_id,place,search_term,sent_score)
			try:
				cursor.execute(insert_call,insert_values)
			except mysql.connector.IntegrityError, e:
				print("Error: {}".format(e))

import sys, operator, string

if len(sys.argv) < 2:
	sys.exit(1)

f = open(sys.argv[1],"r")
lines = f.read().splitlines()

fout = open("geo_samples.txt","w")

geo_count = 1	
hashtag_dict = {}
for line in lines:
	words = line.split()
	if words:
		if words[0] == "place:" and words[1] != "None":
			geo_count += 1
			fout.write(' '.join(words[1:len(words)]) + "\n")
		elif words[0] == "text:":
			for i in range(1,len(words)):
				if words[i][0] == "#":
					stripped_word = words[i].lower().translate(string.maketrans("",""),string.punctuation)
					if stripped_word != "":
						if stripped_word not in hashtag_dict:
							hashtag_dict[stripped_word] = 0
						hashtag_dict[stripped_word] += 1

sorted_hashtags = sorted(hashtag_dict.items(),key=operator.itemgetter(1),reverse=True)

print("geo_count = " + str(geo_count))

print("top 20 hashtags: ")
for i in range(0,20):
	print(sorted_hashtags[i])

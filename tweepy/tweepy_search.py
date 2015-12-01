import tweepy
import nltk
import string
import requests, sys, time, re
from datetime import datetime

if len(sys.argv) < 3:
	print("usage: python tweepy_search.py <machine_no> <search_term>")
	sys.exit(1)

machine_no = sys.argv[1]
search_term = sys.argv[2]

f = open('env/tweepy_creds','r')
f_lines = (f.read()).splitlines()
f.close()

creds = f_lines[int(machine_no)].split()

consumer_key = creds[1]
consumer_key_secret = creds[2]
access_token = creds[3]
access_token_secret = creds[4]

auth = tweepy.OAuthHandler(consumer_key, consumer_key_secret)
auth.set_access_token(access_token, access_token_secret)

api = tweepy.API(auth)

date = datetime.now()
fout_name = search_term + "_" + str(date.year) + "." + str(date.month) + "." + str(date.day)

f_results = open("results/" + fout_name,"w")
f_log = open("logs/" + fout_name + "_log","w")

f_log.write("search results for search term: \"" + search_term + "\"\n")
print("search results for search term: \"" + search_term + "\"\n")

count = 1
start = time.time()

'''
# Get all English tweets containing search term from previous 7 days.
# Write tweet_id, text, timestamp, user_id, and geolocation object of each tweet to file.
# Tweepy search returns most recent tweets (up to 100) unless max tweet ID is specified.
# After first search, lowest tweet ID returned from search is used as max_id in subsequent search.
# Sleeps for 15 minutes on TweepError (Twitter API limits search to 180 queries per 15 minutes).
'''

last_id = None
while(1):
	try:
		if not last_id: # first call to search - get most recent tweets
			tweets = api.search(q=search_term,lang="en",count=100)
		else: # subsequent calls to search - use min_id-1 from last search as max_id for next search, get 100 tweets preceding previous results
			tweets = api.search(q=search_term,lang="en",count=100,max_id=str(last_id-1))
		if not tweets: # no results returned - search finished
			break
		for tweet in tweets:
			f_results.write("tweet " + str(count) + "\n")
			f_results.write("tweet_id: " + str(tweet.id) + "\n")
			f_results.write("text: " +str(tweet.text.encode('ascii','ignore')) + "\n")
			f_results.write("created_at: " + str(tweet.created_at) + "\n")
			f_results.write("user_id: " + str(tweet.author.id) + "\n")
			f_results.write("place: " + str(tweet.place) + "\n\n")
			count += 1
			if count % 1000 == 0:
				f_log.write("\nupdate - " + str((time.time()-start)/60) + " minutes elapsed - " + str(count) + " tweets collected\n")
		last_id = tweets[-1].id
	except tweepy.TweepError, ex:
		f_log.write("TweepError: %s\n" % ex)
		time.sleep(15*60)
	except Exception, ex:
		f_log.write("caught exception: %s\n" % ex)

end = time.time()

f_log.write("\ncollected " + str(count) + " tweets")
f_log.write("\ntime elapsed: " + str((end-start)/60) + " minutes")

f_results.close()
f_log.close()


from tkinter import N
import requests
from os import getenv
import mysql.connector
from data_parser import cursus_users_to_pool_list, cursus_users_to_db_users
import gzip
from json import dumps
from time import sleep, time
from datetime import datetime

headers = {
	"Authorization": "Bearer "+getenv("_42_TOKEN")
}

mydb = mysql.connector.connect(
  host="localhost",
  user="test",
  password="test",
  database="test"
)

mycursor = mydb.cursor()

mycursor.execute("SELECT `user_id` FROM users ORDER BY `user_id` DESC LIMIT 1")
fetch_from = mycursor.fetchone()

if fetch_from is None:
	fetch_from = 105400
else:
	fetch_from = fetch_from[0]

print(f"fetching new users from {fetch_from}")
page_num = 0
with gzip.open("users.txt.gz", "a") as fpp: # si j'ai besoin de retraiter les infos
	while True:
		a = requests.get(
			f"https://api.intra.42.fr/v2/cursus_users?page[number]={page_num}&page[size]=100&range[user_id]={fetch_from},1000000&sort=user_id",
			headers=headers
		)

		if a.status_code != 200:
			print(a.status_code, a.text)
			raise Exception("pas 200")

		a = a.json()
		if len(a) == 0:
			break

		for user in a:
			fpp.write((dumps(user)+"\r\n").encode())

		piscines = cursus_users_to_pool_list(a)

		if len(piscines):
			mycursor.executemany(
				"INSERT IGNORE INTO `piscines`("+(', '.join(piscines[0].keys()))+") VALUES("+str(("%s, "*len(piscines[0].keys()))[:-2])+")",
				[tuple(piscine.values()) for piscine in piscines]
			)
			mydb.commit()

		piscineux = cursus_users_to_db_users(a)

		if len(piscineux):
			mycursor.executemany(
				"INSERT IGNORE INTO `users`("+(', '.join(piscineux[0].keys()))+") VALUES ("+str(("%s, "*len(piscineux[0].keys()))[:-2])+")",
				[tuple(p.values()) for p in piscineux]
			)
			mydb.commit()

		page_num += 1

		print("fetching page", str(page_num)+"...", end="\r")
		with open("id.txt", "w") as fp: # si ça crash je peux recommencer ici
			fp.write(str(a[-1]["user"]["id"]))
		sleep(3)
		

print("\nupdating update_until fields...")
mycursor.execute("SELECT piscine_id, end_at, begin_at FROM piscines;")
piscines = mycursor.fetchall()
for piscine in piscines:
	print(piscine[0], f"{piscines.index(piscine)}/{len(piscines)}", end="\r")
	mycursor.execute(f"UPDATE users SET update_from={piscine[2]}, update_until={piscine[1]} WHERE piscine_id='{piscine[0]}'")
mydb.commit()

print("\nupdating users")
i = 0
update_start = int(time())
while True:
	mycursor.execute(f"SELECT login, piscine_id FROM users WHERE update_from < {int(time())} AND updated_at < update_until AND updated_at < {update_start} ORDER BY updated_at ASC LIMIT 1;")
	to_update = mycursor.fetchone()

	if to_update is None:
		break
	
	print(f"updating {to_update[0]}".ljust(30), end="\r")
	ret = requests.get("https://api.intra.42.fr/v2/users/"+to_update[0], headers=headers)

	if ret.status_code != 200:
		print(ret.status_code, ret.content)
		raise Exception("pas 200")

	ret = ret.json()
	
	for project in ret["projects_users"]:
		if project["cursus_ids"][0] == 9 and project["final_mark"] is not None:
			mycursor.execute(f"DELETE FROM `projects` WHERE login='{to_update[0]}' AND slug='{project['project']['slug']}'")
			mycursor.execute(
				"""INSERT INTO `projects`(
					piscine_id, login, user_id, retry,
					final_mark, marked_at, slug, name) 
					VALUES(%s, %s, %s, %s, %s, %s, %s, %s)""",
				(to_update[1], to_update[0], ret["id"], project["occurrence"],
				project["final_mark"], int(datetime.fromisoformat(project["marked_at"][:10]).timestamp()),
				project["project"]["slug"], project["project"]["name"]))

	mycursor.execute(f"UPDATE users SET updated_at={int(time())} WHERE login='{to_update[0]}'")
	mydb.commit()
	sleep(2)

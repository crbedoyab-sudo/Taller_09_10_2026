from locust import HttpUser, task
import random

class Accounts(HttpUser):
    @task
    def index(self):
        self.client.get("/users")

    @task
    def emails(self):
        self.client.get("/users/emails")

    @task 
    def overTwenty(self):
        self.client.get(f"/users/over-twenty")

    @task 
    def bulksStore(self):
        payload = {"name": "Juan", "email": "juan@test.com", "birth_date": "1990-01-01", "password": "password123"}
        self.client.post("/users/bulk", json=payload)

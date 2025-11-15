Overview

This project implements an AI agent pipeline that analyzes a candidate résumé and classifies the most relevant SAP module role (MM, SD, FICO, PM, Basis).
It also generates:

A clean candidate summary

One recommended SAP role

Three SAP skill gaps

Three actionable learning tips

Full reasoning logs stored in DB

The system uses Laravel 12, FastAPI, LangChain, Groq Llama-3.3, Docker, and Kubernetes.

Architecture

User
→ POST /api/resume (PDF/DOC/TXT)
→ Laravel API
→ Stores file and extracts text
→ Logs reasoning steps
→ Calls agent /match
→ Python Agent Service
→ Llama model returns JSON
→ Laravel returns final structured output
→ Client receives JSON

Project Structure

hackathon-sap-sol/

laravel/ (Laravel API project)

agent-service/venv/ (Python FastAPI LangChain agent)

docker-compose.yml

k8s.yaml

README.md

Setup Instructions

Clone the project
git clone https://github.com/
<your-repo>/hackathon-sap-sol.git
cd hackathon-sap-sol

Laravel API Setup (Local)

Copy .env file
cp .env.example .env

Use SQLite
DB_CONNECTION=sqlite
Create the DB file:
mkdir -p database
touch database/database.sqlite

Install dependencies and migrate
composer install
php artisan key:generate
php artisan migrate

Start Laravel
php artisan serve --host=0.0.0.0 --port=8000

Python Agent Microservice Setup

cd agent-service/venv
pip install -r requirements.txt
export GROQ_API_KEY="your_real_key"
uvicorn main:app --host 0.0.0.0 --port=9000

API Endpoints

POST /api/resume
Uploads file + triggers agent pipeline

POST /api/match
Only runs the Llama agent manually

Example Response:

{
"resume_id": 1,
"candidate_summary": "...",
"suggested_role": "SD",
"skill_gaps": ["...", "...", "..."],
"learning_tips": ["...", "...", "..."]
}

Logs stored in:
storage/logs/laravel.log
agent_logs table

Docker Setup

Build Laravel image:
docker build -f Dockerfile.laravel -t apurva19989432/hackathon-sap-sol-laravel:latest .

Build agent image:
docker build -f agent-service/venv/Dockerfile.agent -t apurva19989432/hackathon-sap-sol-agent:latest agent-service/venv

Push images:
docker push apurva19989432/hackathon-sap-sol-laravel:latest
docker push apurva19989432/hackathon-sap-sol-agent:latest

Run via Docker Compose:
docker compose up

Laravel runs on: http://localhost:8000

Agent runs on: http://localhost:9000

Kubernetes Deployment

kubectl apply -f k8s.yaml
kubectl get pods
kubectl get svc

Test endpoint after ingress load balancer:
curl -X POST http://<EXTERNAL-IP>/api/resume -F "resume=@file.pdf"

Logging and Reasoning

Laravel logs agent steps:
storage/logs/laravel.log

Database:
agent_logs table logs all steps:

step

tool

input

output

timestamp

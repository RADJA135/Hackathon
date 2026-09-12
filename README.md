# TrustAI

**Secure. Smart. Built with Nokia.**

TrustAI is an AI‑powered fraud prevention system that uses real‑time telecom signals from Nokia's Open Gateway APIs to protect users before they even log in.

## Features

- **3 Nokia CAMARA APIs:** SIM Swap, Device Status, Location Verification
- **AI Orchestration:** 4‑agent CrewAI pipeline (Data Agent, Identity Agent, Risk Analysis Agent, Decision Agent)
- **Deterministic Scoring:** Trust Score calculated from telecom signals with Allow/Warn/Block decisions
- **Modern Stack:** Laravel, Vue.js, Python/FastAPI, Ollama

## Tech Stack

- **Frontend:** Vue.js + Inertia.js
- **Backend:** Laravel (PHP)
- **AI Agent:** Python + FastAPI + CrewAI + Ollama
- **APIs:** Nokia Network‑as‑Code (SIM Swap, Device Status, Location Verification)

## Demo Video

https://drive.google.com/file/d/1CO0y9HnpfXkalisxrSMbiUSB4wf2eayu/view?usp=sharing

## Team

- Radja Tibouk 
- Meriem Semsoum
- Maroua Haddad

  
## Live Nokia API Integration

"TrustAI dynamically calculates the Trust Score based on real-time Nokia CAMARA API signals. Different phone numbers will produce different scores depending on their live SIM Swap, Device Status, and Location Verification results."

 ## Note on the Demo Video vs. Live Code

The demo video was recorded using a temporary DEMO_MODE override that forces specific phone numbers to return predefined scores. This was done purely to demonstrate the full range of results (100, 80, 70, 50, 0) within a 3-minute video, avoiding unreliable API latency during recording.

In this submitted codebase, DEMO_MODE is disabled. The system now relies entirely on real-time responses from the Nokia CAMARA APIs (SIM Swap, Device Status, Location). This means the scores you see are fully dynamic and reflect genuine network data.


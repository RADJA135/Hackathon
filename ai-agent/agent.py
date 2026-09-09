"""
TrustAI Decision Agent —(CrewAI + Ollama, with fallback)
"""

import os
from fastapi import FastAPI
from pydantic import BaseModel
from dotenv import load_dotenv
from crewai import Agent, Task, Crew, Process
from crewai.llm import LLM
from typing import Dict, Any
import logging

load_dotenv()
app = FastAPI(title="TrustAI Decision Agent")

# Set up logging for CrewAI output (optional)
logging.basicConfig(level=logging.INFO)

llm = LLM(
    model=os.getenv("LLM_MODEL", "ollama/qwen2.5:0.5b"),
    api_key=os.getenv("LLM_API_KEY", "not-needed"),
    temperature=0.1,
)

class DecideRequest(BaseModel):
    trust_check_id: int
    signals: Dict[str, Any]

class DecisionOutput(BaseModel):
    trust_score: int
    decision: str
    reasoning: str


def compute_score(signals: dict) -> tuple[int, str]:
    score = 100
    if signals.get("sim_swapped"):
        score -= 50
    if not signals.get("device_known", True):
        score -= 30
    loc = signals.get("location_consistent")
    if loc is False:
        score -= 20
    score = max(0, min(100, score))
    decision = "allow" if score >= 80 else "warn" if score >= 50 else "block"
    return score, decision


def fallback_reasoning(signals: dict, score: int, decision: str) -> str:
    parts = []
    if signals.get("sim_swapped"):
        parts.append("SIM swapped")
    else:
        parts.append("SIM not swapped")

    if signals.get("device_known"):
        parts.append("device known")
    else:
        parts.append("device unknown")

    loc = signals.get("location_consistent")
    if loc is False:
        parts.append("location inconsistent")
    else:
        parts.append("location consistent")


    signal_summary = ", ".join(parts)
    return f"Trust score {score}/100 ({decision}) – {signal_summary}."


def build_crew(trust_check_id: int, signals: dict, score: int, decision: str) -> Crew:
    signal_summary = (
        f"Trust check ID: {trust_check_id}\n"
        f"- SIM swapped: {signals.get('sim_swapped')} (True means swapped)\n"
        f"- Device known: {signals.get('device_known')} (True means known)\n"
        f"- Location consistent: {signals.get('location_consistent')} (True means consistent, False means inconsistent)"
    )

    data_agent = Agent(
        role="Data Agent",
        goal="Output the signal values exactly as given.",
        backstory="You are a precise data reporter. You never add extra text.",
        llm=llm,
        verbose=True,
    )

    identity_agent = Agent(
        role="Identity Agent",
        goal="Determine identity confidence using simple rules.",
        backstory="You only output HIGH, MEDIUM, or LOW based on SIM and device.",
        llm=llm,
        verbose=True,
    )

    risk_agent = Agent(
        role="Risk Analysis Agent",
        goal="Determine overall risk using simple rules.",
        backstory="You only output LOW, MEDIUM, or HIGH based on location and identity.",
        llm=llm,
        verbose=True,
    )

    decision_agent = Agent(
        role="Decision Agent",
        goal="Produce exactly one sentence that includes the three signal values and the score.",
        backstory="You are a strict formatter. Your output must follow this template: 'SIM swapped: X, Device known: Y, Location consistent: Z – Score: S/100 (decision).'",
        llm=llm,
        verbose=True,
    )

    data_task = Task(
        description=f"{signal_summary}\n\nOutput exactly: 'SIM swapped: [value], Device known: [value], Location consistent: [value]'",
        expected_output="A line with the three key-value pairs.",
        agent=data_agent,
    )

    identity_task = Task(
        description=(
            "Using the Data Agent's output, decide identity confidence:\n"
            "- If SIM swapped is True → LOW\n"
            "- If SIM swapped is False and Device known is True → HIGH\n"
            "- If SIM swapped is False and Device known is False → MEDIUM\n"
            "Output only: 'HIGH', 'MEDIUM', or 'LOW'."
        ),
        expected_output="HIGH, MEDIUM, or LOW.",
        agent=identity_agent,
        context=[data_task],
    )

    risk_task = Task(
        description=(
            "Using the Data Agent's location signal and the Identity Agent's finding, decide risk:\n"
            "- If location consistent is True and identity confidence is HIGH → LOW risk\n"
            "- If location consistent is False or identity confidence is MEDIUM → MEDIUM risk\n"
            "- If identity confidence is LOW → HIGH risk\n"
            "Output only: 'LOW', 'MEDIUM', or 'HIGH'."
        ),
        expected_output="LOW, MEDIUM, or HIGH.",
        agent=risk_agent,
        context=[data_task, identity_task],
    )

    decision_task = Task(
        description=(
            f"The deterministic trust score is {score}/100, decision: {decision}.\n"
            "Write ONE plain sentence that includes the exact values of the three signals and the score.\n"
            "Format: 'SIM swapped: [value], Device known: [value], Location consistent: [value] – Score: S/100 (decision).'\n"
            "Do not add any other text. Use the exact values from the Data Agent."
        ),
        expected_output="One sentence in the specified format.",
        agent=decision_agent,
        context=[identity_task, risk_task],
    )

    return Crew(
        agents=[data_agent, identity_agent, risk_agent, decision_agent],
        tasks=[data_task, identity_task, risk_task, decision_task],
        process=Process.sequential,
        verbose=True, 
    )

@app.post("/decide", response_model=DecisionOutput)
def decide(request: DecideRequest):
    signals = request.signals
    score, decision = compute_score(signals)

    
    crew = build_crew(request.trust_check_id, signals, score, decision)
    result = crew.kickoff()
    raw_crew_output = str(result).strip()

    # Log the CrewAI output for reference (visible in terminal)
    print("\n CREWAI RAW OUTPUT:\n", raw_crew_output, "\n")

 
    reasoning = fallback_reasoning(signals, score, decision)

    # Truncate if needed
    if len(reasoning) > 400:
        reasoning = reasoning[:397] + "..."

    return DecisionOutput(trust_score=score, decision=decision, reasoning=reasoning)

@app.get("/health")
def health():
    return {"status": "ok"}
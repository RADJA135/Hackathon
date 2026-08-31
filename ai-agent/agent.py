"""
TrustAI Decision Agent
----------------------
A small FastAPI service that wraps a 3-agent CrewAI crew.

Laravel calls POST /decide once all 3 Nokia signals have been collected.
This service reasons over them and returns a trust score + decision.

Run locally:
    pip install -r requirements.txt
    uvicorn agent:app --reload --port 8001

Set your LLM key in .env (see .env.example) — any provider from the
Resource & Tooling Guide works: Groq, Google AI Studio (Gemini), etc.
"""

import os
from fastapi import FastAPI
from pydantic import BaseModel
from dotenv import load_dotenv
from crewai import Agent, Task, Crew, Process
from crewai.llm import LLM

load_dotenv()

app = FastAPI(title="TrustAI Decision Agent")

# ---- LLM setup -------------------------------------------------
# Swap provider/model here. Examples (all free-tier, all in the
# approved Resource & Tooling Guide):
#   Groq:   model="groq/llama-3.3-70b-versatile"
#   Gemini: model="gemini/gemini-2.5-flash"
llm = LLM(
    model=os.getenv("LLM_MODEL", "groq/llama-3.3-70b-versatile"),
    api_key=os.getenv("LLM_API_KEY"),
    temperature=0.2,
)

# ---- Request / response shapes ----------------------------------
class SignalInput(BaseModel):
    phone_number: str
    sim_swapped: bool
    sim_swap_last_changed: str | None = None
    device_known: bool
    location_consistent: bool
    location_country: str | None = None


class DecisionOutput(BaseModel):
    trust_score: int
    decision: str  # "allow" | "warn" | "block"
    reasoning: str


# ---- Agents -------------------------------------------------------
identity_agent = Agent(
    role="Identity Agent",
    goal="Determine whether the SIM and device belong to the real account owner.",
    backstory=(
        "You specialize in telecom identity signals. You look at SIM swap "
        "history and device recognition status to judge whether this login "
        "device/SIM combination matches the legitimate user."
    ),
    llm=llm,
    verbose=True,
)

risk_agent = Agent(
    role="Risk Analysis Agent",
    goal="Assess whether this login looks suspicious given all available signals.",
    backstory=(
        "You specialize in fraud pattern detection. You weigh location "
        "consistency alongside identity findings to flag anomalies — e.g. a "
        "recent SIM swap combined with an unfamiliar location is high risk, "
        "but either signal alone might be innocent."
    ),
    llm=llm,
    verbose=True,
)

decision_agent = Agent(
    role="Decision Agent",
    goal="Combine the Identity and Risk findings into one final trust score and decision.",
    backstory=(
        "You make the final call. You output a trust score from 0-100 and a "
        "decision: allow (score >= 80), warn (50-79), or block (< 50). You "
        "always explain your reasoning in one or two plain sentences."
    ),
    llm=llm,
    verbose=True,
)


def compute_score(signals: SignalInput) -> tuple[int, str]:
    """Deterministic scoring — no LLM involved. This guarantees a correct
    number and decision regardless of how small/weak the LLM is."""
    score = 100
    if signals.sim_swapped:
        score -= 50
    if not signals.device_known:
        score -= 30
    if not signals.location_consistent:
        score -= 20
    score = max(0, score)

    if score >= 80:
        decision = "allow"
    elif score >= 50:
        decision = "warn"
    else:
        decision = "block"
    return score, decision


def build_crew(signals: SignalInput, score: int, decision: str) -> Crew:
    identity_task = Task(
        description=(
            f"Signals for this login attempt:\n"
            f"- SIM swapped recently: {signals.sim_swapped} "
            f"(last changed: {signals.sim_swap_last_changed or 'unknown'})\n"
            f"- Device recognized as known: {signals.device_known}\n\n"
            "Assess identity confidence based only on these two signals. "
            "Output a short verdict: HIGH, MEDIUM, or LOW confidence, with one sentence why."
        ),
        expected_output="One line: confidence level + one-sentence reason.",
        agent=identity_agent,
    )

    risk_task = Task(
        description=(
            f"Location consistency for this login: {signals.location_consistent} "
            f"(country: {signals.location_country or 'unknown'}).\n\n"
            "Combine this with the Identity Agent's finding to assess overall risk. "
            "Output a short verdict: LOW, MEDIUM, or HIGH risk, with one sentence why."
        ),
        expected_output="One line: risk level + one-sentence reason.",
        agent=risk_agent,
        context=[identity_task],
    )

    decision_task = Task(
        description=(
            f"The trust score has already been calculated by the system: {score}/100, "
            f"decision: {decision}.\n\n"
            "Using the Identity Agent's and Risk Agent's findings above, write ONE plain "
            "sentence explaining why this score and decision make sense. Do NOT change the "
            "score or decision — only explain it. Output just the sentence, nothing else."
        ),
        expected_output="One plain sentence of reasoning, no labels or formatting.",
        agent=decision_agent,
        context=[identity_task, risk_task],
    )

    return Crew(
        agents=[identity_agent, risk_agent, decision_agent],
        tasks=[identity_task, risk_task, decision_task],
        process=Process.sequential,
        verbose=True,
    )


def parse_decision(raw: str, score: int, decision: str) -> DecisionOutput:
    """Score and decision come from compute_score() — always correct.
    Only the reasoning sentence comes from the LLM, with a safe fallback."""
    reason = str(raw).strip()
    if not reason or len(reason) > 400:
        reason = f"Trust score {score}/100 based on SIM swap, device, and location signals."
    return DecisionOutput(trust_score=score, decision=decision, reasoning=reason)


@app.post("/decide", response_model=DecisionOutput)
def decide(signals: SignalInput):
    score, decision = compute_score(signals)
    crew = build_crew(signals, score, decision)
    result = crew.kickoff()
    return parse_decision(result, score, decision)


@app.get("/health")
def health():
    return {"status": "ok"}
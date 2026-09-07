"""
TrustAI Decision Agent — v4
CrewAI + Ollama
Reliable hybrid AI + deterministic trust policy

Run:
    uvicorn agent:app --reload --port 8001
"""

import os
from typing import Dict, Any

from fastapi import FastAPI
from pydantic import BaseModel
from dotenv import load_dotenv

from crewai import Agent, Task, Crew, Process
from crewai.llm import LLM


# ============================================================
# CONFIGURATION
# ============================================================

load_dotenv()

app = FastAPI(
    title="TrustAI Decision Agent",
    version="4.0"
)

llm = LLM(
    model=os.getenv(
        "LLM_MODEL",
        "ollama/qwen2.5:0.5b"
    ),
    api_key=os.getenv(
        "LLM_API_KEY",
        "not-needed"
    ),
    temperature=0.1,
)


# ============================================================
# DATA MODELS
# ============================================================

class DecideRequest(BaseModel):
    trust_check_id: int
    signals: Dict[str, Any]


class DecisionOutput(BaseModel):
    trust_score: int
    decision: str
    reasoning: str


# ============================================================
# SIGNAL NORMALIZATION
# ============================================================
# Telecom APIs can sometimes return different representations.
# We normalize them before analysis.
# ============================================================

def normalize_bool(value: Any, default: bool = False) -> bool:

    if isinstance(value, bool):
        return value

    if isinstance(value, int):
        return value != 0

    if isinstance(value, str):

        value = value.strip().lower()

        if value in {
            "true",
            "1",
            "yes",
            "verified",
            "consistent",
            "known"
        }:
            return True

        if value in {
            "false",
            "0",
            "no",
            "not verified",
            "inconsistent",
            "unknown",
            "new"
        }:
            return False

    return default


def normalize_signals(signals: Dict[str, Any]) -> Dict[str, bool]:

    return {
        "sim_swapped": normalize_bool(
            signals.get("sim_swapped"),
            False
        ),

        "device_known": normalize_bool(
            signals.get("device_known"),
            True
        ),

        "location_consistent": normalize_bool(
            signals.get("location_consistent"),
            True
        ),
    }


# ============================================================
# TRUST POLICY
# ============================================================
# IMPORTANT:
#
# This is the security policy.
#
# CrewAI provides reasoning.
# This function guarantees that the numerical decision
# follows the exact business/security policy.
# ============================================================

def calculate_trust_score(
    signals: Dict[str, bool]
) -> tuple[int, str]:

    score = 100

    # SIM Swap
    if signals["sim_swapped"]:
        score -= 50

    # Device
    if not signals["device_known"]:
        score -= 30

    # Location
    if not signals["location_consistent"]:
        score -= 20

    # Safety bounds
    score = max(0, min(100, score))

    # Final decision
    if score >= 80:
        decision = "allow"

    elif score >= 50:
        decision = "warn"

    else:
        decision = "block"

    return score, decision


# ============================================================
# HUMAN-READABLE EXPLANATION
# ============================================================

def build_policy_reason(
    signals: Dict[str, bool],
    score: int,
    decision: str
) -> str:

    factors = []

    if signals["sim_swapped"]:
        factors.append(
            "SIM swap detected (-50)"
        )
    else:
        factors.append(
            "SIM is not swapped"
        )

    if signals["device_known"]:
        factors.append(
            "device is known"
        )
    else:
        factors.append(
            "device is unknown (-30)"
        )

    if signals["location_consistent"]:
        factors.append(
            "location is consistent"
        )
    else:
        factors.append(
            "location is inconsistent (-20)"
        )

    return (
        f"Trust score {score}/100 ({decision}) – "
        + ", ".join(factors)
        + "."
    )


# ============================================================
# CREWAI AGENTS
# ============================================================

def build_crew(
    trust_check_id: int,
    signals: Dict[str, bool]
) -> Crew:

    signal_summary = f"""
Trust Check ID: {trust_check_id}

Telecom security signals:

SIM swapped: {signals["sim_swapped"]}
Device known: {signals["device_known"]}
Location consistent: {signals["location_consistent"]}
"""

    # ========================================================
    # AGENT 1 — TELECOM ANALYST
    # ========================================================

    telecom_agent = Agent(

        role="Telecom Signal Analyst",

        goal=(
            "Analyze trusted telecom security signals "
            "without inventing or modifying their values."
        ),

        backstory=(
            "You are a telecom security analyst. "
            "You work with CAMARA-style telecom signals such as "
            "SIM Swap, Device Status and Location Verification. "
            "Your job is to identify suspicious indicators."
        ),

        llm=llm,
        verbose=True,
    )


    # ========================================================
    # AGENT 2 — IDENTITY ANALYST
    # ========================================================

    identity_agent = Agent(

        role="Identity Risk Analyst",

        goal=(
            "Assess identity confidence using SIM and device "
            "signals."
        ),

        backstory=(
            "You specialize in digital identity and telecom "
            "fraud detection."
        ),

        llm=llm,
        verbose=True,
    )


    # ========================================================
    # AGENT 3 — FRAUD ANALYST
    # ========================================================

    fraud_agent = Agent(

        role="Fraud Risk Analyst",

        goal=(
            "Analyze the combined telecom signals and identify "
            "the overall fraud risk."
        ),

        backstory=(
            "You specialize in detecting account takeover, "
            "SIM-swap fraud and suspicious device/location "
            "behavior."
        ),

        llm=llm,
        verbose=True,
    )


    # ========================================================
    # TASK 1
    # ========================================================

    telecom_task = Task(

        description=f"""
Analyze these trusted telecom signals:

{signal_summary}

Identify:

1. Whether a SIM swap is detected.
2. Whether the device is known.
3. Whether the location is consistent.

Do NOT change the values.

Explain briefly which signals are suspicious.
""",

        expected_output=(
            "A concise analysis of the three telecom signals."
        ),

        agent=telecom_agent,
    )


    # ========================================================
    # TASK 2
    # ========================================================

    identity_task = Task(

        description="""
Based on the Telecom Signal Analyst's findings:

Assess identity confidence.

Consider:

- A SIM swap is a strong identity-risk indicator.
- A known device increases confidence.
- An unknown device decreases confidence.

Return:

Identity confidence:
HIGH / MEDIUM / LOW

Then provide one short explanation.
""",

        expected_output=(
            "Identity confidence and a short explanation."
        ),

        agent=identity_agent,

        context=[telecom_task],
    )


    # ========================================================
    # TASK 3
    # ========================================================

    fraud_task = Task(

        description="""
Using the telecom analysis and identity analysis:

Determine the overall fraud risk.

Consider:

- SIM swap
- Device familiarity
- Location consistency
- Identity confidence

Return:

Fraud risk:
LOW / MEDIUM / HIGH

Then provide one short explanation.

Do not invent any telecom signals.
""",

        expected_output=(
            "Fraud risk level and a short explanation."
        ),

        agent=fraud_agent,

        context=[
            telecom_task,
            identity_task
        ],
    )


    # ========================================================
    # CREW
    # ========================================================

    return Crew(

        agents=[
            telecom_agent,
            identity_agent,
            fraud_agent
        ],

        tasks=[
            telecom_task,
            identity_task,
            fraud_task
        ],

        process=Process.sequential,

        verbose=True
    )


# ============================================================
# MAIN DECISION ENDPOINT
# ============================================================

@app.post(
    "/decide",
    response_model=DecisionOutput
)
def decide(request: DecideRequest):

    print("\n")
    print("=" * 60)
    print("🤖 TRUSTAI DECISION AGENT")
    print("=" * 60)

    # --------------------------------------------------------
    # STEP 1 — Normalize telecom signals
    # --------------------------------------------------------

    signals = normalize_signals(
        request.signals
    )

    print("\n📡 TELECOM SIGNALS")

    print(
        f"SIM swapped: "
        f"{signals['sim_swapped']}"
    )

    print(
        f"Device known: "
        f"{signals['device_known']}"
    )

    print(
        f"Location consistent: "
        f"{signals['location_consistent']}"
    )


    # --------------------------------------------------------
    # STEP 2 — Calculate guaranteed trust score
    # --------------------------------------------------------
    #
    # This is deliberately deterministic.
    #
    # The AI cannot accidentally turn:
    #
    # True + True + False
    #
    # into 80.
    #
    # It MUST be:
    #
    # 100 - 50 - 20 = 30
    #

    score, decision = calculate_trust_score(
        signals
    )

    print("\n🔐 TRUST POLICY")

    print("Starting score: 100")

    if signals["sim_swapped"]:
        print("SIM swap: -50")

    if not signals["device_known"]:
        print("Unknown device: -30")

    if not signals["location_consistent"]:
        print("Inconsistent location: -20")

    print(
        f"\n✅ FINAL POLICY SCORE: "
        f"{score}/100"
    )

    print(
        f"✅ POLICY DECISION: "
        f"{decision.upper()}"
    )


    # --------------------------------------------------------
    # STEP 3 — Run CrewAI reasoning
    # --------------------------------------------------------

    try:

        print("\n")
        print("=" * 60)
        print("🧠 RUNNING CREWAI ANALYSIS")
        print("=" * 60)

        crew = build_crew(
            request.trust_check_id,
            signals
        )

        result = crew.kickoff()

        ai_reasoning = str(
            result
        ).strip()

        print("\n🤖 CREWAI ANALYSIS:")
        print(ai_reasoning)


    except Exception as e:

        print("\n⚠️ CrewAI failed:")
        print(str(e))

        ai_reasoning = (
            "AI reasoning unavailable; "
            "deterministic trust policy used."
        )


    # --------------------------------------------------------
    # STEP 4 — Build final trusted explanation
    # --------------------------------------------------------
    #
    # The score/decision ALWAYS come from the validated
    # security policy.
    #
    # AI reasoning cannot override them.
    #

    policy_reason = build_policy_reason(
        signals,
        score,
        decision
    )

    reasoning = (
        f"{policy_reason} "
        f"AI analysis: {ai_reasoning}"
    )


    # --------------------------------------------------------
    # STEP 5 — Limit DB size
    # --------------------------------------------------------

    if len(reasoning) > 400:

        reasoning = (
            reasoning[:397]
            + "..."
        )


    # --------------------------------------------------------
    # FINAL RESULT
    # --------------------------------------------------------

    print("\n")
    print("=" * 60)
    print("🎯 FINAL TRUST DECISION")
    print("=" * 60)

    print(
        f"Trust Score : {score}/100"
    )

    print(
        f"Decision    : {decision.upper()}"
    )

    print(
        f"Reasoning   : {reasoning}"
    )

    print("=" * 60)


    return DecisionOutput(

        trust_score=score,

        decision=decision,

        reasoning=reasoning
    )


# ============================================================
# HEALTH CHECK
# ============================================================

@app.get("/health")
def health():

    return {
        "status": "ok",
        "agent": "TrustAI Decision Agent",
        "version": "4.0"
    }
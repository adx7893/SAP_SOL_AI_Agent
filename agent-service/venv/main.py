from fastapi import FastAPI
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from langchain_groq import ChatGroq
from langchain_core.prompts import ChatPromptTemplate
import json
import traceback
import os
import re

app = FastAPI()


class AnalyzeRequest(BaseModel):
    resume_text: str


# ✅ Read Groq API key from env (or hard-code temporarily)
GROQ_API_KEY = os.environ.get("GROQ_API_KEY", "").strip()

if not GROQ_API_KEY:
    print("⚠️ WARNING: GROQ_API_KEY not set – LLM calls will fail.")


llm = ChatGroq(
    model="llama-3.3-70b-versatile",
    temperature=0.2,
    api_key=GROQ_API_KEY or None,
)


prompt = ChatPromptTemplate.from_messages([
    ("system",
    """You are an recruiter. 
Analyze the resume and return ONLY valid JSON (no text outside JSON).

1. If the resume contains NO SAP-related keywords 
   ("SAP", "MM", "SD", "FICO", "PM", "Basis", "HANA", "ABAP"):
   - candidate_summary MUST clearly say the candidate has **no SAP experience** and summarize the resume with sepecific skills he/she have Tehcnical skills and mentions programming langauges skilled.
   - suggested_role MUST be Closest possible seeing the skills from ("SAP", "MM", "SD", "FICO", "PM", "Basis", "HANA", "ABAP").
   - skill_gaps MUST list 3 missing SAP areas.
   - learning_tips MUST give 3 simple beginner-friendly SAP learning steps.

2. If SAP keywords ARE present:
   - Choose ONE SAP role: "MM", "SD", "FICO", "PM", or "Basis".
   - Skill gaps must be SAP-specific.

3. candidate_summary:
   - MUST summarize the **overall resume**, not only SAP.

Expected JSON (escaped braces for formatting):

{{
  "candidate_summary": "2–3 sentence generale summary",
  "suggested_role": "MM | SD | FICO | PM | Basis",
  "skill_gaps": ["gap1", "gap2", "gap3"],
  "learning_tips": ["tip1", "tip2", "tip3"]
}}
"""),

    ("user",
    "Resume text:\n\n{resume_text}\n\nReturn ONLY the JSON.")
])

def _safe_parse_json(raw):
    raw = str(raw).strip()

    # Remove code fences like ```json and ```
    raw = raw.replace("```json", "").replace("```", "")

    # Extract JSON block
    match = re.search(r"\{[\s\S]*\}", raw)
    if match:
        try:
            return json.loads(match.group(0))
        except:
            pass

    # If still fails, return fallback
    return {
        "candidate_summary": "JSON parsing failed",
        "suggested_role": "SD",
        "skill_gaps": ["N/A1","N/A2","N/A3"],
        "learning_tips": ["N/A1","N/A2","N/A3"]
    }


@app.post("/match")
def match_resume(req: AnalyzeRequest):
    try:
        # Build messages for LangChain
        messages = prompt.format_messages(resume_text=req.resume_text)

        # Call Llama model
        response = llm.invoke(messages)

        # Some LangChain versions may return objects with non-str content
        content = getattr(response, "content", response)
        content = str(content)

        # Log raw model output to console for debugging
        print("🔍 RAW LLM OUTPUT:")
        print(repr(content))

        data = _safe_parse_json(content)

        # Final safety: ensure all keys exist with correct types
        return {
            "candidate_summary": str(data.get("candidate_summary", "")),
            "suggested_role": str(data.get("suggested_role", "")),
            "skill_gaps": list(data.get("skill_gaps", [])),
            "learning_tips": list(data.get("learning_tips", [])),
        }

    except Exception:
        # Print full traceback in the Python console
        traceback.print_exc()

        # ❗ Always return valid JSON to Laravel, even on errors
        return {
            "candidate_summary": "Agent internal error (see Python logs).",
            "suggested_role": "SD",
            "skill_gaps": [
                "Investigate agent error in logs",
                "Check Groq API key and model name",
                "Validate JSON parsing logic",
            ],
            "learning_tips": [
                "Use fallback content for demo",
                "Add more robust JSON handling",
                "Show judges the logged reasoning steps",
            ],
        }

import csv
import os
from functools import lru_cache
from pathlib import Path
from typing import Any

import requests
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field


PROJECT_ROOT = Path(__file__).resolve().parents[1]
PRIVATE_ROOT = PROJECT_ROOT / "storage" / "app" / "private"
DATA_DIR = Path(os.getenv("PET_AI_DATA_DIR", PRIVATE_ROOT / "ai-data"))
BREED_DATA = DATA_DIR / "breed_symptom_frequency.csv"
OVERALL_DATA = DATA_DIR / "overall_symptom_frequency.csv"
URGENT_SYMPTOMS = {"difficulty breathing", "pale gums", "bloody stool", "swollen abdomen"}
SYMPTOM_ALIASES = {"coughing": "cough", "womiting": "vomiting"}
SYMPTOM_LABELS = {"cough": "Coughing", "vomiting": "Vomiting"}
OLLAMA_URL = os.getenv("OLLAMA_URL", "http://127.0.0.1:11434/api/generate")
OLLAMA_MODEL = os.getenv("OLLAMA_MODEL", "llama3")
OLLAMA_TIMEOUT = float(os.getenv("OLLAMA_TIMEOUT", "120"))

app = FastAPI(title="Pet Care Guidance", version="1.0.0")


class GuidanceRequest(BaseModel):
    species: str = Field(min_length=1, max_length=50)
    breed: str | None = Field(default=None, max_length=80)
    age_months: int | None = Field(default=None, ge=0, le=600)
    weight_kg: float | None = Field(default=None, gt=0, le=500)
    symptoms: list[str] = Field(default_factory=list, max_length=20)


def normalize(value: str | None) -> str:
    return " ".join((value or "").strip().lower().split())


def normalize_symptom(value: str) -> str:
    symptom = normalize(value)
    return SYMPTOM_ALIASES.get(symptom, symptom)


def symptom_label(value: str) -> str:
    normalized = normalize_symptom(value)
    return SYMPTOM_LABELS.get(normalized, normalized.capitalize())


def read_csv(path: Path) -> list[dict[str, str]]:
    try:
        with path.open(newline="", encoding="utf-8-sig") as csv_file:
            return list(csv.DictReader(csv_file))
    except OSError as error:
        raise HTTPException(status_code=503, detail=f"AI data file is unavailable: {path.name}") from error


@lru_cache(maxsize=1)
def load_data() -> tuple[list[dict[str, str]], list[dict[str, str]]]:
    return read_csv(BREED_DATA), read_csv(OVERALL_DATA)


def generate_educational_guidance(context: dict[str, Any]) -> str:
    observations = "\n".join(
        f"- {item['display_symptom']}: observed in {item['breed_frequency_percent']:g}% of similar {context['breed'] or context['species']} records"
        for item in context["dataset_observations"]
    ) or "- No matching symptom observations were found in the supplied dataset."
    symptoms = "\n".join(f"- {symptom}" for symptom in context["reported_symptoms"]) or "- None reported"
    prompt = f"""Species: {context['species']}
Breed: {context['breed'] or 'Not provided'}
Age: {context['age_months'] if context['age_months'] is not None else 'Not provided'} months
Weight: {context['weight_kg'] if context['weight_kg'] is not None else 'Not provided'} kg

Reported symptoms:
{symptoms}

Dataset observations:
{observations}

Instruction:
Provide educational pet-care guidance based on the provided
breed, species, age, weight, symptoms, and dataset observations.
Do not diagnose diseases.
Do not prescribe medication.
Recommend veterinary consultation when appropriate.
Explain that dataset frequencies are descriptive and cannot diagnose an individual pet.
Keep the guidance concise, practical, and free of greetings or role-play.
Do not invent or change any dataset percentages.
Limit your response to 120 words."""
    try:
        response = requests.post(
            OLLAMA_URL,
            json={
                "model": OLLAMA_MODEL,
                "prompt": prompt,
                "stream": False,
                "options": {"num_predict": 180, "temperature": 0.2},
            },
            timeout=OLLAMA_TIMEOUT,
        )
        response.raise_for_status()
        return str(response.json()["response"]).strip()
    except (requests.RequestException, KeyError, TypeError, ValueError) as error:
        raise HTTPException(
            status_code=503,
            detail="Educational guidance is temporarily unavailable. Check that the configured Ollama model is running.",
        ) from error


@app.get("/health")
def health() -> dict[str, Any]:
    return {"status": "ok", "engine": "Ollama", "configured_model": OLLAMA_MODEL}


@app.get("/symptoms")
def symptoms() -> dict[str, list[str]]:
    breed_rows, overall_rows = load_data()
    labels = {
        normalize(row["Symptoms"])
        for row in [*breed_rows, *overall_rows]
        if normalize(row.get("Symptoms"))
    }
    return {"symptoms": sorted(labels)}


@app.post("/guidance")
def guidance(request: GuidanceRequest) -> dict[str, Any]:
    breed_rows, overall_rows = load_data()
    species = normalize(request.species)
    breed = normalize(request.breed)
    requested = list(dict.fromkeys(normalize_symptom(symptom) for symptom in request.symptoms if normalize(symptom)))
    available = {
        normalize_symptom(row["Symptoms"])
        for row in [*breed_rows, *overall_rows]
        if normalize(row.get("Symptoms"))
    }
    unsupported = sorted(set(requested) - available)
    if unsupported:
        raise HTTPException(
            status_code=422,
            detail=f"Symptoms are not present in the datasets: {', '.join(unsupported)}",
        )
    frequency_rows = []

    for symptom in requested:
        breed_row = next((row for row in breed_rows if normalize(row["Species"]) == species and normalize(row["Breed"]) == breed and normalize(row["Symptoms"]) == symptom), None)
        overall_row = next((row for row in overall_rows if normalize(row["Symptoms"]) == symptom), None)
        frequency_rows.append({
            "symptom": symptom,
            "display_symptom": symptom_label(symptom),
            "breed_frequency_percent": float(breed_row["observed_frequency_%"]) if breed_row else 0.0,
            "overall_frequency_percent": float(overall_row["dataset_frequency_%"]) if overall_row else 0.0,
            "known_for_breed": breed_row is not None,
        })

    frequency_rows.sort(key=lambda row: row["breed_frequency_percent"], reverse=True)
    age_years = request.age_months / 12 if request.age_months is not None else None
    context = {
        "species": request.species,
        "breed": request.breed,
        "age_months": request.age_months,
        "weight_kg": request.weight_kg,
        "reported_symptoms": [symptom_label(symptom) for symptom in requested],
        "dataset_observations": frequency_rows,
    }
    return {
        **context,
        "age_years": age_years,
        "guidance": generate_educational_guidance(context),
    }
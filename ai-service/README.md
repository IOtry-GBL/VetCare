# Pet Care AI Service

The service loads the trained scikit-learn model from `storage/app/private/models/pet_symptom_model.joblib` and reads its frequency data from `storage/app/private/ai-data/`.

The model was serialized with scikit-learn 1.6.1, so keep that version pinned in this service environment. From the repository root, create the environment and install dependencies with:

```powershell
py -3.11 -m venv ai-service\venv311
ai-service\venv311\Scripts\python.exe -m pip install -r ai-service\requirements.txt
```

Start the API from the repository root:

```powershell
ai-service\venv311\Scripts\python.exe -m uvicorn api:app --app-dir ai-service --host 127.0.0.1 --port 8001
```

Laravel calls `http://127.0.0.1:8001/guidance` by default. The service uses Ollama (`llama3` by default) to generate concise educational guidance from the selected pet profile, reported symptoms, and supplied dataset observations. Configure `OLLAMA_URL`, `OLLAMA_MODEL`, and `OLLAMA_TIMEOUT` in the service environment as needed. Local text generation may take over a minute on CPU; the default timeout is 120 seconds. The `.joblib` file is a separate profile-based classifier and is not loaded by this guidance flow because it does not generate educational text or use reported symptoms. None of these outputs are a veterinary diagnosis.
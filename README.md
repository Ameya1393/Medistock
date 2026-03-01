## holoHC REDCap Datalake – Phase 1 Prototype

This repository contains **Phase 1 of the REDCap → Datalake prototype**, focused on:

- **Reliable data extraction** from the REDCap API  
- **Immutable raw data preservation**  
- **Normalized, analysis-ready tables**  
- **Clear, reproducible pipeline steps**  

---

## 1. Overview

Phase 1 establishes a deterministic, reproducible data pipeline that:

- Extracts data from the REDCap API with retry handling  
- Preserves immutable raw JSON exports  
- Produces clean, normalized CSV tables for downstream analysis  

The design follows a strict **RAW → PROCESSED** pattern:

- **RAW layer**: exact exports from REDCap, stored as JSON with date-stamped filenames  
- **PROCESSED layer**: normalized, analysis-ready CSV tables  

---

## 2. Prerequisites

You must have the following installed:

- **Python 3.10+**
- **pip**
- Access to a **REDCap API token** for the target project

Verify Python installation:

```bash
python --version
```

---

## 3. Repository Structure

`holohc-datalake-prototype/`:

```text
holohc-datalake-prototype/
├─ data/
│  ├─ raw/
│  │  └─ redcap/
│  │     ├─ metadata_YYYYMMDD.json
│  │     ├─ instruments_YYYYMMDD.json
│  │     └─ records_YYYYMMDD.json
│  └─ processed/
│     ├─ patients.csv
│     ├─ visits.csv
│     ├─ labs.csv
│     └─ behaviors.csv
├─ scripts/
│  ├─ extract_metadata.py
│  ├─ extract_records.py
│  ├─ normalize_patients.py
│  ├─ normalize_visits_clean.py
│  ├─ normalize_labs.py
│  └─ normalize_behaviors.py
└─ README.md
```

---

## 4. Install Dependencies

From the project root:

```bash
pip install requests pandas
```

**(Optional) Using a virtual environment:**

```bash
python -m venv .venv

# Linux / macOS
source .venv/bin/activate

# Windows
.\.venv\Scripts\activate

pip install requests pandas
```

---

## 5. Configure REDCap API Access

Set your REDCap API token as an environment variable.

**Windows (PowerShell):**

```powershell
setx REDCAP_API_TOKEN "your_api_token_here"
```

Restart the terminal after setting this.

**Linux / macOS:**

```bash
export REDCAP_API_TOKEN="your_api_token_here"
```

For persistence, add this line to your shell profile.

---

## 6. Raw Data Extraction (Source Layer)

All raw REDCap exports are written to:

- `data/raw/redcap/`

Files are date-stamped using `YYYYMMDD`.

### 6.1 Extract Metadata & Instruments

```bash
python scripts/extract_metadata.py
```

**Outputs:**

- `data/raw/redcap/metadata_YYYYMMDD.json`  
- `data/raw/redcap/instruments_YYYYMMDD.json`  

These files preserve the REDCap project structure at the time of export.

### 6.2 Extract Records

```bash
python scripts/extract_records.py
```

This step:

- Calls the REDCap API to export records  
- Implements retry logic for transient failures  
- Handles unstable REDCap API responses  
- Writes verbatim raw JSON without transformation  

**Output:**

- `data/raw/redcap/records_YYYYMMDD.json`

**Important:**  
The **RAW layer is append-only**. Do **not** edit or overwrite raw files. Generate a new dated export instead.

---

## 7. Data Normalization (Processed Layer)

Processed, analysis-ready tables are written to:

- `data/processed/`

Ensure the directory exists:

```bash
mkdir -p data/processed
```

### 7.1 Normalize Patients

```bash
python scripts/normalize_patients.py
```

**Output:**

- `data/processed/patients.csv`

**Description:**

- One row per patient  
- Core demographics and baseline attributes  

### 7.2 Normalize Visits

```bash
python scripts/normalize_visits_clean.py
```

**Output:**

- `data/processed/visits.csv`

**Description:**

- One row per patient per visit  
- Visit dates, compliance, hospitalization, and mortality flags  

### 7.3 Normalize Labs

```bash
python scripts/normalize_labs.py
```

**Output:**

- `data/processed/labs.csv`

**Schema (long format):**

- `study_id`  
- `visit`  
- `lab_name`  
- `value`  

Each row represents a single lab measurement.

### 7.4 Normalize Behaviors

```bash
python scripts/normalize_behaviors.py
```

**Output:**

- `data/processed/behaviors.csv`

**Description:**

- Behavioral indicators normalized per patient per visit  

---

## 8. Data Quality & Reliability Notes

- The REDCap API may intermittently raise errors such as `ConnectionResetError (10054)`  
- Extraction scripts include retry logic to mitigate transient failures  

If failures persist:

- Re-run the extraction script  
- Keep existing raw files for traceability  

---

## 9. Reproducibility Guidelines

To reproduce a dataset snapshot:

- Do not modify files under `data/raw/redcap/`  
- Note the `YYYYMMDD` suffix of the raw exports  
- Re-run normalization scripts to regenerate CSVs  
- Commit processed outputs for versioned traceability

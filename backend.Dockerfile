FROM python:3.10-slim

WORKDIR /app

# Install system dependencies if required by psycopg2 or others
RUN apt-get update && apt-get install -y \
    gcc \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

COPY . .

# Render dynamically assigns PORT, fallback to 8000
ENV PORT=8000
EXPOSE ${PORT}

# Run Uvicorn
CMD ["sh", "-c", "uvicorn main:app --host 0.0.0.0 --port ${PORT}"]

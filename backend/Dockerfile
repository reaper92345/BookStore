FROM python:3.9

# Set the working directory
WORKDIR /app

# Install System Dependencies
# 1. default-libmysqlclient-dev & pkg-config: For MySQL
# 2. poppler-utils: Required if you use 'pdf2image'
# 3. libmagickwand-dev: Required if you use 'Wand' or ImageMagick
RUN apt-get update && apt-get install -y \
    default-libmysqlclient-dev \
    pkg-config \
    poppler-utils \
    imagemagick \
    libmagickwand-dev \
    && rm -rf /var/lib/apt/lists/*

# Fix ImageMagick security policy to allow PDF processing
RUN sed -i 's/rights="none" pattern="PDF"/rights="read|write" pattern="PDF"/' /etc/ImageMagick-6/policy.xml || true

# Copy the requirements file
COPY requirements.txt .

# Install the Python dependencies
RUN pip install --no-cache-dir -r requirements.txt

# Copy the application code
COPY app ./app

# Expose the port
EXPOSE 8000

# Command to run the application
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]
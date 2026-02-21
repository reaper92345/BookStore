from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
import traceback
from fastapi.middleware.cors import CORSMiddleware
from app.api.routes import router as api_router
from app.database import init_db
from fastapi.staticfiles import StaticFiles
import os

app = FastAPI()

# Initialize database on startup
try:
    init_db()
except Exception as e:
    print(f"Database initialization error: {e}")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Serve uploads directory
os.makedirs("uploads", exist_ok=True)
app.mount("/uploads", StaticFiles(directory="uploads"), name="uploads")

# Include the API router
app.include_router(api_router)

@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    # Log the full error on the server side
    print(f"Global exception caught: {exc}")
    traceback.print_exc()
    # Return a clean error to the client
    return JSONResponse(
        status_code=500,
        content={
            "detail": "Internal Server Error. Please check backend logs for details.",
        }
    )

@app.get("/")
def read_root():
    return {"message": "Welcome to the Online Bookstore API"}

@app.get("/health")
def health_check():
    return {"status": "healthy"}
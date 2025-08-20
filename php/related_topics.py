import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'

import tensorflow as tf
from flask import Flask, request, jsonify
from sentence_transformers import SentenceTransformer, util
import torch

app = Flask(__name__)

# Load model only once (RAM-cached)
model = SentenceTransformer('all-MiniLM-L6-v2')
topic_pool = [
    "requirements engineering", "software testing", "agile", "refactoring", "design patterns",
    "deep learning", "neural networks", "natural language processing", "computer vision",
    "reinforcement learning", "data mining", "big data", "cloud computing",
    "cybersecurity", "encryption", "network security", "malware detection",
    "machine learning", "transfer learning", "explainable AI", "generative AI",
    "software architecture", "microservices", "DevOps", "continuous integration",
    "software reliability", "static code analysis", "code quality", "technical debt",
    "ontology learning", "AI ethics", "autonomous systems", "robotics",
    "speech recognition", "semantic web", "graph neural networks", "AI in healthcare",
    "IoT security", "blockchain", "quantum computing", "bioinformatics",
    "user experience", "human-computer interaction", "recommender systems",
    "multimodal learning", "sentiment analysis", "social network analysis",
    "game engines", "Unity development", "Unreal Engine", "2D game development", "3D game development",
    "physics in games", "collision detection", "game AI", "pathfinding algorithms", "procedural generation",
    "game mechanics", "game design patterns", "level design", "player behavior modeling",
    "multiplayer networking", "lag compensation", "client-server architecture in games",
    "virtual reality games", "augmented reality in games", "XR development",
    "mobile game development", "console game development", "PC game optimization",
]

topic_embeddings = model.encode(topic_pool, convert_to_tensor=True)

@app.route('/related_topics')
def get_related_topics():
    query = request.args.get("q", "")
    if not query.strip():
        return jsonify([])

    query_embed = model.encode(query, convert_to_tensor=True)
    sims = util.pytorch_cos_sim(query_embed, topic_embeddings)[0]
    top_indices = torch.topk(sims, k=5).indices.tolist()

    top_topics = [topic_pool[i] for i in top_indices]
    return jsonify(top_topics)

if __name__ == '__main__':
    app.run(port=5001, debug=True)

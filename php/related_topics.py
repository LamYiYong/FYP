import sys
import json
from sentence_transformers import SentenceTransformer, util

# model and topic_pool
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
        "multimodal learning", "sentiment analysis", "social network analysis"
]

topic_embeddings = model.encode(topic_pool, convert_to_tensor=True)

def get_related(query):
    query_embed = model.encode(query, convert_to_tensor=True)
    sims = util.pytorch_cos_sim(query_embed, topic_embeddings)[0]
    top_indices = sims.argsort(descending=True)[:5]
    return [topic_pool[i] for i in top_indices]

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps([]))
    else:
        query = sys.argv[1]
        topics = get_related(query)
        print(json.dumps(topics))

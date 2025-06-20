# aggregator.py
import sys
import json
from semanticscholar import fetch_papers as fetch_semantic
from crossref import fetch_crossref_papers

def aggregate_results(query, limit=20):
    semantic_results = fetch_semantic(query, 0, limit // 2)
    crossref_results = fetch_crossref_papers(query, rows=limit // 2)

    return semantic_results + crossref_results

if __name__ == "__main__":
    if len(sys.argv) > 1:
        query = sys.argv[1]
        limit = int(sys.argv[2]) if len(sys.argv) > 2 else 30
        combined = aggregate_results(query, limit)
        print(json.dumps(combined))
    else:
        print(json.dumps({"error": "No query provided"}))

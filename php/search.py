# search.py
import sys
import json
import requests

def fetch_papers(query, offset=0, limit=5):
    url = "https://api.semanticscholar.org/graph/v1/paper/search"
    params = {
        "query": query,
        "offset": offset,
        "limit": limit,
        "fields": "title,authors,abstract,year,externalIds,url,citationCount"
    }

    response = requests.get(url, params=params)
    data = response.json()
    
    results = []
    for paper in data.get("data", []):
        results.append({
            "title": paper.get("title", ""),
            "authors": [author.get("name", "") for author in paper.get("authors", [])],
            "abstract": paper.get("abstract", "No abstract available."),
            "year": paper.get("year", 0),
            "url": paper.get("url", ""),
            "num_citations": paper.get("citationCount", 0)
        })

    return results

if __name__ == "__main__":
    if len(sys.argv) > 1:
        query = sys.argv[1]
        offset = int(sys.argv[2]) if len(sys.argv) > 2 else 0
        limit = int(sys.argv[3]) if len(sys.argv) > 3 else 5
        papers = fetch_papers(query, offset, limit)
        print(json.dumps(papers))
    else:
        print(json.dumps({"error": "No query provided"}))

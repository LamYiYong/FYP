# crossref.py
import sys
import json
import requests

def fetch_crossref_papers(query, rows=15):
    url = "https://api.crossref.org/works"
    params = {
        "query": query,
        "rows": rows
    }

    response = requests.get(url, params=params)
    data = response.json()

    results = []
    for item in data.get("message", {}).get("items", []):
        # Format authors
        authors = []
        if "author" in item:
            for author in item["author"]:
                full_name = f"{author.get('given', '')} {author.get('family', '')}".strip()
                if full_name:
                    authors.append(full_name)

        results.append({
            "title": item.get("title", ["No title"])[0],
            "authors": authors,
            "abstract": "Not available",  # CrossRef usually doesn't return abstracts
            "year": item.get("issued", {}).get("date-parts", [[0]])[0][0],
            "url": item.get("URL", ""),
            "num_citations": 0  # CrossRef doesn't return citation count
        })

    return results

if __name__ == "__main__":
    if len(sys.argv) > 1:
        query = sys.argv[1]
        rows = int(sys.argv[2]) if len(sys.argv) > 2 else 15
        papers = fetch_crossref_papers(query, rows)
        print(json.dumps(papers))
    else:
        print(json.dumps({"error": "No query provided"}))

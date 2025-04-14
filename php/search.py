import sys
import json
from scholarly import scholarly

def fetch_papers(query, max_results=5):
    search_query = scholarly.search_pubs(query)
    results = []

    for _ in range(max_results):
        try:
            paper = next(search_query)
            results.append({
                "title": paper.get("bib", {}).get("title", "No title"),
                "author": paper.get("bib", {}).get("author", "No author"),
                "abstract": paper.get("bib", {}).get("abstract", "No abstract"),
                "year": paper.get("bib", {}).get("pub_year", "Unknown"),
                "url": paper.get("pub_url", "Unavailable")
            })
        except StopIteration:
            break

    return results

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("No query provided")
        sys.exit(1)

    query = sys.argv[1]
    papers = fetch_papers(query)

    with open("results.json", "w", encoding="utf-8") as f:
        json.dump(papers, f, indent=2, ensure_ascii=False)

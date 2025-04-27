# search.py
import sys
import json
from scholarly import scholarly

def fetch_papers(query):
    search_query = scholarly.search_pubs(query)
    results = []
    for i in range(5):  # Get top 5 results
        try:
            paper = next(search_query)
            results.append({
                "title": paper['bib'].get('title', ''),
                "authors": paper['bib'].get('author', []),
                "abstract": paper['bib'].get('abstract', ''),
                "year": paper['bib'].get('pub_year', 0),
                "venue": paper['bib'].get('venue', ''),
                "url": paper.get('pub_url', ''),
                "num_citations": paper.get('num_citations', 0)  # For popularity sorting
            })
        except StopIteration:
            break
    return results

if __name__ == "__main__":
    if len(sys.argv) > 1:
        query = sys.argv[1]
        papers = fetch_papers(query)
        print(json.dumps(papers))
    else:
        print(json.dumps({"error": "No query provided"}))

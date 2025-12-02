from flask import Flask, request, jsonify
import aiohttp
import asyncio
import feedparser

app = Flask(__name__)

async def fetch_arxiv(query, limit=10):
    url = f"http://export.arxiv.org/api/query?search_query=all:{query}&start=0&max_results={limit}"
    async with aiohttp.ClientSession() as session:
        async with session.get(url) as response:
            xml = await response.text()
            feed = feedparser.parse(xml)
            results = []
            for entry in feed.entries:
                results.append({
                    'title': entry.title,
                    'abstract': entry.summary,
                    'url': entry.link,
                    'pdf_url': entry.id.replace('abs', 'pdf') + '.pdf',
                    'authors': [author.name for author in entry.authors],
                    'year': int(entry.published[:4]) if entry.published else 0,
                    'num_citations': 0,
                    'source': 'arXiv'
                })
            return results

async def fetch_crossref(query, limit=10):
    url = f"https://api.crossref.org/works?query={query}&rows={limit}"
    async with aiohttp.ClientSession() as session:
        async with session.get(url) as response:
            data = await response.json()
            results = []
            for item in data.get('message', {}).get('items', []):
                results.append({
                    'title': item.get('title', [''])[0],
                    'abstract': item.get('abstract', ''),
                    'url': item.get('URL', ''),
                    'pdf_url': None,
                    'authors': [f"{a.get('given', '')} {a.get('family', '')}".strip() for a in item.get('author', [])],
                    'year': item.get('issued', {}).get('date-parts', [[0]])[0][0] or 0,
                    'num_citations': item.get('is-referenced-by-count', 0),
                    'source': 'Crossref'
                })
            return results

@app.route('/aggregate')
def aggregate():
    query = request.args.get('q', '')
    limit = int(request.args.get('limit', 20))

    if not query:
        return jsonify({"error": "No query provided"}), 400

    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)
    arxiv_task = fetch_arxiv(query, limit // 2)
    crossref_task = fetch_crossref(query, limit // 2)

    results = loop.run_until_complete(asyncio.gather(arxiv_task, crossref_task))

    return jsonify(results[0] + results[1])

if __name__ == "__main__":
    app.run(debug=True)

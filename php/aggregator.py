from flask import Flask, request, jsonify
import asyncio
import aiohttp
import feedparser

app = Flask(__name__)

# --- Fetch from ArXiv ---
async def fetch_arxiv(query, limit=10):
    try:
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
    except Exception as e:
        print(f"[ArXiv Error] {e}")
        return []

# --- Fetch from Crossref ---
async def fetch_crossref(session, query, limit=10):
    try:
        url = f"https://api.crossref.org/works?query={query}&rows={limit}"
        async with session.get(url) as response:
            data = await response.json()
            results = []
            for item in data.get('message', {}).get('items', []):
                results.append({
                    'title': item.get('title', [''])[0] if isinstance(item.get('title'), list) else item.get('title', ''),
                    'abstract': item.get('abstract', ''),
                    'url': item.get('URL', ''),
                    'pdf_url': None,  # Crossref usually doesn't provide PDF
                    'authors': [f"{a.get('given', '')} {a.get('family', '')}".strip() for a in item.get('author', [])] if 'author' in item else [],
                    'year': item.get('issued', {}).get('date-parts', [[0]])[0][0] or 0,
                    'num_citations': item.get('is-referenced-by-count', 0),
                    'source': 'Crossref'
                })
            return results
    except Exception as e:
        print(f"[Crossref Error] {e}")
        return []

# --- Main API Route ---
@app.route('/aggregate', methods=['GET'])
async def aggregate():
    query = request.args.get('q', '')
    limit = int(request.args.get('limit', 20))

    if not query:
        return jsonify({'error': 'No query provided'}), 400

    try:
        async with aiohttp.ClientSession() as session:
            arxiv_task = fetch_arxiv(query, limit // 2)
            crossref_task = fetch_crossref(session, query, limit // 2)
            results = await asyncio.gather(arxiv_task, crossref_task)
            return jsonify(results[0] + results[1])
    except Exception as e:
        import traceback
        traceback.print_exc()
        return jsonify({'error': f'Server error: {str(e)}'}), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)

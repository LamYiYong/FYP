# Intellit – AI-Driven Research Paper Recommendation System

Intellit is an AI-powered web-based platform designed to help university students and educators efficiently discover relevant academic research papers using Natural Language Processing (NLP) techniques like semantic similarity and topic modeling. It integrates data from open-access scholarly databases such as ArXiv and Crossref to provide dynamic, personalized recommendations.

## 📌 Features

- **Smart Search**: Search for academic papers by keyword or topic.
- **Filter by Year**: Refine search results by publication year.
- **Paper Bookmarking**: Save papers for later reading.
- **Related Topic Suggestions**: Get semantically related topics.
- **Personalized Recommendations**: Based on your search history.
- **Citation Generator**: Create APA, MLA, and IEEE citations.
- **Profile Dashboard**: View search stats, trends, and saved items.

## 🧠 Technologies Used

### Frontend:
- HTML5, CSS3, JavaScript

### Backend:
- PHP (User management, UI logic)
- Python (Flask for APIs, NLP with `sentence-transformers`)
- MySQL (Database via phpMyAdmin)

### Libraries & Tools:
- Flask
- aiohttp
- feedparser
- sentence-transformers (`all-MiniLM-L6-v2`)
- XAMPP (Apache + MySQL)
- GitHub (version control)

## 📊 System Architecture

The system is organized into four layers:
- **Client-Side**: Browser-based interaction
- **Frontend Layer**: UI and page interactivity
- **Backend Layer**: PHP + Python logic
- **Data Layer**: MySQL database (users, bookmarks, papers, search logs)

## 🧪 Testing

- ✅ Unit Testing: All major features tested including login, search, bookmarking, citation, and dashboard.
- ✅ Usability Testing: 30+ participants evaluated system interface, usability, and usefulness.
- ✅ Result: High user satisfaction; citation and recommendation features rated most useful.

## 🚀 Setup Instructions

1. Clone the repo or download the files.
2. Run XAMPP and start Apache & MySQL.
3. Import the database schema via phpMyAdmin.
4. Serve the PHP files under `htdocs`.
5. Start the Python backend server using Flask:
   ```bash
   python aggregator.py
   python related_topics.py
6. Access the site at: http://localhost/your-folder-name/

## Security Notes
1. Basic input validation on both frontend and backend.
2. No authentication for APIs (local environment).
3. No role-based access – all users share the same access level.

## 📈 Future Enhancements
1. Add mobile responsiveness and dark mode
2. Summarization of research content
3. Chatbot assistant and mood-based paper suggestions
4. Paper link-based citation generator

## 👨‍💻 Developer
Lam Yi Yong
Bachelor of Computer Science (Hons) Software Engineering
Faculty of Computing and Informatics, Multimedia University
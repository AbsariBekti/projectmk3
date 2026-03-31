<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Premium - Store API</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --accent-gradient: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(236, 72, 153, 0.15) 0%, transparent 50%);
            background-attachment: fixed;
        }

        header {
            width: 100%;
            padding: 4rem 2rem;
            text-align: center;
            animation: fadeInDown 1s ease-out;
        }

        h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        p.subtitle {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            padding: 2rem;
            flex: 1;
        }

        .loader {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
            font-size: 1.5rem;
            color: var(--text-muted);
            animation: pulse 1.5s infinite;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
        }

        .grid.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
            transition: left 0.5s ease;
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 20px 40px rgba(139, 92, 246, 0.2);
        }

        .card:hover::before {
            left: 100%;
        }

        .card h2 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .card:hover .card-icon {
            transform: scale(1.2) rotate(5deg);
        }

        .status-badge {
            display: inline-block;
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            padding: 0.25rem 1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 1rem;
        }

        .error-state {
            text-align: center;
            color: #ef4444;
            padding: 2rem;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 15px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { opacity: 0.6; transform: scale(0.98); }
            50% { opacity: 1; transform: scale(1); }
            100% { opacity: 0.6; transform: scale(0.98); }
        }

        @media (max-width: 768px) {
            h1 { font-size: 2.5rem; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <header>
        <h1>Koleksi Store</h1>
        <p class="subtitle">Jelajahi kategori eksklusif kami, dilayani langsung dari API berkinerja tinggi.</p>
    </header>

    <main class="container">
        <div id="loader" class="loader">
            Mengambil data dari API...
        </div>
        
        <div id="error-container" style="display: none;" class="error-state"></div>

        <div id="categories-grid" class="grid">
            <!-- Cards will be injected here via JS -->
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const apiUrl = '{{ url("/api/categories") }}';
            const grid = document.getElementById('categories-grid');
            const loader = document.getElementById('loader');
            const errorContainer = document.getElementById('error-container');

            // Quick function to assign emoji based on name
            const getEmoji = (name) => {
                const n = name.toLowerCase();
                if (n.includes('baju') || n.includes('kaos') || n.includes('kemeja')) return '👕';
                if (n.includes('celana') || n.includes('jeans')) return '👖';
                if (n.includes('sepatu') || n.includes('sneakers')) return '👟';
                if (n.includes('tas') || n.includes('bag')) return '🎒';
                if (n.includes('topi')) return '🧢';
                if (n.includes('elektronik') || n.includes('hp')) return '📱';
                return '✨'; // default
            };

            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(result => {
                    loader.style.display = 'none';
                    
                    // result.data contains the array of categories (based on our CategoryController format)
                    const categories = result.data || [];

                    if (categories.length === 0) {
                        errorContainer.style.display = 'block';
                        errorContainer.innerHTML = '<h3>Belum ada kategori yang ditambahkan.</h3>';
                        return;
                    }

                    categories.forEach((cat, index) => {
                        const card = document.createElement('div');
                        card.className = 'card';
                        
                        // Stagger effect for cards
                        card.style.transitionDelay = `${index * 0.1}s`;

                        const emoji = getEmoji(cat.name);
                        
                        card.innerHTML = `
                            <div class="card-icon">${emoji}</div>
                            <h2>${cat.name}</h2>
                            <div class="status-badge">Tersedia</div>
                        `;
                        
                        grid.appendChild(card);
                    });

                    // Trigger reflow to start transition
                    void grid.offsetWidth;
                    grid.classList.add('visible');
                })
                .catch(error => {
                    loader.style.display = 'none';
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = `
                        <h2>Opps! Gagal memuat data.</h2>
                        <p style="margin-top: 0.5rem">Error: ${error.message}</p>
                        <p style="margin-top: 0.5rem; font-size: 0.9em; opacity: 0.8">Pastikan server API sudah berjalan dengan baik.</p>
                    `;
                    console.error('API Fetch Error:', error);
                });
        });
    </script>
</body>
</html>

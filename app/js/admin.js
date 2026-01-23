const API = "http://localhost:8000/index.php";

const loginSection = document.getElementById("loginSection");
const loginForm = document.getElementById("loginForm");
const adminPanel = document.getElementById("adminPanel");
const logoutBtn = document.getElementById("logoutBtn");
const movieForm = document.getElementById('movieForm');

const btnTabMovies = document.getElementById('btnTabMovies');
const btnTabProfile = document.getElementById('btnTabProfile');
const sectionMovies = document.getElementById('sectionMovies');
const sectionProfile = document.getElementById('sectionProfile');

// --- LOGOWANIE ---
if (loginForm) {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const res = await fetch(`${API}?controller=auth&action=login`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        username: document.getElementById("username").value,
        password: document.getElementById("password").value,
      }),
    });

    if (res.ok) {
      loginSection.classList.add("hidden");
      adminPanel.classList.remove("hidden");
      loadMovies();
    } else {
      alert("Błędne dane logowania");
    }
  });
}

// --- WYLOGOWANIE (Przywrócone) ---
if (logoutBtn) {
  logoutBtn.addEventListener("click", async () => {
    try {
      await fetch(`${API}?controller=auth&action=logout`);
      location.reload(); // Najbezpieczniejszy sposób na wyczyszczenie panelu
    } catch (error) {
      console.error("Błąd wylogowania:", error);
    }
  });
}

// --- ZAPIS FILMU (Dodawanie / Edycja) ---
if (movieForm) {
  movieForm.onsubmit = async (e) => {
    e.preventDefault();
    const data = {
      id: document.getElementById('movieId').value,
      title: document.getElementById('title').value,
      year: document.getElementById('year').value,
      rating: document.getElementById('rating').value,
      platform: document.getElementById('platform').value,
      type: document.getElementById('type').value,
      duration: document.getElementById('duration').value,
      poster: document.getElementById('poster').value,
      description: document.getElementById('description').value
    };

    const action = data.id ? 'update' : 'create';

    try {
      const res = await fetch(`${API}?controller=movie&action=${action}`, {
        method: 'POST',
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      });

      const result = await res.text();
      try {
          const json = JSON.parse(result);
          if (res.ok) {
              alert('Sukces: Zapisano!');
              movieForm.reset();
              document.getElementById('movieId').value = ''; // Ważne: czyścimy ID po zapisie
              loadMovies();
          }
      } catch (e) {
          console.error("Serwer zwrócił błąd zamiast JSON:", result);
          alert("Błąd serwera! Sprawdź konsolę (F12).");
      }
    } catch (error) { console.error('Błąd wysyłania:', error); }
  };
}

// --- LISTA FILMÓW ---
async function loadMovies() {
    try {
        const res = await fetch(`${API}?controller=movie&action=index`);
        const text = await res.text();
        const movies = JSON.parse(text);
        const container = document.getElementById('moviesList');
        if (!container) return;

        container.innerHTML = movies.map(m => `
            <div style="border-bottom: 1px solid #333; padding: 10px; display: flex; justify-content: space-between; align-items: center;">
                <span style="color: white;">${m.title} (${m.year})</span>
                <div>
                    <button onclick="editMovie('${m.id}')" style="color:#3b82f6; background:none; border:none; cursor:pointer; margin-right: 10px;">Edytuj</button>
                    <button onclick="deleteMovie('${m.id}')" style="color:#ef4444; background:none; border:none; cursor:pointer;">Usuń</button>
                </div>
            </div>
        `).join('');
    } catch (e) {
        console.error("Błąd ładowania filmów.");
    }
}

// --- FUNKCJE GLOBALNE (Usuwanie i Edycja) ---

window.deleteMovie = async (id) => {
    if (confirm('Czy na pewno chcesz usunąć ten film?')) {
        const res = await fetch(`${API}?controller=movie&action=delete&id=${id}`);
        if (res.ok) {
            alert('Film został usunięty');
            loadMovies(); // Odświeżamy listę po usunięciu
        } else {
            alert('Błąd podczas usuwania');
        }
    }
};

window.editMovie = async (id) => {
    try {
        const res = await fetch(`${API}?controller=movie&action=show&id=${id}`);
        const movie = await res.json();

        // Wypełniamy formularz danymi do edycji
        document.getElementById('movieId').value = movie.id;
        document.getElementById('title').value = movie.title;
        document.getElementById('year').value = movie.year;
        document.getElementById('rating').value = movie.rating;
        document.getElementById('platform').value = movie.platform;
        document.getElementById('type').value = movie.type;
        document.getElementById('duration').value = movie.duration;
        document.getElementById('poster').value = movie.poster;
        document.getElementById('description').value = movie.description;

        window.scrollTo(0, 0); // Przewiń do góry, by widzieć formularz
    } catch (e) {
        console.error("Błąd pobierania danych filmu do edycji");
    }
};

// --- PRZEŁĄCZANIE ZAKŁADEK ---
if (btnTabMovies && btnTabProfile) {
  btnTabMovies.onclick = () => {
    sectionMovies.classList.remove("hidden");
    sectionProfile.classList.add("hidden");
    loadMovies();
  };
  btnTabProfile.onclick = () => {
    sectionMovies.classList.add("hidden");
    sectionProfile.classList.remove("hidden");
  };
}
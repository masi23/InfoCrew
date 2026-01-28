const API = "http://localhost:8000/index.php";

const loginSection = document.getElementById("loginSection");
const loginForm = document.getElementById("loginForm");
const adminPanel = document.getElementById("adminPanel");
const logoutBtn = document.getElementById("logoutBtn");
const movieForm = document.getElementById("movieForm");
const profileForm = document.getElementById("profileForm");

const tabs = {
  movies: { btn: document.getElementById("btnTabMovies"), section: document.getElementById("sectionMovies") },
  reviews: { btn: document.getElementById("btnTabReviews"), section: document.getElementById("sectionReviews") },
  categories: { btn: document.getElementById("btnTabCategories"), section: document.getElementById("sectionCategories") },
  platforms: { btn: document.getElementById("btnTabPlatforms"), section: document.getElementById("sectionPlatforms") },
  badwords: { btn: document.getElementById("btnTabBadwords"), section: document.getElementById("sectionBadwords") },
  profile: { btn: document.getElementById("btnTabProfile"), section: document.getElementById("sectionProfile") },
};

function switchTab(tabName) {
  Object.keys(tabs).forEach((key) => {
    tabs[key].btn?.classList.remove("active");
    tabs[key].section?.classList.add("hidden");
  });
  tabs[tabName]?.btn?.classList.add("active");
  tabs[tabName]?.section?.classList.remove("hidden");

  if (tabName === "movies") loadMovies();
  if (tabName === "reviews") loadAllReviews();
  if (tabName === "categories") loadCategories();
  if (tabName === "platforms") loadPlatforms();
}

Object.keys(tabs).forEach((key) => {
  tabs[key].btn?.addEventListener("click", () => switchTab(key));
});

loginForm?.addEventListener("submit", async (e) => {
  e.preventDefault();
  
  try {
    const res = await fetch(`${API}?controller=auth&action=login`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({
        username: document.getElementById("username").value,
        password: document.getElementById("password").value,
      }),
    });

    const data = await res.json();

    if (res.ok && data.success) {
      loginSection.classList.add("hidden");
      adminPanel.classList.remove("hidden");
      loadMovies();
    } else {
      alert(data.error || "Błędne dane logowania");
    }
  } catch (error) {
    console.error("Błąd logowania:", error);
    alert("Błąd połączenia z serwerem");
  }
});

logoutBtn?.addEventListener("click", async () => {
  try {
    await fetch(`${API}?controller=auth&action=logout`, { credentials: "include" });
    location.reload();
  } catch (error) {
    console.error("Błąd wylogowania:", error);
  }
});

movieForm?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const data = {
    id: document.getElementById("movieId").value,
    title: document.getElementById("title").value,
    year: parseInt(document.getElementById("year").value) || new Date().getFullYear(),
    rating: parseFloat(document.getElementById("rating").value) || 0,
    platform: document.getElementById("platform").value,
    type: document.getElementById("type").value,
    duration: document.getElementById("duration").value,
    country: document.getElementById("country").value,
    popularity: parseInt(document.getElementById("popularity").value) || 50,
    genres: document.getElementById("genres").value,
    cast: document.getElementById("cast").value,
    poster: document.getElementById("poster").value,
    backdrop: document.getElementById("backdrop").value,
    description: document.getElementById("description").value,
  };

  const action = data.id ? "update" : "create";

  try {
    const res = await fetch(`${API}?controller=movie&action=${action}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify(data),
    });

    const result = await res.json();

    if (res.ok && result.success) {
      alert(data.id ? "Film zaktualizowany!" : "Film dodany!");
      clearMovieForm();
      loadMovies();
    } else {
      alert(result.error || "Błąd zapisu");
    }
  } catch (error) {
    console.error("Błąd:", error);
    alert("Błąd połączenia");
  }
});

document.getElementById("clearMovieForm")?.addEventListener("click", clearMovieForm);

function clearMovieForm() {
  movieForm.reset();
  document.getElementById("movieId").value = "";
}

async function loadMovies() {
  try {
    const res = await fetch(`${API}?controller=movie&action=index`);
    const movies = await res.json();
    const container = document.getElementById("moviesList");

    if (!container) return;

    container.innerHTML = movies.map((m) => `
      <div class="item-row">
        <div>
          <strong style="color: #fff;">${m.title}</strong>
          <span style="color: #666; margin-left: 10px;">(${m.year}) - ${m.platform || 'Brak platformy'}</span>
        </div>
        <div class="actions">
          <button onclick="editMovie('${m.id}')" class="btn btn-secondary" style="padding: 5px 10px;">Edytuj</button>
          <button onclick="deleteMovie('${m.id}')" class="btn btn-danger" style="padding: 5px 10px;">Usuń</button>
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Błąd ładowania filmów:", e);
  }
}

window.editMovie = async (id) => {
  try {
    const res = await fetch(`${API}?controller=movie&action=show&id=${id}`);
    const movie = await res.json();

    document.getElementById("movieId").value = movie.id;
    document.getElementById("title").value = movie.title || "";
    document.getElementById("year").value = movie.year || "";
    document.getElementById("rating").value = movie.rating || "";
    document.getElementById("platform").value = movie.platform || "";
    document.getElementById("type").value = movie.type || "Film";
    document.getElementById("duration").value = movie.duration || "";
    document.getElementById("country").value = movie.country || movie.language || "";
    document.getElementById("popularity").value = movie.popularity || "";
    document.getElementById("genres").value = Array.isArray(movie.genres) ? movie.genres.join(", ") : "";
    document.getElementById("cast").value = Array.isArray(movie.cast) ? movie.cast.join(", ") : "";
    document.getElementById("poster").value = movie.poster || "";
    document.getElementById("backdrop").value = movie.backdrop || "";
    document.getElementById("description").value = movie.description || "";

    window.scrollTo(0, 0);
    switchTab("movies");
  } catch (e) {
    console.error("Błąd pobierania filmu:", e);
  }
};

window.deleteMovie = async (id) => {
  if (!confirm("Czy na pewno chcesz usunąć ten film?")) return;

  try {
    const res = await fetch(`${API}?controller=movie&action=delete&id=${id}`, { credentials: "include" });
    if (res.ok) {
      alert("Film usunięty");
      loadMovies();
    } else {
      alert("Błąd usuwania");
    }
  } catch (e) {
    console.error("Błąd:", e);
  }
};

async function loadAllReviews() {
  try {
    const res = await fetch(`${API}?controller=movie&action=allReviews`, { credentials: "include" });
    
    if (!res.ok) {
      document.getElementById("reviewsList").innerHTML = '<p style="color: #888;">Zaloguj się, aby zobaczyć recenzje.</p>';
      return;
    }

    const reviews = await res.json();
    const container = document.getElementById("reviewsList");

    if (!reviews.length) {
      container.innerHTML = '<p style="color: #888;">Brak recenzji.</p>';
      return;
    }

    container.innerHTML = reviews.map((r) => `
      <div class="review-item ${r.highlighted ? 'highlighted' : ''}">
        <div class="review-header">
          <div>
            <strong style="color: ${r.highlighted ? '#22c55e' : '#3b82f6'};">
              ${r.highlighted ? '⭐ ' : ''}${r.user}
            </strong>
            ${r.rating ? `<span style="margin-left: 10px; color: #fbbf24;">★ ${r.rating}/10</span>` : ''}
            <span class="review-meta" style="margin-left: 10px;">
              Film: <em>${r.movieTitle}</em>
            </span>
          </div>
          <span class="review-meta">${r.date} | 👍 ${r.likes || 0}</span>
        </div>
        <p class="review-text">${r.text}</p>
        <div class="review-actions">
          <button onclick="highlightReview('${r.movieId}', '${r.id}', ${!r.highlighted})" 
                  class="btn ${r.highlighted ? 'btn-secondary' : 'btn-success'}" style="padding: 5px 10px;">
            ${r.highlighted ? 'Usuń wyróżnienie' : 'Wyróżnij'}
          </button>
          <button onclick="deleteReview('${r.movieId}', '${r.id}')" 
                  class="btn btn-danger" style="padding: 5px 10px;">
            Usuń
          </button>
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Błąd ładowania recenzji:", e);
  }
}

window.highlightReview = async (movieId, reviewId, highlight) => {
  try {
    const res = await fetch(`${API}?controller=movie&action=highlightReview&movieId=${movieId}&reviewId=${reviewId}`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ highlight }),
    });

    if (res.ok) {
      loadAllReviews();
    } else {
      alert("Błąd wyróżniania recenzji");
    }
  } catch (e) {
    console.error("Błąd:", e);
  }
};

window.deleteReview = async (movieId, reviewId) => {
  if (!confirm("Czy na pewno chcesz usunąć tę recenzję?")) return;

  try {
    const res = await fetch(`${API}?controller=movie&action=deleteReview&movieId=${movieId}&reviewId=${reviewId}`, {
      credentials: "include",
    });

    if (res.ok) {
      alert("Recenzja usunięta");
      loadAllReviews();
    } else {
      alert("Błąd usuwania recenzji");
    }
  } catch (e) {
    console.error("Błąd:", e);
  }
};

async function loadCategories() {
  try {
    const res = await fetch(`${API}?controller=category&action=index`);
    const categories = await res.json();
    const container = document.getElementById("categoriesList");

    container.innerHTML = categories.map((c) => `
      <div class="item-row">
        <span style="color: #fff;">${c.name}</span>
        <div class="actions">
          <button onclick="deleteCategory(${c.id})" class="btn btn-danger" style="padding: 5px 10px;">Usuń</button>
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Błąd ładowania kategorii:", e);
  }
}

document.getElementById("addCategoryBtn")?.addEventListener("click", async () => {
  const name = document.getElementById("newCategoryName").value.trim();
  if (!name) return alert("Podaj nazwę kategorii");

  try {
    const res = await fetch(`${API}?controller=category&action=create`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ name }),
    });

    if (res.ok) {
      document.getElementById("newCategoryName").value = "";
      loadCategories();
    } else {
      alert("Błąd dodawania kategorii");
    }
  } catch (e) {
    console.error("Błąd:", e);
  }
});

window.deleteCategory = async (id) => {
  if (!confirm("Usunąć kategorię?")) return;

  try {
    const res = await fetch(`${API}?controller=category&action=delete&id=${id}`, { credentials: "include" });
    if (res.ok) loadCategories();
    else alert("Błąd usuwania");
  } catch (e) {
    console.error("Błąd:", e);
  }
};

async function loadPlatforms() {
  try {
    const res = await fetch(`${API}?controller=platform&action=index`);
    const platforms = await res.json();
    const container = document.getElementById("platformsList");

    container.innerHTML = platforms.map((p) => `
      <div class="item-row">
        <div style="display: flex; align-items: center; gap: 10px;">
          <span style="width: 20px; height: 20px; background: ${p.color}; border-radius: 4px;"></span>
          <span style="color: #fff;">${p.name}</span>
        </div>
        <div class="actions">
          <button onclick="deletePlatform(${p.id})" class="btn btn-danger" style="padding: 5px 10px;">Usuń</button>
        </div>
      </div>
    `).join("");
  } catch (e) {
    console.error("Błąd ładowania platform:", e);
  }
}

document.getElementById("addPlatformBtn")?.addEventListener("click", async () => {
  const name = document.getElementById("newPlatformName").value.trim();
  const color = document.getElementById("newPlatformColor").value;
  
  if (!name) return alert("Podaj nazwę platformy");

  try {
    const res = await fetch(`${API}?controller=platform&action=create`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ name, color }),
    });

    if (res.ok) {
      document.getElementById("newPlatformName").value = "";
      loadPlatforms();
    } else {
      alert("Błąd dodawania platformy");
    }
  } catch (e) {
    console.error("Błąd:", e);
  }
});

window.deletePlatform = async (id) => {
  if (!confirm("Usunąć platformę?")) return;

  try {
    const res = await fetch(`${API}?controller=platform&action=delete&id=${id}`, { credentials: "include" });
    if (res.ok) loadPlatforms();
    else alert("Błąd usuwania");
  } catch (e) {
    console.error("Błąd:", e);
  }
};

profileForm?.addEventListener("submit", async (e) => {
  e.preventDefault();

  const newPassword = document.getElementById("newPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (newPassword !== confirmPassword) {
    alert("Hasła nie są identyczne!");
    return;
  }

  if (newPassword.length < 6) {
    alert("Hasło musi mieć minimum 6 znaków!");
    return;
  }

  try {
    const res = await fetch(`${API}?controller=auth&action=changePassword`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({
        currentPassword: document.getElementById("currentPassword").value,
        newPassword: newPassword,
        newUsername: document.getElementById("newUsername").value || null,
      }),
    });

    const result = await res.json();

    if (res.ok && result.success) {
      alert("Hasło zostało zmienione!");
      profileForm.reset();
    } else {
      alert(result.error || "Błąd zmiany hasła");
    }
  } catch (e) {
    console.error("Błąd:", e);
    alert("Błąd połączenia");
  }
});

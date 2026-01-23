import { openModal } from "./modal.js";
import { $ } from "../utils/dom.js";

export function renderMovies(movies) {
  const grid = $("#moviesGrid");
  const counter = $("#resultsCount");
  const noResults = $("#noResults");

  counter.textContent = movies.length;
  grid.innerHTML = "";

  if (!movies.length) {
    noResults.style.display = "block";
    return;
  }

  noResults.style.display = "none";
  movies.forEach((movie) => grid.appendChild(createCard(movie)));
}

function createCard(movie) {
  const card = document.createElement("div");
  card.className = "movie-card";
  card.onclick = () => openModal(movie);

  // ZABEZPIECZENIA DANYCH (żeby uniknąć błędu slice/undefined)
  const genres = movie.genres || []; // Jeśli brak gatunków, użyj pustej tablicy
  const poster = movie.poster || 'assets/img/default-poster.jpg'; // Domyślny obrazek
  const duration = movie.duration || 'N/A'; // Jeśli brak czasu trwania

  card.innerHTML = `
    <div class="movie-poster">
      <img src="${poster}" alt="${movie.title}">
      <span class="movie-platform">${movie.platform}</span>
      <span class="movie-rating">${movie.rating}</span>
    </div>
    <div class="movie-info">
      <h3>${movie.title}</h3>
      <div class="movie-meta">${movie.year} • ${duration}</div>
      <div class="movie-genres">
        ${genres
          .slice(0, 3)
          .map((g) => `<span>${g}</span>`)
          .join("")}
      </div>
    </div>
  `;
  return card;
}
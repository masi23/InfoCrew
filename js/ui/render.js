import { openModal } from "./modal.js";
import { $ } from "../utils/dom.js";

export function renderMovies(movies) {
  const grid = $("#moviesGrid");
  const counter = $("#resultsCount");
  const noResults = $("#noResults");

  if (!grid) {
    console.error("Nie znaleziono #moviesGrid");
    return;
  }

  if (counter) counter.textContent = movies.length;
  grid.innerHTML = "";

  if (!movies.length) {
    if (noResults) noResults.style.display = "block";
    return;
  }

  if (noResults) noResults.style.display = "none";
  movies.forEach((movie) => grid.appendChild(createCard(movie)));
}

function createCard(movie) {
  const card = document.createElement("div");
  card.className = "movie-card";
  card.onclick = () => openModal(movie);

  // Zabezpieczenia danych
  const genres = movie.genres || [];
  const poster = movie.poster || 'assets/img/default-poster.jpg';
  const duration = movie.duration || 'N/A';
  const rating = movie.rating || 0;
  const year = movie.year || '';
  const platform = movie.platform || '';

  card.innerHTML = `
    <div class="movie-poster">
      <img src="${poster}" alt="${movie.title}" onerror="this.src='assets/img/default-poster.jpg'">
      <span class="movie-platform">${platform}</span>
      <span class="movie-rating">${rating}</span>
    </div>
    <div class="movie-info">
      <h3>${movie.title}</h3>
      <div class="movie-meta">${year} • ${duration}</div>
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

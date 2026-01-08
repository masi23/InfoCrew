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

  card.innerHTML = `
    <div class="movie-poster">
      <img src="${movie.poster}" alt="${movie.title}">
      <span class="movie-platform">${movie.platform}</span>
      <span class="movie-rating">${movie.rating}</span>
    </div>
    <div class="movie-info">
      <h3>${movie.title}</h3>
      <div class="movie-meta">${movie.year} • ${movie.duration}</div>
      <div class="movie-genres">
        ${movie.genres
          .slice(0, 3)
          .map((g) => `<span>${g}</span>`)
          .join("")}
      </div>
    </div>
  `;
  return card;
}

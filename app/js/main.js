// import { allMovies } from "./data/movies.js";
// import { debounce } from "./utils/debounce.js";
import { $, $$ } from "./utils/dom.js";
import { getFilters, applyFilters } from "./filters/filters.js";
import { renderMovies } from "./ui/render.js";
import { closeModal } from "./ui/modal.js";

let allMovies = [];

async function fetchMovies() {
  try {
    const response = await fetch(
      "http://localhost:8000/index.php?controller=movie&action=index",
    );
    console.log(response);
    if (!response.ok) {
      throw new Error("Błąd pobierania danych");
    }

    allMovies = await response.json();
    update();
  } catch (error) {
    console.error(error);
  }
}

async function update() {
  const filters = getFilters();
  const params = new URLSearchParams(filters);
  const response = await fetch(
    `http://localhost:8000/index.php?controller=movie&action=index&${params}`,
  );
  const data = await response.json();
  renderMovies(data);
  // const filtered = applyFilters(allMovies, filters);
  // renderMovies(filtered);
}

document.addEventListener("DOMContentLoaded", () => {
  fetchMovies();

  // $("#searchInput").addEventListener("input", debounce(update));
  $("#sortSelect").addEventListener("change", update);
  $("#resetFilters").addEventListener("click", () => {
    document.querySelector("form")?.reset();
    update();
  });

  $$(".filter-option input, .filter-select").forEach((el) =>
    el.addEventListener("change", update),
  );

  $("#modalClose").addEventListener("click", closeModal);
  $("#modalOverlay").addEventListener("click", (e) => {
    if (e.target.id === "modalOverlay") closeModal();
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
    if (e.key === "/") {
      e.preventDefault();
      $("#searchInput").focus();
    }
  });
});

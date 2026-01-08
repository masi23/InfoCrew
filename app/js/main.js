import { allMovies } from "./data/movies.js";
// import { debounce } from "./utils/debounce.js";
import { $, $$ } from "./utils/dom.js";
import { getFilters, applyFilters } from "./filters/filters.js";
import { renderMovies } from "./ui/render.js";
import { closeModal } from "./ui/modal.js";

function update() {
  const filters = getFilters();
  const filtered = applyFilters(allMovies, filters);
  renderMovies(filtered);
}

document.addEventListener("DOMContentLoaded", () => {
  renderMovies(allMovies);

  // $("#searchInput").addEventListener("input", debounce(update));
  $("#sortSelect").addEventListener("change", update);
  $("#resetFilters").addEventListener("click", () => {
    document.querySelector("form")?.reset();
    update();
  });

  $$(".filter-option input, .filter-select").forEach((el) =>
    el.addEventListener("change", update)
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

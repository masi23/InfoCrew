import { $, $$ } from "./utils/dom.js";
import { getFilters } from "./filters/filters.js";
import { renderMovies } from "./ui/render.js";
import { closeModal } from "./ui/modal.js";

const API = "http://localhost:8000/index.php";

/**
 * Pobiera kategorie z serwera i renderuje je w panelu bocznym
 */
async function loadCategories() {
  const categoryContainer = document.getElementById('dynamic-categories');
  if (!categoryContainer) return;

  try {
    const response = await fetch(`${API}?controller=category&action=index`);
    const categories = await response.json();

    categoryContainer.innerHTML = '';

    categories.forEach(category => {
      const label = document.createElement('label');
      label.className = 'filter-option';
      label.innerHTML = `
        <input type="checkbox" name="category" value="${category.name}" />
        ${category.name}
      `;
      categoryContainer.appendChild(label);
    });

    categoryContainer.querySelectorAll('input').forEach(input => {
      input.addEventListener('change', update);
    });
  } catch (error) {
    console.error('Błąd ładowania kategorii:', error);
  }
}

/**
 * Pobiera platformy z serwera i renderuje je w panelu bocznym
 */
async function loadPlatforms() {
  const platformContainer = document.getElementById('dynamic-platforms');
  if (!platformContainer) return;

  try {
    const response = await fetch(`${API}?controller=platform&action=index`);
    const platforms = await response.json();

    platformContainer.innerHTML = '';

    platforms.forEach(platform => {
      const label = document.createElement('label');
      label.className = 'filter-option';
      label.innerHTML = `
        <input type="checkbox" name="platform" value="${platform.name}" />
        ${platform.name}
      `;
      platformContainer.appendChild(label);
    });

    platformContainer.querySelectorAll('input').forEach(input => {
      input.addEventListener('change', update);
    });
  } catch (error) {
    console.error('Błąd ładowania platform:', error);
  }
}

/**
 * Pobiera przefiltrowaną listę filmów i aktualizuje widok
 */
async function update() {
  try {
    const filters = getFilters();
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
      if (value && value !== "") {
        params.append(key, value);
      }
    });

    const url = `${API}?controller=movie&action=index&${params.toString()}`;
    const response = await fetch(url);

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const data = await response.json();
    renderMovies(data);

    const resultsCount = $("#resultsCount");
    if (resultsCount) resultsCount.textContent = data.length;

    const noResults = $("#noResults");
    if (noResults) noResults.style.display = data.length === 0 ? "block" : "none";

  } catch (error) {
    console.error("Błąd aktualizacji widoku:", error);
  }
}

/**
 * Inicjalizacja po załadowaniu struktury DOM
 */
document.addEventListener("DOMContentLoaded", () => {
  // 1. Ładujemy dynamiczne filtry, a potem filmy
  Promise.all([loadCategories(), loadPlatforms()]).then(() => {
    update();
  });

  const searchInput = $("#searchInput");

  // Dynamiczne wyszukiwanie (debounce)
  let debounceTimer;
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(update, 300);
    });
  }

  // Zmiana sortowania
  $("#sortSelect")?.addEventListener("change", update);

  // Przycisk Reset filtrów
  $("#resetFilters")?.addEventListener("click", () => {
    document.querySelector("aside.filters form")?.reset();
    if (searchInput) searchInput.value = "";
    const sortSelect = $("#sortSelect");
    if (sortSelect) sortSelect.value = "popularity";
    update();
  });

  // Reakcja na statyczne filtry
  $$(".filter-option input:not([name='category']):not([name='platform']), .filter-select").forEach((el) =>
    el.addEventListener("change", update)
  );

  // --- OBSŁUGA MODALA ---
  const modalOverlay = $("#movieModal");
  document.addEventListener("click", (e) => {
    if (e.target.id === "modalClose" || e.target.classList.contains("close-modal")) {
      closeModal();
    }
    if (e.target === modalOverlay) {
      closeModal();
    }
  });

  // Obsługa klawiatury
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
    if (e.key === "/" && document.activeElement !== searchInput) {
      e.preventDefault();
      searchInput?.focus();
    }
  });
});
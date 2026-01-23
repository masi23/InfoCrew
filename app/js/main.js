import { $, $$ } from "./utils/dom.js";
import { getFilters } from "./filters/filters.js";
import { renderMovies } from "./ui/render.js";
import { closeModal } from "./ui/modal.js";

let allMovies = [];

// Początkowe pobranie filmów
async function fetchMovies() {
  try {
    const response = await fetch(
      "http://localhost:8000/index.php?controller=movie&action=index",
    );
    if (!response.ok) {
      throw new Error("Błąd pobierania danych");
    }

    allMovies = await response.json();
    update();
  } catch (error) {
    console.error("Błąd pobierania:", error);
  }
}

// Główna funkcja aktualizująca widok
async function update() {
  try {
    // 1. Pobieramy filtry z formularza bocznego
    const filters = getFilters();

    // 2. Ręcznie dołączamy wartość wyszukiwania z nagłówka
    const searchVal = $("#searchInput")?.value.trim() || "";
    if (searchVal) {
        filters.search = searchVal;
    }

    // 3. Budujemy parametry URL
    const params = new URLSearchParams(filters);

    // 4. Wysyłamy zapytanie do MovieController
    const response = await fetch(
      `http://localhost:8000/index.php?controller=movie&action=index&${params.toString()}`
    );

    if (!response.ok) throw new Error("Błąd serwera");

    const data = await response.json();

    // 5. Renderujemy przefiltrowane filmy
    renderMovies(data);

    // 6. Aktualizacja licznika "Znaleziono"
    const resultsCount = $("#resultsCount");
    if (resultsCount) {
        resultsCount.textContent = data.length;
    }

    // 7. Obsługa komunikatu "Brak wyników"
    const noResults = $("#noResults");
    if (noResults) {
        noResults.style.display = data.length === 0 ? "block" : "none";
    }

  } catch (error) {
    console.error("Błąd aktualizacji widoku:", error);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  fetchMovies();

  // Dynamiczne wyszukiwanie podczas pisania
  const searchInput = $("#searchInput");
  if (searchInput) {
    searchInput.addEventListener("input", update);
  }

  // Zmiana sortowania
  $("#sortSelect")?.addEventListener("change", update);

  // --- NAPRAWIONY PRZYCISK RESET ---
  $("#resetFilters")?.addEventListener("click", () => {
    // Resetuje tylko checkboxy i radio wewnątrz <form>
    document.querySelector("aside.filters form")?.reset();

    // Ręcznie czyścimy input w nagłówku
    if (searchInput) {
        searchInput.value = "";
    }

    // Resetujemy select sortowania
    const sortSelect = $("#sortSelect");
    if (sortSelect) {
        sortSelect.value = "popularity";
    }

    // Wymuszamy pobranie pełnej listy (puste filtry)
    update();
  });

  // Reakcja na każdy checkbox i select w panelu bocznym
  $$(".filter-option input, .filter-select").forEach((el) =>
    el.addEventListener("change", update),
  );

  // --- OBSŁUGA MODALA ---
  const modalOverlay = $("#movieModal");

  // Zamknięcie przez kliknięcie w tło lub Escape
  document.addEventListener("click", (e) => {
    if (e.target.id === "modalClose" || e.target.classList.contains("close-modal")) {
      closeModal();
    }
    if (e.target === modalOverlay) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
    if (e.key === "/") {
      e.preventDefault();
      searchInput?.focus();
    }
  });
});
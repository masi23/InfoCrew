import { $, $$ } from "./utils/dom.js";
import { getFilters } from "./filters/filters.js";
import { renderMovies } from "./ui/render.js";
import { closeModal } from "./ui/modal.js";

const API = "http://localhost:8000/index.php";

// Główna funkcja aktualizująca widok
async function update() {
  try {
    // 1. Pobieramy filtry z formularza bocznego
    const filters = getFilters();

    // 2. Budujemy parametry URL (usuwamy puste wartości)
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value && value !== "") {
        params.append(key, value);
      }
    });

    // 3. Wysyłamy zapytanie do MovieController
    const url = `${API}?controller=movie&action=index&${params.toString()}`;
    console.log("Fetching:", url);

    const response = await fetch(url);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();

    // 4. Renderujemy przefiltrowane filmy
    renderMovies(data);

    // 5. Aktualizacja licznika "Znaleziono"
    const resultsCount = $("#resultsCount");
    if (resultsCount) {
      resultsCount.textContent = data.length;
    }

    // 6. Obsługa komunikatu "Brak wyników"
    const noResults = $("#noResults");
    if (noResults) {
      noResults.style.display = data.length === 0 ? "block" : "none";
    }

  } catch (error) {
    console.error("Błąd aktualizacji widoku:", error);
  }
}

// Inicjalizacja po załadowaniu DOM
document.addEventListener("DOMContentLoaded", () => {
  // Pierwsze pobranie filmów
  update();

  const searchInput = $("#searchInput");

  // Dynamiczne wyszukiwanie podczas pisania (z debounce)
  let debounceTimer;
  if (searchInput) {
    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(update, 300);
    });
  }

  // Zmiana sortowania
  $("#sortSelect")?.addEventListener("change", update);

  // Przycisk Reset
  $("#resetFilters")?.addEventListener("click", () => {
    // Resetuj formularz filtrów
    document.querySelector("aside.filters form")?.reset();

    // Wyczyść input wyszukiwania
    if (searchInput) {
      searchInput.value = "";
    }

    // Resetuj select sortowania
    const sortSelect = $("#sortSelect");
    if (sortSelect) {
      sortSelect.value = "popularity";
    }

    // Odśwież listę
    update();
  });

  // Reakcja na każdy checkbox i select w panelu bocznym
  $$(".filter-option input, .filter-select").forEach((el) =>
    el.addEventListener("change", update)
  );

  // --- OBSŁUGA MODALA ---
  const modalOverlay = $("#movieModal");

  // Zamknięcie przez kliknięcie w tło lub przycisk X
  document.addEventListener("click", (e) => {
    if (e.target.id === "modalClose" || e.target.classList.contains("close-modal")) {
      closeModal();
    }
    if (e.target === modalOverlay) {
      closeModal();
    }
  });

  // Zamknięcie przez Escape, fokus na wyszukiwarce przez /
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeModal();
    if (e.key === "/" && document.activeElement !== searchInput) {
      e.preventDefault();
      searchInput?.focus();
    }
  });
});

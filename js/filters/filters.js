import { $, $$ } from "../utils/dom.js";

export function getFilters() {
  // Pobieramy tablice, aby móc je złączyć przecinkami dla PHP
  const platformArray = [...$$('input[name="platform"]:checked')].map((el) => el.value);
  const categoryArray = [...$$('input[name="category"]:checked')].map((el) => el.value);

  return {
    search: $("#searchInput")?.value?.toLowerCase() || "",
    // PHP oczekuje stringa "Akcja,Dramat", a nie tablicy JS
    platforms: platformArray.join(','),
    categories: categoryArray.join(','),
    country: $("#countryFilter")?.value || "",  // Zmienione z language na country
    year: $("#yearFilter")?.value || "",
    rating: $("#ratingFilter")?.value || "",
    type: document.querySelector('input[name="type"]:checked')?.value || "",
    sort: $("#sortSelect")?.value || "popularity",
  };
}

export function applyFilters(movies, filters) {
  let result = [...movies];

  if (filters.search) {
    result = result.filter(
      (m) =>
        m.title?.toLowerCase().includes(filters.search) ||
        m.description?.toLowerCase().includes(filters.search) ||
        (m.cast && m.cast.some((c) => c.toLowerCase().includes(filters.search)))
    );
  }

  // Obsługa tablic (jeśli filtry pochodzą z lokalnego JS) lub stringów (z API)
  if (filters.platforms && filters.platforms.length) {
    const pList = Array.isArray(filters.platforms) ? filters.platforms : filters.platforms.split(',');
    result = result.filter((m) => pList.includes(m.platform));
  }

  if (filters.categories && filters.categories.length) {
    const cList = Array.isArray(filters.categories) ? filters.categories : filters.categories.split(',');
    result = result.filter((m) =>
      m.genres && m.genres.some((g) => cList.includes(g))
    );
  }

  if (filters.country) {
    result = result.filter((m) => m.country === filters.country);
  }

  if (filters.year) {
    result =
      filters.year === "older"
        ? result.filter((m) => m.year < 2021)
        : result.filter((m) => m.year === Number(filters.year));
  }

  if (filters.rating) {
    result = result.filter((m) => m.rating >= Number(filters.rating));
  }

  if (filters.type) {
    result = result.filter((m) => m.type === filters.type);
  }

  return sortMovies(result, filters.sort);
}

function sortMovies(movies, sort) {
  const result = [...movies];
  switch (sort) {
    case "rating":
      return result.sort((a, b) => b.rating - a.rating);
    case "newest":
      return result.sort((a, b) => b.year - a.year);
    case "title":
      return result.sort((a, b) => a.title.localeCompare(b.title, "pl"));
    default:
      return result.sort((a, b) => (b.popularity || 0) - (a.popularity || 0));
  }
}

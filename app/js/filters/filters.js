import { $, $$ } from "../utils/dom.js";

export function getFilters() {
  return {
    search: $("#searchInput").value.toLowerCase(),
    platforms: [...$$('input[name="platform"]:checked')].map((el) => el.value),
    categories: [...$$('input[name="category"]:checked')].map((el) => el.value),
    language: $("#languageFilter").value,
    year: $("#yearFilter").value,
    rating: $("#ratingFilter").value,
    type: document.querySelector('input[name="type"]:checked')?.value || "",
    sort: $("#sortSelect").value,
  };
}

export function applyFilters(movies, filters) {
  let result = [...movies];

  if (filters.search) {
    result = result.filter(
      (m) =>
        m.title.toLowerCase().includes(filters.search) ||
        m.description.toLowerCase().includes(filters.search) ||
        m.cast.some((c) => c.toLowerCase().includes(filters.search))
    );
  }

  if (filters.platforms.length) {
    result = result.filter((m) => filters.platforms.includes(m.platform));
  }

  if (filters.categories.length) {
    result = result.filter((m) =>
      m.genres.some((g) => filters.categories.includes(g))
    );
  }

  if (filters.language) {
    result = result.filter((m) => m.language === filters.language);
  }

  if (filters.year) {
    result =
      filters.year === "older"
        ? result.filter((m) => m.year < 2019)
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
  switch (sort) {
    case "rating":
      return movies.sort((a, b) => b.rating - a.rating);
    case "newest":
      return movies.sort((a, b) => b.year - a.year);
    case "title":
      return movies.sort((a, b) => a.title.localeCompare(b.title, "pl"));
    default:
      return movies.sort((a, b) => b.popularity - a.popularity);
  }
}

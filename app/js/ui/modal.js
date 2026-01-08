import { $ } from "../utils/dom.js";

const overlay = $("#modalOverlay");

export function openModal(movie) {
  $("#modalBackdrop").src = movie.backdrop;
  $("#modalTitle").textContent = movie.title;
  $("#modalDescription").textContent = movie.description;

  $("#modalMeta").innerHTML = `
    <span>${movie.year}</span>
    <span>${movie.duration}</span>
    <span>${movie.language}</span>
    <span>${movie.platform}</span>
    <span>${movie.rating}/10</span>
  `;

  $("#modalCast").innerHTML = `
    <strong>Obsada:</strong> ${movie.cast.join(", ")}
  `;

  $("#btnWatch").onclick = () => {
    const urls = {
      Netflix: "https://www.netflix.com",
      "Prime Video": "https://www.primevideo.com",
      "Disney+": "https://www.disneyplus.com",
      "HBO Max": "https://www.max.com",
      "Apple TV+": "https://tv.apple.com",
    };
    window.open(urls[movie.platform] || "#", "_blank");
  };

  overlay.classList.add("active");
  document.body.style.overflow = "hidden";
}

export function closeModal() {
  overlay.classList.remove("active");
  document.body.style.overflow = "";
}

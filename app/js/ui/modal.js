import { $ } from "../utils/dom.js";

// Upewnij się, że ten adres jest identyczny z tym, który widzisz w Network (F12)
const API = "http://localhost:8000/index.php";

function getModal() {
  return $("#movieModal");
}

export function openModal(movie) {
  const modal = getModal();
  const content = $("#modalContent");
  const reviews = movie.reviews || [];

  if (!modal || !content) {
    console.error("Błąd: Nie znaleziono elementów modala!");
    return;
  }

  content.innerHTML = `
    <div class="modal-header">
      <img src="${movie.backdrop || movie.poster}" alt="${movie.title}" class="modal-backdrop">
      <button class="close-modal">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-title-row">
        <h1>${movie.title}</h1>
        <span class="modal-rating">${movie.rating}</span>
      </div>
      <div class="modal-meta">
        <span>${movie.year}</span>
        <span>${movie.duration}</span>
        <span>${movie.language || 'Polski'}</span>
        <span>${movie.platform}</span>
      </div>
      <p class="modal-description">${movie.description}</p>
      
      <hr style="border: 0; border-top: 1px solid #333; margin: 30px 0 20px;">
      
      <div class="reviews-section">
        <h3>Recenzje i Komentarze</h3>
        <div id="reviewsContainer" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
          ${reviews.length > 0 
            ? reviews.map(r => `
                <div class="review-item" style="background: #1a1a1a; padding: 12px; border-radius: 8px; margin-bottom: 10px; border-left: 3px solid #3b82f6;">
                  <div style="display: flex; justify-content: space-between;">
                    <strong style="color: #3b82f6;">${r.user}</strong>
                    <small style="color: #666;">${r.date}</small>
                  </div>
                  <p style="color: #ccc; margin-top: 5px;">${r.text}</p>
                </div>
              `).join('')
            : '<p style="color: #666;">Brak recenzji. Bądź pierwszy!</p>'
          }
        </div>

        <div class="add-review-form">
          <textarea id="newReviewText" placeholder="Co sądzisz o tym tytule?" 
            style="width: 100%; height: 80px; background: #0a0a0a; color: white; border: 1px solid #333; padding: 10px; border-radius: 6px;"></textarea>
          <button onclick="window.submitReview('${movie.id}')" 
            style="background: #3b82f6; color: white; border: none; padding: 10px; border-radius: 6px; margin-top: 10px; cursor: pointer; width: 100%;">
            Opublikuj recenzję
          </button>
        </div>
      </div>
    </div>
  `;

  modal.classList.add("active");
  document.body.style.overflow = "hidden";

  const closeBtn = content.querySelector(".close-modal");
  if (closeBtn) closeBtn.onclick = closeModal;
}

export function closeModal() {
  const modal = getModal();
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "auto";
  }
}

// GLOBALNA REJESTRACJA FUNKCJI
window.submitReview = async (movieId) => {
    const textElement = document.getElementById('newReviewText');
    const text = textElement ? textElement.value.trim() : "";

    if (!text) return alert("Wpisz treść!");

    try {
        const url = `${API}?controller=movie&action=addReview&id=${movieId}`;
        console.log("Wysyłam recenzję do:", url);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                text: text,
                user: "Użytkownik",
                date: new Date().toISOString().split('T')[0]
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            alert("Dodano recenzję!");
            location.reload();
        } else {
            console.error("Błąd serwera:", result);
            alert("Błąd serwera: " + (result.error || "Nieznany błąd"));
        }
    } catch (error) {
        console.error("Błąd krytyczny JavaScript:", error);
        alert("Wystąpił błąd przy wysyłaniu. Sprawdź konsolę (F12).");
    }
};
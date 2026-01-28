import { $ } from "../utils/dom.js";

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

  // Sortuj recenzje - wyróżnione na górze, potem po liczbie lajków
  const sortedReviews = [...reviews].sort((a, b) => {
    if (a.highlighted && !b.highlighted) return -1;
    if (!a.highlighted && b.highlighted) return 1;
    return (b.likes || 0) - (a.likes || 0);
  });

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
        <span>${movie.country || 'Nieznany'}</span>
        <span>${movie.platform}</span>
      </div>
      <p class="modal-description">${movie.description}</p>
      
      <hr style="border: 0; border-top: 1px solid #333; margin: 30px 0 20px;">
      
      <div class="reviews-section">
        <h3>Recenzje i Komentarze (${reviews.length})</h3>
        
        <div id="reviewsContainer" style="max-height: 400px; overflow-y: auto; margin-bottom: 20px;">
          ${sortedReviews.length > 0 
            ? sortedReviews.map(r => `
                <div class="review-item ${r.highlighted ? 'highlighted' : ''}" 
                     style="background: ${r.highlighted ? '#1a2a1a' : '#1a1a1a'}; 
                            padding: 12px; border-radius: 8px; margin-bottom: 10px; 
                            border-left: 3px solid ${r.highlighted ? '#22c55e' : '#3b82f6'};">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                      <strong style="color: ${r.highlighted ? '#22c55e' : '#3b82f6'};">
                        ${r.highlighted ? '⭐ ' : ''}${r.user}
                      </strong>
                      ${r.rating ? `<span style="margin-left: 10px; color: #fbbf24;">★ ${r.rating}/10</span>` : ''}
                    </div>
                    <small style="color: #666;">${r.date}</small>
                  </div>
                  <p style="color: #ccc; margin-top: 8px; line-height: 1.5;">${r.text}</p>
                  <div style="margin-top: 8px; display: flex; gap: 10px; align-items: center;">
                    <button type="button" onclick="window.likeReview('${movie.id}', '${r.id}')"
 
                            style="background: none; border: 1px solid #333; color: #888; padding: 4px 10px; 
                                   border-radius: 4px; cursor: pointer; font-size: 12px;">
                      👍 ${r.likes || 0}
                    </button>
                  </div>
                </div>
              `).join('')
            : '<p style="color: #666; text-align: center; padding: 20px;">Brak recenzji. Bądź pierwszy!</p>'
          }
        </div>

        <div class="add-review-form" style="background: #111; padding: 15px; border-radius: 8px;">
          <h4 style="margin-bottom: 10px; color: #fff;">Dodaj recenzję</h4>
          
          <div style="display: flex; gap: 10px; margin-bottom: 10px;">
            <input type="text" id="reviewUserName" placeholder="Twoje imię (opcjonalne)" 
                   style="flex: 1; padding: 8px; background: #1a1a1a; color: white; border: 1px solid #333; border-radius: 4px;">
            
            <select id="reviewRating" style="padding: 8px; background: #1a1a1a; color: white; border: 1px solid #333; border-radius: 4px;">
              <option value="0">Bez oceny</option>
              <option value="10">10 - Arcydzieło</option>
              <option value="9">9 - Wybitny</option>
              <option value="8">8 - Bardzo dobry</option>
              <option value="7">7 - Dobry</option>
              <option value="6">6 - Przeciętny</option>
              <option value="5">5 - Słaby</option>
              <option value="4">4 - Zły</option>
              <option value="3">3 - Bardzo zły</option>
              <option value="2">2 - Okropny</option>
              <option value="1">1 - Tragedia</option>
            </select>
          </div>
          
          <textarea id="newReviewText" placeholder="Co sądzisz o tym tytule?" 
            style="width: 100%; height: 80px; background: #1a1a1a; color: white; border: 1px solid #333; 
                   padding: 10px; border-radius: 6px; resize: vertical;"></textarea>
          
          <button onclick="window.submitReview('${movie.id}')" 
            style="background: #3b82f6; color: white; border: none; padding: 12px; border-radius: 6px; 
                   margin-top: 10px; cursor: pointer; width: 100%; font-weight: bold; transition: background 0.2s;">
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

// ==================== GLOBALNE FUNKCJE ====================

window.submitReview = async (movieId) => {
  const textElement = document.getElementById('newReviewText');
  const userElement = document.getElementById('reviewUserName');
  const ratingElement = document.getElementById('reviewRating');
  
  const text = textElement?.value?.trim() || "";
  const user = userElement?.value?.trim() || "Użytkownik";
  const rating = parseInt(ratingElement?.value) || 0;

  if (!text) {
    alert("Wpisz treść recenzji!");
    return;
  }

  if (text.length < 3) {
    alert("Recenzja musi mieć minimum 3 znaki!");
    return;
  }

  try {
    const url = `${API}?controller=movie&action=addReview&id=${movieId}`;
    
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        text: text,
        user: user,
        rating: rating,
        date: new Date().toISOString().split('T')[0]
      })
    });

    const result = await response.json();

    if (response.ok && result.success) {
      alert("Dodano recenzję!");
      location.reload();
    } else {
      console.error("Błąd serwera:", result);
      alert("Błąd: " + (result.error || "Nieznany błąd"));
    }
  } catch (error) {
    console.error("Błąd JavaScript:", error);
    alert("Wystąpił błąd przy wysyłaniu. Sprawdź konsolę (F12).");
  }
};

window.likeReview = async (movieId, reviewId) => {
  try {
    const btn = event.target.closest("button");

    const url = `${API}?controller=movie&action=likeReview&movieId=${movieId}&reviewId=${reviewId}`;
    
    const response = await fetch(url, { method: 'POST' });
    const result = await response.json();

    if (result.success) {
      const span = btn.querySelector("span");
      span.textContent = parseInt(span.textContent) + 1;
    } else {
      alert("Błąd: " + (result.error || "Nie udało się polubić"));
    }
  } catch (error) {
    console.error("Błąd:", error);
  }
};


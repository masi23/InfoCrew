const API = "http://localhost:8000/index.php";

const loginForm = document.getElementById("loginForm");
const adminPanel = document.getElementById("adminPanel");
const logoutBtn = document.getElementById("logoutBtn");

loginForm.addEventListener("submit", async (e) => {
  e.preventDefault();

  const res = await fetch(`${API}?controller=auth&action=login`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      username: username.value,
      password: password.value,
    }),
  });

  if (res.ok) {
    loginForm.classList.add("hidden");
    adminPanel.classList.remove("hidden");
  } else {
    alert("Błędne dane logowania");
  }
});

logoutBtn.addEventListener("click", async () => {
  await fetch(`${API}?controller=auth&action=logout`);
  location.reload();
});

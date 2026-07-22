// Modal open
function openForm() {
  document.getElementById("formModal").classList.add("active");
}

// Modal close
function closeForm() {
  document.getElementById("formModal").classList.remove("active");
}

// Modal veliye click panna close
window.addEventListener("click", (e) => {
  const modal = document.getElementById("formModal");
  if (e.target === modal) {
    modal.classList.remove("active");
  }
});

// ESC key adicha modal close
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    closeForm();
  }
});
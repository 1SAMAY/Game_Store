(function () {
  const storageKey = "gamestore-theme";

  function applyTheme(theme) {
    const mode = theme === "light" ? "light" : "dark";
    document.body.classList.toggle("light-theme", mode === "light");

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      button.textContent = mode === "light" ? "Dark mode" : "Light mode";
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem(storageKey) || "dark";
    applyTheme(savedTheme);

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      button.addEventListener("click", () => {
        const nextTheme = document.body.classList.contains("light-theme") ? "dark" : "light";
        localStorage.setItem(storageKey, nextTheme);
        applyTheme(nextTheme);
      });
    });
  });
})();

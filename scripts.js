const games = {
  rdr: {
    title: "RED DEAD REDEMPTION 2",
    desc: "Outlaws for life in the dying days of the wild west.",
    banner: "images/RDR2.jpg"
  },
  blackmyth: {
    title: "BLACK MYTH: WUKONG",
    desc: "Unleash your legend in the mythical world of Sun Wukong.",
    banner: "images/Black Myth Wukong.jpeg"
  },
  gta: {
    title: "GRAND THEFT AUTO V",
    desc: "Build an empire to stand the test of time.",
    banner: "images/Gta V.jpg"
  },
  valorant: {
    title: "VALORANT",
    desc: "A 5v5 character-based tactical FPS.",
    banner: "images/Valorant.jpg"
  }
};

function showGame(key, el = null) {
  const game = games[key];
  if (!game) {
    console.warn(`Game key "${key}" not found.`);
    return;
  }

  const banner = document.getElementById("banner-img");
  const title = document.getElementById("game-title");
  const desc = document.getElementById("game-desc");

  if (!banner || !title || !desc) {
    return;
  }

  banner.src = game.banner;
  title.textContent = game.title;
  desc.textContent = game.desc;

  banner.classList.remove("fade-in");
  void banner.offsetWidth;
  banner.classList.add("fade-in");

  [title, desc].forEach(node => {
    node.classList.remove("text-reveal");
    void node.offsetWidth;
    node.classList.add("text-reveal");
  });

  const bannerWrapper = banner.closest(".featured-banner");
  if (bannerWrapper) {
    bannerWrapper.classList.remove("shine");
    void bannerWrapper.offsetWidth;
    bannerWrapper.classList.add("shine");
  }

  document.querySelectorAll(".game-bar").forEach(bar => bar.classList.remove("active"));
  if (el) {
    el.classList.add("active");
  } else {
    const sidebarEl = document.querySelector('.game-bar[data-game="' + key + '"]');
    if (sidebarEl) sidebarEl.classList.add("active");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const gameKeys = Object.keys(games);
  const defaultKey = gameKeys[0];
  const defaultEl = document.querySelector('.game-bar[data-game="' + defaultKey + '"]');

  if (defaultKey) {
    showGame(defaultKey, defaultEl);
  }

  let currentIndex = 0;
  setInterval(() => {
    const key = gameKeys[currentIndex];
    const activeEl = document.querySelector('.game-bar[data-game="' + key + '"]');
    showGame(key, activeEl);
    currentIndex = (currentIndex + 1) % gameKeys.length;
  }, 5000);
});

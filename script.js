// // Nav bar menu icon
// const navlinks = document.getElementById("link-account");
// navlinks.style.height = "100vh";
// const menuIcon = document.getElementById("menutoggle").addEventListener("click", () => {
//     if (navlinks.style.height == 0) {
//         navlinks.style.height = "100vh";
//     } else {
//         navlinks.style.height = 0;
//     }
//     console.log(navlinks);
//     console.log(menuIcon);
// })

// Footer Year changing
let yearContainer = document.getElementById("copyright-year-container");
let year = new Date().getFullYear();
yearContainer.innerHTML = year;


document.addEventListener("DOMContentLoaded", function() {
    // Ocultar todos los submenús al cargar la página
    document.querySelectorAll('.dropdown-submenu > .dropdown-menu').forEach(function(menu) {
        menu.style.display = "none";
    });

    // Manejo de clic para alternar la visibilidad de cada submenú
    const submenuToggles = document.querySelectorAll('.dropdown-submenu > a.dropdown-toggle');
    submenuToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            let submenu = this.nextElementSibling;
            
            // Alterna la visibilidad del submenú
            if (submenu.style.display === "block") {
                submenu.style.display = "none";
            } else {
                // Ocultar otros submenús abiertos
                submenuToggles.forEach(function(otherToggle) {
                    if (otherToggle !== toggle) {
                        otherToggle.nextElementSibling.style.display = "none";
                    }
                });
                submenu.style.display = "block";
            }
        });
    });

    // Cierra los submenús al hacer clic fuera del menú
    document.addEventListener("click", function() {
        submenuToggles.forEach(function(toggle) {
            toggle.nextElementSibling.style.display = "none";
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleSidebarBtn = document.getElementById('toggleSidebar');
    const sidebarContent = document.getElementById('sidebarContent');
    const chevronLeft = toggleSidebarBtn.querySelector('.chevron-left');
    const chevronRight = toggleSidebarBtn.querySelector('.chevron-right');
    const accordionTriggers = document.querySelectorAll('.accordion-trigger');
    const employeeForm = document.getElementById('employeeForm');
    const successAlert = document.getElementById('successAlert');

    // Función para alternar el sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        sidebarContent.classList.toggle('hidden');
        chevronLeft.classList.toggle('hidden');
        chevronRight.classList.toggle('hidden');
    }

    // Event listener para el botón de alternar sidebar
    toggleSidebarBtn.addEventListener('click', toggleSidebar);

    // Función para manejar los acordeones
    function toggleAccordion() {
        this.classList.toggle('active');
        const content = this.nextElementSibling;
        content.classList.toggle('active');
        
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
        }
    }

    // Event listeners para los acordeones
    accordionTriggers.forEach(trigger => {
        trigger.addEventListener('click', toggleAccordion);
    });

    // Funcionalidad de los botones (para demostración)
    const buttons = document.querySelectorAll('button:not(#toggleSidebar):not(.accordion-trigger)');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault(); // Previene la acción por defecto
            console.log('Botón clickeado:', this.textContent.trim());
        });
    });

    // Manejo del formulario
    employeeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        console.log('Formulario enviado con los siguientes datos:');
        for (let [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }
        
        // Mostrar mensaje de éxito
        successAlert.classList.remove('hidden');
        
        // Limpiar el formulario
        this.reset();
        
        // Ocultar el mensaje de éxito después de 3 segundos
        setTimeout(() => {
            successAlert.classList.add('hidden');
        }, 3000);
    });

    // Mejora de accesibilidad: manejo de teclas para los acordeones
    accordionTriggers.forEach(trigger => {
        trigger.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
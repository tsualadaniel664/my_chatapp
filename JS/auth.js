document.addEventListener('DOMContentLoaded', function () {
    // 1. Password Visibility Toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icons
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }

    // 2. File Input Display (for register page)
    const photoInput = document.getElementById('photoInput');
    const fileNameDisplay = document.getElementById('fileNameDisplay');

    if (photoInput && fileNameDisplay) {
        photoInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const fileName = this.files[0].name;
                fileNameDisplay.innerHTML = `
                    <i class="bi bi-file-earmark-image fs-4 mb-1 text-primary"></i>
                    <p class="small mb-0 text-white">${fileName}</p>
                `;
            } else {
                fileNameDisplay.innerHTML = `
                    <i class="bi bi-cloud-arrow-up fs-4 mb-1"></i>
                    <p class="small mb-0">Cliquez pour choisir une photo</p>
                `;
            }
        });
    }

    // 3. Form Loading Animation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                const spinner = submitBtn.querySelector('.spinner-border');
                if (spinner) {
                    spinner.classList.remove('d-none');
                    submitBtn.classList.add('disabled');
                    // We don't preventDefault as we want the PHP submission to occur
                }
            }
        });
    });

    // 4. Staggered Card Animation (Home Page)
    const cards = document.querySelectorAll('.user-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

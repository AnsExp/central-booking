document.addEventListener('DOMContentLoaded', function () {
    const modals = document.querySelectorAll('.git-modal');
    modals.forEach(modal => {
        const id = modal.id;
        document.querySelectorAll(`[data-target="#${id}"]`).forEach(button => {
            button.addEventListener('click', function () {
                if (button.classList.contains('git-modal-launch')) {
                    openGitModal(id);
                } else if (button.classList.contains('git-modal-dismiss')) {
                    closeGitModal(id);
                }
            });
        });
        document.addEventListener('click', function (event) {
            const modal = document.getElementById(id);
            if (event.target === modal) {
                closeGitModal(id);
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeGitModal(id);
            }
        });
    });
});

function openGitModal(id) {
    document.getElementById(id).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeGitModal(id) {
    document.getElementById(id).style.display = 'none';
    document.body.style.overflow = '';
}


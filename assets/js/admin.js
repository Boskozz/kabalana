import '../styles/admin.css';

document.addEventListener('click', function (event) {
    const target = event.target.closest('.copy-to-clipboard');
    if (!target) return;

    event.preventDefault();
    event.stopImmediatePropagation();

    const text = target.dataset.copy;
    navigator.clipboard.writeText(text).then(() => {
        const original = target.style.color;
        target.style.color = '#28a745';
        setTimeout(() => { target.style.color = original; }, 600);
    });
}, true); // 👈 true = phase de capture
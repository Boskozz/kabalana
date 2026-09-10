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

// =====================================================================
// Blocs : insertion automatique du modèle JSON selon le type choisi
// =====================================================================

const EMPTY_CONTENT_VALUES = ['', '{}', '[]', 'null'];

function getCodeEditor(textarea) {
    const wrapper = textarea.nextElementSibling;
    if (wrapper && wrapper.CodeMirror) {
        return wrapper.CodeMirror;
    }
    return null;
}

function getContentValue(textarea) {
    const editor = getCodeEditor(textarea);
    return editor ? editor.getValue() : textarea.value;
}

function setContentValue(textarea, value) {
    const editor = getCodeEditor(textarea);
    if (editor) {
        editor.setValue(value);
        if (typeof editor.save === 'function') {
            editor.save();
        }
        return;
    }

    textarea.value = value;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.dispatchEvent(new Event('change', { bubbles: true }));
}

// Mémorise le type courant avant toute modification, pour pouvoir
// revenir en arrière si l'utilisateur annule la confirmation.
document.addEventListener('focusin', function (event) {
    const select = event.target.closest('select[data-bloc-type]');
    if (select) {
        select.dataset.blocPrevType = select.value;
    }
});

document.addEventListener('change', function (event) {
    const select = event.target.closest('select[data-bloc-type]');
    if (!select) return;

    let templates;
    try {
        templates = JSON.parse(select.dataset.blocTemplates || '{}');
    } catch (error) {
        return;
    }

    const newType = select.value;
    const template = templates[newType];
    if (!template) return;

    const contentField = document.querySelector('[data-bloc-content]');
    if (!contentField) return;

    const currentValue = getContentValue(contentField).trim();
    const isEmpty = EMPTY_CONTENT_VALUES.includes(currentValue);
    const previousType = select.dataset.blocPrevType ?? select.value;

    if (!isEmpty) {
        const option = select.options[select.selectedIndex];
        const label = option ? option.text : newType;
        const confirmed = window.confirm(
            'Le contenu JSON est déjà rempli.\n\n'
            + 'Voulez-vous vraiment le remplacer par le modèle du type « ' + label + ' » ?'
        );

        if (!confirmed) {
            select.value = previousType;
            return;
        }
    }

    setContentValue(contentField, JSON.stringify(template, null, 2));
    select.dataset.blocPrevType = newType;
});
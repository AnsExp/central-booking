import { createContentExcelDimiss } from "./table.js";

const selects = document.getElementsByClassName('git-multiselect');

for (const select of selects) {
    const container = getContainer(select);
    select.addEventListener('change', () => {
        const index = select.selectedIndex;
        const option = select.options[index];
        if (option.getAttribute('value') === null) {
            return;
        }
        addOption(option, container);
        select.selectedIndex = 0;
    });
}

/**
 * @param {HTMLSelectElement} select 
 * @returns {HTMLDivElement}
 */
function getContainer(select) {
    return document.getElementById(`${select.id}-container`);
}

/**
 * 
 * @param {HTMLOptionElement} option 
 * @param {HTMLDivElement} container 
 */
function addOption(option, container) {
    option.setAttribute('selected', '');
    option.style.display = 'none';
    const cellDimiss = createContentExcelDimiss(option.textContent, () => {
        option.style.display = '';
        option.removeAttribute('selected');
    });
    container.appendChild(cellDimiss);
}

/**
 * 
 * @param {HTMLSelectElement} multiselect 
 * @returns {Array}
 */
export function getOptions(multiselect) {
    const results = [];
    if (!multiselect.classList.contains('git-multiselect')) {
        return results;
    }

    const options = multiselect.options;

    for (const option of options) {
        if (option.getAttribute('selected') !== null) {
            results.push(option.value);
        }
    }

    return results;
}

/**
 * 
 * @param {HTMLSelectElement} multiselect 
 * @param {Array} options 
 */
export function setOptions(multiselect, options) {
    if (!multiselect.classList.contains('git-multiselect')) {
        return;
    }
    for (const option of multiselect.options) {
        option.style.display = '';
        option.removeAttribute('selected');
    }
    const container = getContainer(multiselect);
    container.innerHTML = '';
    for (const option of options) {
        let e = multiselect.querySelector(`option[value="${option}"]`);
        if (e) {
            addOption(e, container);
        }
    }
    multiselect.selectedIndex = 0;
}
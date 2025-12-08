const templates = document.getElementById(formElements.templates);
const accordionCrew = document.getElementById(formElements.accordionCrew);
const buttonAddAlias = document.getElementById(formElements.buttonAddAlias);
const buttonAddCrewMember = document.getElementById(formElements.buttonAddCrewMember);
const containerAliasFields = document.getElementById(formElements.containerAliasFields);

document.querySelectorAll('.remove-crew-member').forEach(memberDimiss => {
    memberDimiss.addEventListener('click', () => {
        dimissCrewMemberEvent(memberDimiss.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.id);
    });
});

document.querySelectorAll('.button-remove-alias').forEach(memberDimiss => {
    memberDimiss.addEventListener('click', removeAliasEvent);
});

accordionCrew.querySelectorAll('.accordion-item').forEach(item => {
    const header = item.querySelector('.accordion-header');
    const content = item.querySelector('.accordion-collapse');

    content.querySelector('input[name="crew_member_name[]"]').addEventListener('input', (e) => {
        header.querySelector('[data-tag-name]').textContent = e.target.value;
    });

    content.querySelector('input[name="crew_member_role[]"]').addEventListener('input', (e) => {
        header.querySelector('[data-tag-role]').textContent = e.target.value;
    });
});

function dimissCrewMemberEvent(itemId) {
    document.getElementById(itemId).parentNode.remove();
}

buttonAddAlias.addEventListener('click', () => {
    const content = templates.content.querySelector('#form-transport-alias-field').cloneNode(true);
    content.querySelector('button.button-remove-alias').addEventListener('click', removeAliasEvent);
    containerAliasFields.appendChild(content);
});

function removeAliasEvent(e) {
    e.target.parentNode.remove();
}

buttonAddCrewMember.addEventListener('click', () => {
    const header = document.createElement('span');
    header.innerHTML =
        `<span data-tag-name=""></span>
        <i class="bi bi-caret-right"></i>
        <span data-tag-role=""></span>`;
    const content = templates.content.querySelector('#template-form-crew-member>table').cloneNode(true);
    content.querySelector('input[name="crew_member_name[]"]').addEventListener('input', (e) => {
        header.querySelector('[data-tag-name]').textContent = e.target.value;
    });
    content.querySelector('input[name="crew_member_role[]"]').addEventListener('input', (e) => {
        header.querySelector('[data-tag-role]').textContent = e.target.value;
    });
    const itemId = window.AccordionComponentAPI.addItem(accordionCrew, header, content);
    content
        .querySelector('button.remove-crew-member')
        .addEventListener('click', () => {
            dimissCrewMemberEvent(itemId);
        });
});

document.querySelector('button[type="submit"]').addEventListener('click', function () {
    for (const input of accordionCrew.querySelectorAll('input')) {
        let accodionCollapse = null;
        if (input.value.trim() === '') {
            let iterator = input;
            while (accodionCollapse === null) {
                iterator = iterator.parentNode;
                if (iterator.classList && iterator.classList.contains('accordion-collapse')) {
                    accodionCollapse = iterator;
                }
            };
            const idCollapse = accodionCollapse.id;
            const buttonCollapse = document.querySelector(`button[data-target="#${idCollapse}"]`);
            if (!iterator.classList.contains('show')) {
                buttonCollapse.click();
            }
            input.focus();
            return;
        };
    };
});

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

document.getElementById('submit').addEventListener('click', function () {
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
            input.focfus();
            return;
        };
    };
});

document.getElementById('form-transport').addEventListener('submit', submitHandler);

function submitHandler(e) {
    e.preventDefault();

    const working_days = [];
    const crewMembers = [];

    e.target.querySelectorAll('.git-accordion .accordion-item .form-table').forEach(formMember => {
        crewMembers.push({
            name: formMember.querySelector('input[name="crew_member_name[]"]').value,
            role: formMember.querySelector('input[name="crew_member_role[]"]').value,
            license: formMember.querySelector('input[name="crew_member_license[]"]').value,
            contact: formMember.querySelector('input[name="crew_member_contact[]"]').value,
        });
    });

    e.target.querySelectorAll('input[name="days[]"]').forEach(input => {
        if (input.checked) {
            working_days.push(input.value);
        }
    });

    const routesSelect = e.target.querySelector('select[name="routes"]');
    const servicesSelect = e.target.querySelector('select[name="services"]');

    const body = {
        id: e.target.querySelector('input[name="id"]').value,
        nicename: e.target.querySelector('input[name="nicename"]').value,
        code: e.target.querySelector('input[name="code"]').value,
        type: e.target.querySelector('select[name="type"]').value,
        operator: e.target.querySelector('select[name="operator"]').value,
        capacity: e.target.querySelector('input[name="capacity"]').value,
        routes: JSON.parse(routesSelect.dataset.selected || '[]').map(id => parseInt(id)),
        services: JSON.parse(servicesSelect.dataset.selected || '[]').map(id => parseInt(id)),
        working_days: working_days,
        crew: crewMembers,
        nonce: formElements.ajax.nonce,
        photo_url: e.target.querySelector('input[name="photo_url"]').value,
        custom_field_topic: e.target.querySelector('select[name="custom_field_topic"]').value,
        custom_field_content: e.target.querySelector('textarea[name="custom_field"]').value,
        alias: e.target.querySelectorAll('input[name="alias[]"]').length > 0 ? Array.from(e.target.querySelectorAll('input[name="alias[]"]')).map(input => input.value).filter(value => value.trim() !== '') : [],
    };

    const formData = new FormData();
    formData.append('action', formElements.ajax.action);
    formData.append('nonce', formElements.ajax.nonce);
    formData.append('data', JSON.stringify(body));

    const messageContainer = document.getElementById('form-transport-message-container');
    fetch(formElements.ajax.url + '?action=' + formElements.ajax.action, {
        method: 'POST',
        body: formData
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }).then(data => {
        messageContainer.innerHTML += `<div class="notice notice-success is-dismissible"><p>${data.message}</p></div>`;
        location.replace(formElements.ajax.successRedirect);
    }).catch(error => {
        messageContainer.innerHTML += `<div class="notice notice-error is-dismissible"><p>${error}</p></div>`;
    });
}